<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\Log;

class SocialLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->first();

            if ($finduser) {
                Auth::login($finduser);
                return redirect()->intended('dashboard');
            } else {
                $newUser = User::create([
                    'nama' => $user->name,
                    'email' => $user->email,
                    'google_id' => $user->id,
                    'password' => bcrypt(uniqid()),
                    'level' => 'anggota',
                    'no_hp' => '',
                    'alamat' => '',
                ]);

                Auth::login($newUser);
                return redirect()->intended('dashboard');
            }
        } catch (Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan Google');
        }
    }

    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleGithubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();
            
            // Log untuk debugging
            Log::info('GitHub user data:', [
                'id' => $githubUser->id,
                'name' => $githubUser->name,
                'email' => $githubUser->email
            ]);

            // Cek apakah user sudah ada
            $user = User::where('github_id', $githubUser->id)->first();

            if (!$user) {
                // Cek apakah email sudah terdaftar
                $user = User::where('email', $githubUser->email)->first();

                if ($user) {
                    // Update existing user dengan github_id
                    $user->update(['github_id' => $githubUser->id]);
                } else {
                    // Buat user baru
                    $user = User::create([
                        'nama' => $githubUser->name ?? $githubUser->nickname,
                        'email' => $githubUser->email,
                        'github_id' => $githubUser->id,
                        'password' => bcrypt(uniqid()),
                        'level' => 'anggota',
                        'no_hp' => '',
                        'alamat' => '',
                    ]);
                }
            }

            // Login user
            Auth::login($user);

            return redirect()->intended('dashboard');
        } catch (Exception $e) {
            Log::error('GitHub login error: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat login dengan GitHub: ' . $e->getMessage());
        }
    }
} 