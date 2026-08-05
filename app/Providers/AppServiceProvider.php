<?php

namespace App\Providers;

use App\Models\Permission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->registerPermissionGates();
    }

    /**
     * Resolve `$this->authorize('kullanici_sil')` / `Gate::allows('kullanici_sil')` style checks
     * against the `permissions` table, evaluated lazily on each check instead of enumerated once
     * at boot — so newly added permissions (or a database that isn't migrated/seeded yet, as in
     * tests) don't require an app restart or risk denying everyone.
     */
    protected function registerPermissionGates(): void
    {
        Gate::before(function ($user, string $ability) {
            try {
                $permission = Permission::query()->where('anahtar', $ability)->first();
            } catch (\Throwable) {
                // Migration henüz çalışmadıysa (ör. ilk kurulum) tablo yok sayılır.
                return null;
            }

            return $permission ? $user->hasPermission($ability) : null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
