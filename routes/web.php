<?php

use App\Http\Controllers\AiCreditsExportController;
use App\Livewire\AiCredits\Listing as AiCreditsListing;
use App\Livewire\ContentExtraction\Listing as ContentExtractionListing;
use App\Livewire\Pages\Expand as PagesExpand;
use App\Livewire\Pages\Listing as PagesListing;
use App\Livewire\Search\Listing as SearchListing;
use App\Livewire\Websites\Detail as WebsitesDetail;
use App\Livewire\Websites\Listing as WebsitesListing;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

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
    Route::get('/websites', WebsitesListing::class)->name('websites.listing');
    Route::get('/websites/{website}/info', WebsitesDetail::class)->name('websites.detail');
    Route::get('/websites/{website}/pages', PagesListing::class)->name('pages.listing');
    Route::get('/websites/{website}/pages/expand', PagesExpand::class)->name('pages.expand');
    Route::get('/websites/{website}/content-extraction', ContentExtractionListing::class)->name('content-extraction.listing');

    Route::get('/websites/{website}/ai-credits', AiCreditsListing::class)->name('ai-credits.listing');
    Route::get('/websites/{website}/ai-credits/export', AiCreditsExportController::class)->name('ai-credits.export');

    Route::get('/search', SearchListing::class)->name('search.listing');
});
