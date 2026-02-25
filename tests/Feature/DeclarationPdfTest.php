<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;

class DeclarationPdfTest extends TestCase
{
    /**
     * Test generation of declaration PDF via controller method.
     */
    public function test_generate_declaration_pdf()
    {
        $controller = new DocumentController();

        $data = [
            'societe' => 'Majesty Services',
            'date' => '2025-07-17',
            'declarant' => 'TCHABY Onésime',
            'fonction' => 'Le Gérant',
            'reference' => 'DRP-001',
        ];

        $request = Request::create('/documents/declaration/pdf', 'POST', $data);

        $response = $controller->generateDeclarationPDF($request);

        $this->assertNotNull($response);
        $contentType = $response->headers->get('Content-Type') ?? '';
        $this->assertStringContainsString('application/pdf', $contentType, 'La réponse doit être un PDF');
    }
}
