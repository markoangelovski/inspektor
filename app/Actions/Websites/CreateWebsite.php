<?php

namespace App\Actions\Websites;

use App\Actions\Sitemaps\FetchSitemaps;
use App\Jobs\ProcessWebsiteMetadata;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class CreateWebsite
{
    public function __construct(private FetchSitemaps $fetchSitemaps) {}

    public function execute($websiteData): Website
    {
        $website = Website::create([
            'user_id' => Auth::id(),
            'name' => strip_tags($websiteData['name']),
            'url' => rtrim(strip_tags($websiteData['url']), '/'),
        ]);

        ProcessWebsiteMetadata::dispatch($website->id);

        $this->fetchSitemaps->execute($website);

        return $website;
    }
}
