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
        Schema::create('sitemaps', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            $table->string('url', 2048);

            $table->ulid('website_id');
            $table
                ->foreign('website_id')
                ->references('id')
                ->on('websites')
                ->cascadeOnDelete();

            // Prevent duplicates per website
            $table->unique(['website_id', 'url']);

            $table->index('website_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemaps');
    }
};
