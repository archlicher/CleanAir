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
        Schema::create('daily_air_quality', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('pollutant');
            $table->decimal('average_value', 10, 2);
            $table->integer('valid_measurements');     
            $table->integer('expected_measurements');     
            $table->decimal('coverage_percentage', 10, 2);
            $table->integer('station_count');    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_air_quality');
    }
};
