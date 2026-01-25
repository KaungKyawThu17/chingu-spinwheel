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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('age');
            $table->string('gender');
            $table->string('job_title');
            $table->string('drink_time');
            $table->string('drink_place');
            $table->string('drink_whom');
            $table->string('choose_reason');
            $table->string('drink_meal_important');
            $table->string('drink_meal_type');
            $table->string('drink_flavor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
