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
        Schema::create('background_check_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('background_check_id')->constrained()->onDelete('cascade');
            $table->string('amiqus_step_id')->index();
            $table->string('type');
            $table->decimal('cost', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('background_check_steps');
    }
};
