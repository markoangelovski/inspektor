<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Disable wrapping this migration in a transaction
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('content_extraction_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->string('status', 32);
            $table->string('mode', 32)->default('initial');

            $table->integer('total_pages')->default(0);
            $table->integer('processed_pages')->default(0);

            $table->integer('failed_pages')->default(0);
            $table->text('last_error')->nullable()->after('config');
            $table->string('failure_summary')->nullable()->after('last_error');

            $table->string('extractor_version', 64);
            $table->jsonb('config')->default('{}');

            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();

            $table->ulid('created_by')->nullable();

            $table->timestampsTz();

            $table->ulid('website_id')->index();
            $table
                ->foreign('website_id')
                ->references('id')
                ->on('websites')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
