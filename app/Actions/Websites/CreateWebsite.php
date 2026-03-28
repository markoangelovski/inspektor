<?php

namespace App\Actions\Websites;

use App\Jobs\ProcessWebsiteMetadata;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class CreateWebsite
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute($websiteData): Website
    {
        $website = Website::create([
            'user_id' => Auth::id(),
            "name" => strip_tags($websiteData["name"]),
            "url" => rtrim(strip_tags($websiteData["url"]), "/"),
        ]);

        ProcessWebsiteMetadata::dispatch($website->id);

        return $website;
    }
}
