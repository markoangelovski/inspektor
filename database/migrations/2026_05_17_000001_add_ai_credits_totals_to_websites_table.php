<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->unsignedInteger('ai_credits_page_count')->nullable();
            $table->unsignedInteger('ai_credits_word_count')->nullable();
            $table->decimal('ai_credits_one_language', 12, 4)->nullable();
            $table->decimal('ai_credits_five_languages', 12, 4)->nullable();
            $table->unsignedInteger('ai_credits_unique_word_count')->nullable();
            $table->decimal('ai_credits_unique_one_language', 12, 4)->nullable();
            $table->decimal('ai_credits_unique_five_languages', 12, 4)->nullable();
            $table->timestamp('ai_credits_calculated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'ai_credits_page_count',
                'ai_credits_word_count',
                'ai_credits_one_language',
                'ai_credits_five_languages',
                'ai_credits_unique_word_count',
                'ai_credits_unique_one_language',
                'ai_credits_unique_five_languages',
                'ai_credits_calculated_at',
            ]);
        });
    }
};
