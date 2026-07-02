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
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (!Schema::hasColumn('bordereau_lignes', 'specifications_techniques')) {
                $table->longText('specifications_techniques')->nullable();
            }
            if (!Schema::hasColumn('bordereau_lignes', 'specifications_obligatoires')) {
                $table->longText('specifications_obligatoires')->nullable();
            }
            if (!Schema::hasColumn('bordereau_lignes', 'specifications_proposees')) {
                $table->longText('specifications_proposees')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('bordereau_lignes', 'specifications_techniques')) {
                $table->dropColumn('specifications_techniques');
            }
            if (Schema::hasColumn('bordereau_lignes', 'specifications_obligatoires')) {
                $table->dropColumn('specifications_obligatoires');
            }
            if (Schema::hasColumn('bordereau_lignes', 'specifications_proposees')) {
                $table->dropColumn('specifications_proposees');
            }
        });
    }
};
