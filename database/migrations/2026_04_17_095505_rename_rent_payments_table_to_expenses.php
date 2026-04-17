<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('rent_payments', 'expenses');
    }

    public function down(): void
    {
        Schema::rename('expenses', 'rent_payments');
    }
};
