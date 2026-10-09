<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('types_documents')->where('nom', 'Liste de Matériel')->exists()) {
            DB::table('types_documents')->insert([
                'nom' => 'Liste de Matériel',
                'type_formulaire' => 'bordereau',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('types_documents')->where('nom', 'Liste de Matériel')->delete();
    }
};
