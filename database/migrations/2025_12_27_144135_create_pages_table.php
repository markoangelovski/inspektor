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
        Schema::create('pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();

            // Page attributes
            $table->string('url', 2048);
            $table->string('path', 1024);
            $table->string('parent_path', 1024)->nullable();
            $table->string('slug', 255);

            // Relationships
            $table->ulid('website_id');
            $table
                ->foreign('website_id')
                ->references('id')
                ->on('websites')
                ->cascadeOnDelete();

            // Indexes
            $table->index('website_id');
            $table->index('path');
            $table->index('slug');

            // Prevent duplicate pages per website
            $table->unique(['website_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
