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
        Schema::create('content_extraction_run_events', function (Blueprint $table) {
            $table->ulid('id')->primary();


            $table->string('type', 64);
            $table->jsonb('payload');

            $table->timestampTz('created_at')->useCurrent();

            $table->ulid('website_id')->index();
            $table->foreign('website_id')
                ->references('id')->on('websites')
                ->cascadeOnDelete();

            $table->ulid('content_extraction_run_id')->index();
            $table->foreign('content_extraction_run_id')
                ->references('id')->on('content_extraction_runs')
                ->cascadeOnDelete();

            $table->ulid('page_id')->index();
            $table
                ->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_extraction_run_events');
    }
};
