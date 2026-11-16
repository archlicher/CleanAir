<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->cascadeOnDelete();
            $table->string('pollutant', 32);
            $table->timestamp('measured_at');
            $table->decimal('value', 10, 3);
            $table->string('unit', 32)->default('µg/m³');
            $table->string('validity')->nullable();
            $table->string('verification')->nullable();
            $table->unsignedTinyInteger('source_dataset')->nullable();

            $table->unique(['station_id', 'pollutant', 'measured_at']);
            $table->index(['pollutant', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
