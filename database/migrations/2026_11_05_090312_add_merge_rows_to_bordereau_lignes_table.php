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
            if (!Schema::hasColumn('bordereau_lignes', 'merge_rows')) {
                $table->unsignedSmallInteger('merge_rows')->default(1)->after('specifications_proposees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bordereau_lignes', function (Blueprint $table) {
            if (Schema::hasColumn('bordereau_lignes', 'merge_rows')) {
                $table->dropColumn('merge_rows');
            }
        });
    }
};
