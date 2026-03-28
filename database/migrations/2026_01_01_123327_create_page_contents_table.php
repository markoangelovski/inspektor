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
        Schema::create('page_contents', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('page_id')->index();
            $table->string('extractor_version', 64)->index();

            $table->jsonb('content');
            $table->timestampTz('extracted_at');

            $table->timestampsTz();

            $table->unique(['page_id', 'extractor_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
