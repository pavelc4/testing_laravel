<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'judul' => 'Laskar Pelangi',
                'isbn' => '9789793062792',
                'pengarang' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'tahun_terbit' => 2005,
                'jumlah_halaman' => 529,
                'stok' => 5,
                'deskripsi' => 'Novel yang menceritakan perjuangan anak-anak di Belitung untuk mendapatkan pendidikan',
                'category_id' => 1, // Fiksi
                'lokasi_rak' => 'A1',
                'status' => 'tersedia'
            ],
            [
                'judul' => 'Clean Code',
                'isbn' => '9780132350884',
                'pengarang' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2008,
                'jumlah_halaman' => 464,
                'stok' => 3,
                'deskripsi' => 'Buku tentang praktik terbaik dalam menulis kode yang bersih dan mudah dipelihara',
                'category_id' => 5, // Teknologi
                'lokasi_rak' => 'B2',
                'status' => 'tersedia'
            ],
            [
                'judul' => 'Sejarah Indonesia Modern',
                'isbn' => '9789793780401',
                'pengarang' => 'M.C. Ricklefs',
                'penerbit' => 'Gadjah Mada University Press',
                'tahun_terbit' => 2005,
                'jumlah_halaman' => 600,
                'stok' => 2,
                'deskripsi' => 'Buku sejarah yang membahas perkembangan Indonesia dari masa kolonial hingga modern',
                'category_id' => 2, // Non-Fiksi
                'lokasi_rak' => 'C3',
                'status' => 'tersedia'
            ]
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}
