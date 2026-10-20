<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('catalogue_articles', function (Blueprint $table) {
            $table->string('marque_fabricant')->nullable()->after('designation');
        });
    }

    public function down()
    {
        Schema::table('catalogue_articles', function (Blueprint $table) {
            $table->dropColumn('marque_fabricant');
        });
    }
};
