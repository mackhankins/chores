<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chore_misses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('chore_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('child_id')->constrained('children')->cascadeOnDelete();
            $table->date('missed_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['chore_id', 'child_id', 'missed_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_misses');
    }
};
