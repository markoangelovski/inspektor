<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('content_extraction_runs', function (Blueprint $table) {
            $table->jsonb('events')->nullable()->default(null);
        });

        Schema::dropIfExists('content_extraction_run_events');
    }

    public function down(): void
    {
        Schema::table('content_extraction_runs', function (Blueprint $table) {
            $table->dropColumn('events');
        });
    }
};
