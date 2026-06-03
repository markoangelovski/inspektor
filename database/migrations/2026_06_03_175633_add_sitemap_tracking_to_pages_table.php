<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('sitemap_id', 26)->nullable()->after('website_id');
            $table->boolean('is_in_sitemap')->default(true)->after('sitemap_id');
            $table->foreign('sitemap_id')->references('id')->on('sitemaps')->nullOnDelete();
            $table->index('sitemap_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['sitemap_id']);
            $table->dropIndex(['sitemap_id']);
            $table->dropColumn(['sitemap_id', 'is_in_sitemap']);
        });
    }
};
