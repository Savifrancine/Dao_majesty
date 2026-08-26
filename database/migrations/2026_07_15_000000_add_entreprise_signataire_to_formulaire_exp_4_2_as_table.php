<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            if (!Schema::hasColumn('formulaire_exp_4_2_as', 'entreprise_id')) {
                $table->foreignId('entreprise_id')->nullable()->constrained('entreprises')->nullOnDelete();
            }

            if (!Schema::hasColumn('formulaire_exp_4_2_as', 'signataire_id')) {
                $table->foreignId('signataire_id')->nullable()->constrained('signataires')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('formulaire_exp_4_2_as', function (Blueprint $table) {
            if (Schema::hasColumn('formulaire_exp_4_2_as', 'entreprise_id')) {
                $table->dropConstrainedForeignId('entreprise_id');
            }

            if (Schema::hasColumn('formulaire_exp_4_2_as', 'signataire_id')) {
                $table->dropConstrainedForeignId('signataire_id');
            }
        });
    }
};
