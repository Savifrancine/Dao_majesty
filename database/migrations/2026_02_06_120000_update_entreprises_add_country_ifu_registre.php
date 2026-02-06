<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->string('pays')->nullable()->after('email');
            $table->string('ifu')->nullable()->after('pays');
            $table->string('registre_path')->nullable()->after('ifu');
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['pays','ifu','registre_path']);
        });
    }
};
