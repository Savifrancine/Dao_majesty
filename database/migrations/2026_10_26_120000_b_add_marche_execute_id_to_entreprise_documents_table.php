<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->foreignId('marche_execute_id')->nullable()->after('entreprise_id')->constrained('marches_executes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('entreprise_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('marche_execute_id');
        });
    }
};
