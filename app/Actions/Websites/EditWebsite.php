<?php

namespace App\Actions\Websites;

use App\Models\Website;

class EditWebsite
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute($websiteId, $websiteData)
    {
        $website = Website::find($websiteId);

        if ($website) {
            return  $website->update([
                "name" => strip_tags($websiteData["name"]),
                "url" => rtrim(strip_tags($websiteData["url"]), "/"),
                "type" => $websiteData["type"],
                "meta_title" => strip_tags($websiteData["meta_title"]),
                "meta_description" => strip_tags($websiteData["meta_description"]),
                "meta_image_url" => strip_tags($websiteData["meta_image_url"]),

            ]);
        }
    }
}
