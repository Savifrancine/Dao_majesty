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
        Schema::create('champs_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_document_id')->constrained('types_documents')->onDelete('cascade');
            $table->string('nom_champ');
            $table->string('label');
            $table->enum('type', ['text', 'number', 'date'])->default('text');
            $table->integer('ordre')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('champs_documents');
    }
};
