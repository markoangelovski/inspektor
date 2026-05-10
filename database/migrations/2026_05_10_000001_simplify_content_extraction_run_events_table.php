<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Drop the table entirely — the next migration re-creates the events
        // concept as a column on content_extraction_runs instead.
        // Using dropIfExists so migrate:fresh is idempotent with migration 000002.
        Schema::dropIfExists('content_extraction_run_events');
    }

    public function down(): void
    {
        //
    }
};
