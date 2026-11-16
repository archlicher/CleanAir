<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_air_qualities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->string('pollutant', 32);
            $table->date('date');
            $table->decimal('mean_value', 10, 3);
            $table->unsignedSmallInteger('valid_hours');
            $table->decimal('coverage_ratio', 5, 4);
            $table->unsignedTinyInteger('source_dataset')->nullable();

            $table->unique(['station_id', 'pollutant', 'date']);
            $table->index(['city_id', 'pollutant', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_air_qualities');
    }
};
