<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    if (!Schema::hasTable('bordereau_lignes')) {
        Schema::create('bordereau_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bordereau_id')->constrained('bordereaux')->onDelete('cascade');
            $table->string('designation');
            $table->decimal('quantite', 10, 2)->default(0);
            $table->decimal('prix_unitaire', 15, 2)->default(0);
            $table->decimal('montant', 15, 2)->default(0);
            $table->timestamps();
        });
    }
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bordereau_lignes');
    }
};
