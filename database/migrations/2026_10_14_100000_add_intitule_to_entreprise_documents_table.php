<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->string('intitule')->nullable()->after('banque');
        });
    }

    public function down(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->dropColumn('intitule');
        });
    }
};
