<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\Book;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Binding untuk service buku
        $this->app->bind(
            'App\Services\BookServiceInterface',
            'App\Services\BookService'
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length untuk MySQL
        Schema::defaultStringLength(191);

        // Gunakan Bootstrap untuk pagination
        Paginator::useBootstrap();

        // Validasi kustom: tanggal tidak boleh di hari libur
        Validator::extend('not_holiday', function ($attribute, $value, $parameters, $validator) {
            $date = Carbon::parse($value);
            return !$date->isWeekend(); // Contoh sederhana, bisa diganti dengan daftar hari libur
        }, 'Tidak bisa melakukan peminjaman di hari libur');

        // Validasi kustom: buku tersedia
        Validator::extend('book_available', function ($attribute, $value, $parameters, $validator) {
            return Book::find($value)->stok > 0;
        }, 'Buku tidak tersedia untuk dipinjam');

        // Validasi kustom: password lama benar
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, auth()->user()->password);
        }, 'Password saat ini tidak valid');

        // Share data ke semua view
        View::composer('*', function ($view) {
            // Data umum untuk semua user
            $newBooks = Book::orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $view->with('newBooks', $newBooks);
        });

        // Macro untuk format tanggal Indonesia
        Carbon::macro('toIndonesianDate', function () {
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            return sprintf('%s, %d %s %d',
                $days[$this->dayOfWeek],
                $this->day,
                $months[$this->month - 1],
                $this->year
            );
        });
    }
}
