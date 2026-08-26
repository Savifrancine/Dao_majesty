<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            $table->decimal('droits_douane', 15, 2)->nullable()->after('taxe_vente');
        });
    }

    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            $table->dropColumn('droits_douane');
        });
    }
};
