<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('types_documents')
            ->where('nom', 'Description technique des services')
            ->update(['nom' => 'Description technique des fournitures/services']);
    }

    public function down(): void
    {
        DB::table('types_documents')
            ->where('nom', 'Description technique des fournitures/services')
            ->update(['nom' => 'Description technique des services']);
    }
};
