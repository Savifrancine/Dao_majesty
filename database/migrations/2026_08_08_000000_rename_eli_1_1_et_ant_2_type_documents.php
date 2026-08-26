<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $renames = [
        'Formulaire de renseignements sur le candidat' => 'Formulaire ELI – 1.1 : Formulaire de renseignements sur le candidat',
        "Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges" => "Formulaire ANT-2 : Formulaire renseignant sur les antécédents de marchés non exécutés, de litiges en instance et d'antécédents de litiges",
    ];

    public function up(): void
    {
        foreach ($this->renames as $ancien => $nouveau) {
            DB::table('types_documents')->where('nom', $ancien)->update(['nom' => $nouveau]);
        }
    }

    public function down(): void
    {
        foreach ($this->renames as $ancien => $nouveau) {
            DB::table('types_documents')->where('nom', $nouveau)->update(['nom' => $ancien]);
        }
    }
};
