<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('templates')) {
            Schema::create('templates', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->string('type'); // e.g. declaration, lettre, page_garde
                $table->text('content')->nullable(); // JSON or HTML
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('templates');
    }
};
