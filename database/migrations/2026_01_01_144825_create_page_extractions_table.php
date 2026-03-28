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
        Schema::create('page_extractions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('status', 32)->default('pending');
            $table->text('error')->nullable();
            $table->string('failure_type')->nullable();
            $table->timestampTz('started_at')->after('failure_type')->nullable();
            $table->timestampTz('finished_at')->after('started_at')->nullable();

            $table->timestampsTz();


            $table->ulid('content_extraction_run_id')->index();
            $table
                ->foreign('content_extraction_run_id')
                ->references('id')
                ->on('content_extraction_runs')
                ->cascadeOnDelete();

            $table->ulid('page_id')->index();
            $table
                ->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->cascadeOnDelete();

            $table->unique(['content_extraction_run_id', 'page_id'], 'unique_page_per_run');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_extractions');
    }
};
