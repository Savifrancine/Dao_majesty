<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;

class DossierIndexContinueButtonTest extends TestCase
{
    public function test_continue_button_uses_resume_route_for_in_progress_dossiers(): void
    {
        $dossier = new class {
            public $id = 42;
            public $nom_dossier = 'Dossier test';
            public $public_prive = 'prive';
            public $statut = 'en_cours';
            public $created_at;
            public $typeDossier;
            public $entreprise;
            public $documents;

            public function __construct()
            {
                $this->created_at = Carbon::parse('2026-01-15');
                $this->typeDossier = new class {
                    public $nom = 'Type de dossier';
                };
                $this->entreprise = new class {
                    public $nom = 'Entreprise test';
                };
                $this->documents = collect([new class {
                    public $id = 1;
                    public $type_document_id = 10;
                    public $ordre = 1;
                    public $statut = 'vide';
                    public $fichiers;

                    public function __construct()
                    {
                        $this->fichiers = collect();
                    }
                }]);
            }

            public function __toString(): string
            {
                return (string) $this->id;
            }
        };

        $view = view('dossiers.index', [
            'dossiers' => collect([$dossier]),
            'resumeInfo' => [
                $dossier->id => [
                    'current_index' => 0,
                    'document_type_ids' => [10],
                ],
            ],
        ])->render();

        $this->assertStringContainsString('/dossiers/42/continuer', $view);
    }
}
