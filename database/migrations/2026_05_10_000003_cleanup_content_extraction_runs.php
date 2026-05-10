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
            $table->jsonb('diff')->nullable()->after('events');

            $table->dropColumn(['mode', 'last_error', 'failure_summary', 'failed_pages', 'extractor_version', 'config']);
        });
    }

    public function down(): void
    {
        Schema::table('content_extraction_runs', function (Blueprint $table) {
            $table->dropColumn('diff');

            $table->string('mode', 32)->default('initial');
            $table->text('last_error')->nullable();
            $table->string('failure_summary')->nullable();
            $table->integer('failed_pages')->default(0);
            $table->string('extractor_version', 64)->default('readability-v1');
            $table->jsonb('config')->default('{}');
        });
    }
};
