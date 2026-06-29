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
        Schema::create('note_links', function (Blueprint $table) {
            $table->foreignId('source_note_id')->constrained('notes')->cascadeOnDelete();
            $table->foreignId('target_note_id')->constrained('notes')->cascadeOnDelete();
            $table->primary(['source_note_id', 'target_note_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_links');
    }
};
