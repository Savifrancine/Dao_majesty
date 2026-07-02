<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Collection;

class DossierDocumentOrderTest extends TestCase
{
    public function test_existing_documents_keep_their_order_when_reordered(): void
    {
        $documents = collect([
            ['id' => 10, 'type_document_id' => 101, 'ordre' => 1, 'statut' => 'vide'],
            ['id' => 11, 'type_document_id' => 102, 'ordre' => 1, 'statut' => 'vide'],
            ['id' => 12, 'type_document_id' => 103, 'ordre' => 1, 'statut' => 'vide'],
        ]);

        $orderedTypes = [103, 101, 102];

        $documents = $documents->map(function ($document) use ($orderedTypes) {
            $document['ordre'] = array_search($document['type_document_id'], $orderedTypes, true) + 1;
            return $document;
        });

        $ordered = $documents->sortBy('ordre')->values()->pluck('type_document_id')->all();

        $this->assertSame($orderedTypes, $ordered);
    }
}
