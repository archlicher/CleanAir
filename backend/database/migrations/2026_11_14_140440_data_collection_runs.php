<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_collection_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->index();
            $table->integer('records_imported')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_collection_runs');
    }
};
