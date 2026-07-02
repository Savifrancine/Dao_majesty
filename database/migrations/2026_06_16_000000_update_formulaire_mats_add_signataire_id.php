<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            $table->foreignId('signataire_id')->nullable()->after('provenance')->constrained('signataires')->nullOnDelete();
            $table->dropColumn('nom_signataire');
        });
    }

    public function down()
    {
        Schema::table('formulaire_mats', function (Blueprint $table) {
            $table->string('nom_signataire')->nullable()->after('provenance');
            $table->dropConstrainedForeignId('signataire_id');
        });
    }
};
