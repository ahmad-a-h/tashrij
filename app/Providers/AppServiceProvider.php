<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        \App\Models\Refund::observe(\App\Observers\RefundObserver::class);
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make('Subscriptions')
                    ->label('Subscriptions'),
                NavigationGroup::make('Finance')
                    ->label('Finance'),
                NavigationGroup::make('User Management')
                     ->label('User Management'),
            ]);
        });
    }
}
