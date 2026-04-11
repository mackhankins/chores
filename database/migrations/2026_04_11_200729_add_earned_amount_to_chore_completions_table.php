<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chore_completions', function (Blueprint $table) {
            $table->decimal('earned_amount', 8, 2)->nullable()->after('completed_date');
        });
    }

    public function down(): void
    {
        Schema::table('chore_completions', function (Blueprint $table) {
            $table->dropColumn('earned_amount');
        });
    }
};
