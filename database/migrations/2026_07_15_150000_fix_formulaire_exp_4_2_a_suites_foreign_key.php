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
        Schema::table('formulaire_exp_4_2_a_suites', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_exp_4_2_a_suites', 'utilisateur_id')) {
                $table->dropForeign(['utilisateur_id']);
                $table->foreign('utilisateur_id')->references('id')->on('utilisateurs')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('formulaire_exp_4_2_a_suites', function (Blueprint $table) {
            $table->dropForeign(['utilisateur_id']);
            $table->foreign('utilisateur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
