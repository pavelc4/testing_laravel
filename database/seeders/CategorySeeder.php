<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'nama' => 'Fiksi',
                'kode' => 'FIK',
                'deskripsi' => 'Buku-buku fiksi seperti novel, cerpen, dan puisi'
            ],
            [
                'nama' => 'Non-Fiksi',
                'kode' => 'NFK',
                'deskripsi' => 'Buku-buku non-fiksi seperti biografi, sejarah, dan sains'
            ],
            [
                'nama' => 'Pelajaran',
                'kode' => 'PLJ',
                'deskripsi' => 'Buku-buku pelajaran untuk SD, SMP, SMA, dan Perguruan Tinggi'
            ],
            [
                'nama' => 'Referensi',
                'kode' => 'REF',
                'deskripsi' => 'Buku-buku referensi seperti kamus, ensiklopedia, dan atlas'
            ],
            [
                'nama' => 'Teknologi',
                'kode' => 'TEK',
                'deskripsi' => 'Buku-buku tentang teknologi, komputer, dan programming'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
