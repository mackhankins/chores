<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chore_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('chore_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('child_id')->nullable()->constrained('children')->cascadeOnDelete();
            $table->foreignUlid('rotation_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_assignments');
    }
};
