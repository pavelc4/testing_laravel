<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Book::with('category');

        // Filter berdasarkan pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan kategori
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $books = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all();

        return view('books.index', compact('books', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:255|unique:books',
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'jumlah_halaman' => 'required|integer|min:1',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'lokasi_rak' => 'nullable|string|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover')) {
            $cover = $request->file('cover');
            $coverPath = $cover->store('covers', 'public');
            $data['cover'] = $coverPath;
        }

        Book::create($data);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:255|unique:books,isbn,' . $book->id,
            'pengarang' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'jumlah_halaman' => 'required|integer|min:1',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'stok' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'lokasi_rak' => 'nullable|string|max:255',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover')) {
            // Delete old cover
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
            
            $cover = $request->file('cover');
            $coverPath = $cover->store('covers', 'public');
            $data['cover'] = $coverPath;
        }

        $book->update($data);

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if ($book->cover) {
            Storage::disk('public')->delete($book->cover);
        }
        
        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Buku berhasil dihapus');
    }
}
