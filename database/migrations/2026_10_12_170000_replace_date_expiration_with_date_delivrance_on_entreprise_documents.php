<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->date('date_delivrance')->nullable()->after('annee');
            $table->dropColumn('date_expiration');
        });
    }

    public function down(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->date('date_expiration')->nullable()->after('annee');
            $table->dropColumn('date_delivrance');
        });
    }
};
