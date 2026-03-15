<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chore_completions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('chore_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('completed_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_completions');
    }
};
