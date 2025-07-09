<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('isbn')->unique()->nullable();
            $table->string('pengarang');
            $table->string('penerbit');
            $table->integer('tahun_terbit');
            $table->integer('jumlah_halaman');
            $table->string('cover')->nullable();
            $table->integer('stok')->default(0);
            $table->text('deskripsi')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('lokasi_rak')->nullable();
            
            // Changed from 'Status' to 'ActiveStatus' to avoid conflict with 'status'
            $table->enum('status', ['tersedia', 'dipinjam', 'rusak'])->default('tersedia');
            $table->tinyInteger('active_status')->default(1); // Renamed from Status
            
            // Standard fields
            $table->string('CompanyCode', 20)->nullable();
            $table->tinyInteger('IsDeleted')->default(0);
            $table->string('CreatedBy', 32)->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->string('LastUpdatedBy', 32)->nullable();
            $table->dateTime('LastUpdatedDate')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};