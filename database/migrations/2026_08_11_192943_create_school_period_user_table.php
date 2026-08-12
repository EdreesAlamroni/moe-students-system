<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_period_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_period_id')->constrained('school_periods')->cascadeOnDelete();
            $table->primary(['user_id', 'school_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_period_user');
    }
};
