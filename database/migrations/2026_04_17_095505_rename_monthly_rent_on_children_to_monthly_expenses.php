<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->renameColumn('monthly_rent', 'monthly_expenses');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->renameColumn('monthly_expenses', 'monthly_rent');
        });
    }
};
