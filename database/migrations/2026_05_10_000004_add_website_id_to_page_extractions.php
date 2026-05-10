<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('page_extractions', function (Blueprint $table) {
            $table->ulid('website_id')->nullable()->after('id');
        });

        // Backfill from content_extraction_runs
        DB::statement('
            UPDATE page_extractions
            SET website_id = (
                SELECT website_id
                FROM content_extraction_runs
                WHERE content_extraction_runs.id = page_extractions.content_extraction_run_id
            )
            WHERE website_id IS NULL
        ');

        Schema::table('page_extractions', function (Blueprint $table) {
            $table->index('website_id');
            $table->foreign('website_id')
                ->references('id')
                ->on('websites')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_extractions', function (Blueprint $table) {
            $table->dropForeign(['website_id']);
            $table->dropIndex(['website_id']);
            $table->dropColumn('website_id');
        });
    }
};
