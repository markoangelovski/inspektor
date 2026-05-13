<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_ai_credits', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('url');
            $table->jsonb('translatable_content');
            $table->integer('word_count')->default(0);
            $table->decimal('credits_one_language', 10, 4)->default(0);
            $table->decimal('credits_five_languages', 10, 4)->default(0);
            $table->timestampTz('calculated_at');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_ai_credits');
    }
};
