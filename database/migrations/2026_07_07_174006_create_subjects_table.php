<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('grade_level_id')->constrained('grade_levels')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('included_in_total_score');
            $table->boolean('needs_lab');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
