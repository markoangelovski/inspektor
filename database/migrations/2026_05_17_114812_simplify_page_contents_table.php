<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            $table->dropUnique(['page_id', 'extractor_version']);
            $table->dropColumn('extractor_version');
            $table->unique('page_id');
        });
    }

    public function down(): void
    {
        Schema::table('page_contents', function (Blueprint $table) {
            $table->dropUnique(['page_id']);
            $table->string('extractor_version', 64)->index()->default('v1');
            $table->unique(['page_id', 'extractor_version']);
        });
    }
};
