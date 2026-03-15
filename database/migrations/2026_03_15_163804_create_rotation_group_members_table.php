<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotation_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('rotation_group_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('child_id')->constrained('children')->cascadeOnDelete();
            $table->integer('position');
            $table->unique(['rotation_group_id', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rotation_group_members');
    }
};
