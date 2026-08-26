<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            $table->string('formulaire_type')->default('A')->after('signataire_id');
        });

        // Ensure existing records are marked as A
        DB::table('formulaire_exp_4_2_as')->whereNull('formulaire_type')->update(['formulaire_type' => 'A']);
    }

    public function down()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            $table->dropColumn('formulaire_type');
        });
    }
};
