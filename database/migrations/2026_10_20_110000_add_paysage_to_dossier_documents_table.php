<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            $table->boolean('paysage')->default(false)->after('reference_model');
        });
    }

    public function down(): void
    {
        Schema::table('dossier_documents', function (Blueprint $table) {
            $table->dropColumn('paysage');
        });
    }
};
