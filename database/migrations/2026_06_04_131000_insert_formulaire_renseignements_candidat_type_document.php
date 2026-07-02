<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('types_documents')->where('nom', 'Formulaire de renseignements sur le candidat')->exists()) {
            DB::table('types_documents')->insert([
                'nom' => 'Formulaire de renseignements sur le candidat',
                'type_formulaire' => 'formulaire',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('types_documents')->where('nom', 'Formulaire de renseignements sur le candidat')->delete();
    }
};
