<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('background_checks', function (Blueprint $table) {
            $table->string('perform_url')->nullable()->after('amiqus_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('background_checks', function (Blueprint $table) {
            $table->dropColumn('perform_url');
        });
    }
};
