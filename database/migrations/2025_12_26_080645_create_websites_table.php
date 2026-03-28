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
        Schema::create('websites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->timestamps();
            $table->string("name");
            $table->string('url', 2048)->unique();

            $table->string("meta_title", 2048)->nullable();
            $table->text("meta_description")->nullable();
            $table->string("meta_image_url", 2048)->nullable();
            $table->boolean('metadata_processed')->default(false)->index();

            $table->boolean("sitemaps_fetched")->default(false);
            $table->integer("sitemaps_count")->default(0);
            $table->timestamp('sitemaps_last_sync')->nullable();
            $table->string("sitemaps_message")->nullable();
            $table->boolean("sitemaps_processing")->default(false);

            $table->boolean("pages_fetched")->default(false);;
            $table->integer("pages_count")->default(0);
            $table->timestamp('pages_last_sync')->nullable();
            $table->string("pages_message")->nullable();
            $table->boolean("pages_processing")->default(false);

            $table->boolean("content_fetched")->default(false);;
            $table->timestamp('content_last_sync')->nullable();
            $table->string("content_message")->nullable();
            $table->boolean("content_processing")->default(false);

            $table->ulid("user_id");
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
