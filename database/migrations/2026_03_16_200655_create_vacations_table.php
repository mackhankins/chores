<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('child_vacation', function (Blueprint $table) {
            $table->foreignUlid('vacation_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('child_id')->constrained()->cascadeOnDelete();
            $table->primary(['vacation_id', 'child_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_vacation');
        Schema::dropIfExists('vacations');
    }
};
