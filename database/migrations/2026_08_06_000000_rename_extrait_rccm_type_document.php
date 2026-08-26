<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('types_documents')
            ->where('nom', "Copie legalisee de l'Extrait du RCCM")
            ->update(['nom' => "Déclaration de l'autorité contractante"]);
    }

    public function down(): void
    {
        DB::table('types_documents')
            ->where('nom', "Déclaration de l'autorité contractante")
            ->update(['nom' => "Copie legalisee de l'Extrait du RCCM"]);
    }
};
