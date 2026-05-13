<?php

namespace App\Actions\Websites;

use App\Models\Website;

class DeleteWebsite
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute(Website $website): void
    {
        $website->delete();
    }
}
