<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookBrowseController extends Controller
{
    public function __construct()
    {
        // No middleware needed - accessible to all authenticated users
    }

    public function index(Request $request)
    {
        $query = Book::query()->where('stok', '>', 0);

        // Filter by category if selected
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by title, author, or ISBN
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $books = $query->with('category')->latest()->paginate(12);
        $categories = Category::all();

        return view('books.browse', compact('books', 'categories'));
    }

    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);
        return view('books.show', compact('book'));
    }
} 