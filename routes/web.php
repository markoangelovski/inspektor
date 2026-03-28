<?php

use App\Livewire\Websites\Listing as WebsitesListing;
use App\Livewire\Websites\Detail as WebsitesDetail;
use App\Livewire\Sitemaps\Listing as SitemapsListing;
use App\Livewire\Pages\Listing as PagesListing;
use App\Livewire\Pages\Expand as PagesExpand;
use App\Livewire\Search\Listing as SearchListing;
use Livewire\Volt\Volt;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Content Inspector routes:
    Route::get("/websites", WebsitesListing::class)->name("websites.listing");
    Route::get("/websites/{website}", WebsitesDetail::class)->name("websites.detail");
    Route::get("/websites/{website}/sitemaps", SitemapsListing::class)->name("sitemaps.listing");
    Route::get("/websites/{website}/pages", PagesListing::class)->name("pages.listing");
    Route::get("/websites/{website}/pages/expand", PagesExpand::class)->name("pages.expand");

    Route::get("/search", SearchListing::class)->name("search.listing");
});
