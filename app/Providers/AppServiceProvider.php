<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            env('APP_ENV') !== 'local' || 
            request()->secure() || 
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || 
            (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on')
        ) {
            URL::forceScheme('https');
        }

        view()->composer('*', function ($view) {
            if (\Illuminate\Support\Facades\Schema::hasTable('tb_profile')) {
                $profile = \Illuminate\Support\Facades\DB::table('tb_profile')->first();
                if ($profile && !isset($profile->telepon)) {
                    $profile->telepon = $profile->telpon ?? '';
                }
                $view->with('profile', $profile);
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('tbl_keluhan')) {
                $jumlahKeluhanAktif = 0;
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    if ($user->level === 'teknisi') {
                        $jumlahKeluhanAktif = \Illuminate\Support\Facades\DB::table('tbl_keluhan')
                            ->where('status_keluhan', 'proses')
                            ->where(function($q) use ($user) {
                                $q->where('teknisi_id', $user->id)
                                  ->orWhere('assign_to_all', 1);
                            })
                            ->count();
                    } else {
                        // Admin/NOC see tickets that need action (menunggu/perlu_verifikasi)
                        $jumlahKeluhanAktif = \Illuminate\Support\Facades\DB::table('tbl_keluhan')
                            ->whereIn('status_keluhan', ['menunggu', 'perlu_verifikasi'])
                            ->count();
                    }
                } else {
                    $jumlahKeluhanAktif = \Illuminate\Support\Facades\DB::table('tbl_keluhan')
                        ->whereIn('status_keluhan', ['menunggu', 'proses'])
                        ->count();
                }
                $view->with('jumlahKeluhanAktif', $jumlahKeluhanAktif);
            }
        });
    }
}
