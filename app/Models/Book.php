<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'judul',
        'isbn',
        'pengarang',
        'penerbit',
        'tahun_terbit',
        'jumlah_halaman',
        'cover',
        'stok',
        'deskripsi',
        'category_id',
        'status',
        'lokasi_rak'
    ];

    protected $casts = [
        'tahun_terbit' => 'integer',
        'jumlah_halaman' => 'integer',
        'stok' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
