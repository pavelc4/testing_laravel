<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loans = auth()->user()->level === 'anggota' 
            ? Loan::where('user_id', auth()->id())->with(['book', 'user'])->latest()->paginate(10)
            : Loan::with(['book', 'user'])->latest()->paginate(10);
            
        return view('loans.index', compact('loans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->has('book_id')) {
            $book = Book::findOrFail($request->book_id);
            if ($book->stok <= 0) {
                return redirect()->route('books.browse')
                    ->with('error', 'Buku tidak tersedia');
            }
            return view('loans.create', compact('book'));
        }

        $books = Book::where('stok', '>', 0)->get();
        return view('loans.create', compact('books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Starting loan creation process', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $request->validate([
                'book_id' => 'required|exists:books,id',
                'tanggal_pinjam' => 'required|date|after_or_equal:today',
                'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
                'keterangan' => 'nullable|string',
            ]);

            // Check if user has reached maximum loans (3 books)
            $activeLoans = Loan::where('user_id', auth()->id())
                ->whereIn('status', ['reserved', 'dipinjam'])
                ->count();

            \Log::info('Active loans count', ['count' => $activeLoans]);

            if ($activeLoans >= 3) {
                \Log::warning('User has reached maximum loans limit');
                return redirect()->back()
                    ->with('error', 'Anda telah mencapai batas maksimum peminjaman (3 buku)');
            }

            // Check if book is available
            $book = Book::findOrFail($request->book_id);
            \Log::info('Book found', ['book' => $book->toArray()]);

            if ($book->stok <= 0) {
                \Log::warning('Book is not available', ['book_id' => $book->id, 'stock' => $book->stok]);
                return redirect()->back()
                    ->with('error', 'Buku tidak tersedia');
            }

            DB::transaction(function () use ($request, $book) {
                \Log::info('Creating loan record', [
                    'user_id' => auth()->id(),
                    'book_id' => $request->book_id,
                    'dates' => [
                        'pinjam' => $request->tanggal_pinjam,
                        'kembali' => $request->tanggal_kembali
                    ]
                ]);

                // Create loan
                $loan = Loan::create([
                    'user_id' => auth()->id(),
                    'book_id' => $request->book_id,
                    'tanggal_pinjam' => $request->tanggal_pinjam,
                    'tanggal_kembali' => $request->tanggal_kembali,
                    'status' => 'reserved',
                    'keterangan' => $request->keterangan,
                ]);

                \Log::info('Loan created successfully', ['loan_id' => $loan->id]);

                // Update book stock
                $book->decrement('stok');
                if ($book->stok === 0) {
                    $book->update(['status' => 'dipinjam']);
                }

                \Log::info('Book stock updated', [
                    'book_id' => $book->id,
                    'new_stock' => $book->stok,
                    'status' => $book->status
                ]);
            });

            \Log::info('Loan creation process completed successfully');
            return redirect()->route('loans.index')
                ->with('success', 'Peminjaman berhasil dibuat');

        } catch (\Exception $e) {
            \Log::error('Error creating loan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat peminjaman: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        if (auth()->user()->isMember() && $loan->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        return view('loans.show', compact('loan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        if ($loan->status !== 'dipinjam') {
            return redirect()->route('loans.index')
                ->with('error', 'Peminjaman ini tidak dapat diedit.');
        }

        return view('loans.edit', compact('loan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        if ($loan->status !== 'dipinjam') {
            return redirect()->route('loans.index')
                ->with('error', 'Peminjaman ini tidak dapat diperbarui.');
        }

        $validated = $request->validate([
            'tanggal_kembali' => 'required|date|after:tanggal_pinjam',
            'keterangan' => 'nullable|string',
        ]);

        $loan->update($validated);

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        if (!in_array($loan->status, ['dipinjam', 'reserved'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Peminjaman ini tidak dapat dihapus.');
        }

        DB::transaction(function () use ($loan) {
            $book = $loan->book;
            $book->increment('stok');
            if ($book->stok > 0) {
                $book->update(['status' => 'tersedia']);
            }
            
            $loan->delete();
        });

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil dihapus.');
    }

    public function return(Loan $loan)
    {
        if (!in_array(auth()->user()->level, ['admin', 'petugas'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengembalikan buku');
        }

        if ($loan->status !== 'dipinjam') {
            return redirect()->route('loans.index')
                ->with('error', 'Status peminjaman tidak valid');
        }

        DB::transaction(function () use ($loan) {
            // Update loan status
            $loan->update([
                'status' => 'dikembalikan',
                'tanggal_dikembalikan' => now(),
            ]);

            // Update book stock
            $book = $loan->book;
            $book->increment('stok');
            if ($book->stok > 0) {
                $book->update(['status' => 'tersedia']);
            }
        });

        return redirect()->route('loans.index')
            ->with('success', 'Buku berhasil dikembalikan');
    }

    public function approve(Loan $loan)
    {
        if (!in_array(auth()->user()->level, ['admin', 'petugas'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Anda tidak memiliki akses untuk menyetujui peminjaman');
        }

        if ($loan->status !== 'reserved') {
            return redirect()->route('loans.index')
                ->with('error', 'Status peminjaman tidak valid');
        }

        $loan->update(['status' => 'dipinjam']);

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil disetujui');
    }

    public function cancel(Loan $loan)
    {
        if ($loan->user_id !== auth()->id() && !in_array(auth()->user()->level, ['admin', 'petugas'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Anda tidak memiliki akses untuk membatalkan peminjaman');
        }

        if ($loan->status !== 'reserved') {
            return redirect()->route('loans.index')
                ->with('error', 'Status peminjaman tidak valid');
        }

        DB::transaction(function () use ($loan) {
            // Update loan status
            $loan->update(['status' => 'dibatalkan']);

            // Update book stock
            $book = $loan->book;
            $book->increment('stok');
            if ($book->stok > 0) {
                $book->update(['status' => 'tersedia']);
            }
        });

        return redirect()->route('loans.index')
            ->with('success', 'Peminjaman berhasil dibatalkan');
    }
}
