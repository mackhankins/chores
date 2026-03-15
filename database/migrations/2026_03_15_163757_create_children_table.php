<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('pin', 4);
            $table->string('avatar_color');
            $table->time('notify_morning_at')->nullable();
            $table->time('notify_reminder_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
