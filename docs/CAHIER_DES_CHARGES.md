# Cahier des charges — Plateforme DAO SaaS

Transformer l'outil interne de gestion de Dossiers d'Appel d'Offres (DAO) en produit SaaS public multi-tenant, avec l'intégration IA comme axe de différenciation.

| | |
|---|---|
| **Référence** | CDC-DAO-2026-08 |
| **Version** | 1.0 |
| **Date** | 05/08/2026 |
| **Base technique** | Laravel 12 · PHP 8.2 |
| **Domaine** | Marchés publics — Bénin / Afrique de l'Ouest |
| **Statut** | Brouillon pour cadrage |

## Sommaire

1. [Contexte](#01--contexte)
2. [Objectifs](#02--objectifs)
3. [Utilisateurs cibles](#03--utilisateurs-cibles)
4. [État des lieux](#04--état-des-lieux--constats-de-laudit)
5. [Évolutions fonctionnelles à apporter](#05--évolutions-fonctionnelles-à-apporter)
6. [Module Intelligence Artificielle](#06--module-intelligence-artificielle)
7. [Architecture & outils informatiques](#07--architecture--outils-informatiques)
8. [Données multi-tenant](#08--données-multi-tenant)
9. [Sécurité & conformité](#09--sécurité--conformité)
10. [Modèle économique](#10--modèle-économique)
11. [Feuille de route](#11--feuille-de-route)
12. [Équipe nécessaire](#12--équipe-nécessaire)
13. [Risques](#13--risques)
14. [Critères de succès](#14--critères-de-succès)

---

## 01 — Contexte

L'application gère aujourd'hui la constitution de Dossiers d'Appel d'Offres (DAO) : création par étapes, génération des pièces normalisées (lettre de soumission, formulaires MAT / PER / EXP 4.2 a-b, bordereaux de prix), rattachement d'entreprises soumissionnaires, de signataires et de justificatifs administratifs (RCCM, IFU, CNSS…), export PDF.

Elle a été construite comme un outil interne à une seule structure : les rôles (`admin`, `directeur`, `employe`) supposent une organisation unique qui monte des dossiers pour le compte de tiers. Aucune notion de compte client isolé n'existe dans le schéma actuel.

L'objectif porté par ce cahier des charges est de faire évoluer ce socle vers un produit SaaS ouvert : chaque entreprise ou cabinet de conseil qui répond à des appels d'offres publics doit pouvoir créer son propre compte, inviter son équipe, et constituer ses dossiers en toute étanchéité vis-à-vis des autres comptes.

## 02 — Objectifs

- **Ouvrir le produit** à toute entreprise ou cabinet répondant à des appels d'offres, en libre inscription, sans dépendre d'un déploiement dédié par client.
- **Réduire le temps de constitution d'un dossier** — c'est la douleur principale de l'utilisateur cible, et le terrain où l'IA apporte la valeur la plus mesurable.
- **Fiabiliser les dossiers déposés** en détectant en amont les pièces manquantes, expirées ou non conformes au cahier des charges de l'appel d'offres visé.
- **Monétiser par abonnement**, avec un palier gratuit limité pour l'acquisition et des paliers payants portés par le volume de dossiers et l'accès aux fonctions IA.
- **Rester livrable rapidement** : capitaliser sur l'existant (Laravel, logique métier, gabarits de documents déjà écrits) plutôt que de reconstruire.

## 03 — Utilisateurs cibles

| Profil | Besoin principal | Sensibilité |
|---|---|---|
| PME / entreprise soumissionnaire | Monter un dossier complet et conforme sans expertise juridique interne | Prix, simplicité |
| Cabinet de conseil en marchés publics | Gérer plusieurs entreprises clientes et dossiers en parallèle | Multi-comptes, collaboration |
| Grande entreprise / groupement | Réutiliser des pièces d'un dossier à l'autre, tracer les responsabilités internes | Rôles fins, audit |

Ces trois profils partagent une contrainte commune à concevoir dès le départ : un même utilisateur peut appartenir à **plusieurs comptes** (le consultant qui gère les dossiers de trois clients), ce qui oriente le modèle multi-tenant vers une relation utilisateur ↔ organisation en plusieurs-à-plusieurs plutôt qu'un utilisateur rattaché à une seule organisation.

## 04 — État des lieux — constats de l'audit

L'analyse du code actuel fait ressortir les points suivants, qui conditionnent tous les articles suivants :

> **Fuite de données déjà active** — Le tableau de bord (`/home`) affiche `Dossier::all()` sans filtre : tout utilisateur connecté voit les dossiers de tous les autres. À corriger avant toute ouverture publique, indépendamment du chantier SaaS.

- Routes `signataires`, `entreprises`, `templates` déclarées hors du middleware d'authentification — accessibles sans connexion.
- Authentification maison (closures dans `routes/web.php`) : pas de vérification d'e-mail, pas de réinitialisation de mot de passe, pas de throttle anti force-brute.
- Aucune colonne de rattachement à un compte (tenant) sur `Dossier`, `Entreprise`, `Signataire`, `Template`.
- Stockage des fichiers en disque local, sans cloisonnement par client.
- Dump de base (`dao.sql`) présent à la racine, non exclu par `.gitignore`.

Le socle métier (wizard, génération PDF, gabarits de formulaires) est en revanche solide et réutilisable tel quel — le travail à venir est structurel, pas une réécriture.

## 05 — Évolutions fonctionnelles à apporter

**Comptes & accès**
- Création d'un compte = création d'une **organisation** (le tenant), avec inscription en libre-service.
- Invitation de collègues par e-mail, rôles scopés à l'organisation (admin, gestionnaire de dossier, contributeur, lecteur).
- Un utilisateur peut appartenir à plusieurs organisations (cas du consultant).
- Vérification d'e-mail, réinitialisation de mot de passe, journal de connexion.

**Cycle de vie du dossier**
- Alertes automatiques sur échéance de dépôt et sur expiration de pièces (RCCM, attestations) déjà en base.
- Bibliothèque de pièces réutilisables d'un dossier à l'autre au sein d'une même organisation.
- Journal d'audit : qui a créé, modifié ou soumis quoi — attendu dans un contexte de marchés publics où la traçabilité est souvent exigée en interne.

**Plateforme**
- Facturation par abonnement, page de gestion du plan et des moyens de paiement.
- Export / API pour les organisations qui veulent connecter leurs propres outils.

## 06 — Module Intelligence Artificielle

Axe de différenciation validé en priorité : l'IA n'est pas un gadget ajouté après coup mais le levier qui justifie l'abonnement payant, en s'appliquant directement à la douleur du métier — la constitution manuelle et répétitive de dossiers administratifs.

| Fonctionnalité | Priorité | Description | Outils |
|---|---|---|---|
| Extraction automatique depuis les pièces jointes | **P0** | Dépôt d'un RCCM, IFU, attestation CNSS scannés → remplissage automatique des champs de l'entreprise | OCR (Tesseract/cloud) + Claude API |
| Pré-remplissage depuis l'avis d'appel d'offres | **P0** | Dépôt du PDF de l'avis → extraction du numéro d'AO, objet, lots, dates, seuils exigés | Claude API |
| Vérification de conformité | **P0** | Comparaison pièces exigées vs pièces fournies (présence, seuils financiers, dates de validité) — fonctionnalité la plus vendable | Claude API |
| Assistant de rédaction | P1 | Brouillon assisté pour lettre de soumission, méthodologie, description technique | Claude API |
| Recherche sémantique dans l'historique | P2 | Retrouver et réutiliser des réponses déjà rédigées sur un dossier similaire | Embeddings + recherche vectorielle |

> **Point d'attention** — Isoler strictement par organisation toute donnée envoyée aux services d'IA, et informer les clients de cet usage : des documents administratifs sensibles transitent par ces fonctions.

## 07 — Architecture & outils informatiques

| Domaine | Choix recommandé | Pourquoi |
|---|---|---|
| Backend | Laravel 12 / PHP 8.2 | Déjà en place, écosystème SaaS complet (Fortify, Cashier, Horizon) |
| Base de données | MySQL/MariaDB + `tenant_id` | Isolation logique simple, coût d'exploitation faible au démarrage |
| File d'attente | Redis + Laravel Horizon | Traiter PDF, OCR et appels IA en tâche de fond |
| Stockage fichiers | S3 / Cloudflare R2 | Cloisonnement par organisation, indépendant du serveur applicatif |
| IA générative | Claude API | Extraction, rédaction, vérification de conformité |
| OCR | Tesseract / service cloud | Lecture des scans avant extraction IA |
| Paiement | Stripe + Mobile Money (Kkiapay, FedaPay) | Couverture carte internationale et usages locaux Bénin / Afrique de l'Ouest |
| E-mail transactionnel | Postmark / SES | Invitations, alertes d'échéance |
| Supervision | Sentry + Laravel Pulse | Erreurs et performance dès le lancement |
| CI/CD | GitHub Actions | Tests + déploiement automatisés |

## 08 — Données multi-tenant

Approche recommandée : base partagée avec une table `organisations` et une colonne `organisation_id` sur chaque table sensible (`dossiers`, `entreprises`, `signataires`, `templates`, `formulaire_*`), appliquée automatiquement via un global scope Eloquent — aucune requête ne doit pouvoir omettre le filtre par erreur.

Table pivot `organisation_utilisateur` pour gérer l'appartenance multiple (cf. Article 03), avec le rôle porté sur le pivot plutôt que sur l'utilisateur.

## 09 — Sécurité & conformité

- Isolation stricte entre organisations à chaque niveau : requêtes, stockage, files d'attente, journaux.
- Chiffrement au repos des pièces sensibles (identifiants fiscaux, pièces d'identité des signataires).
- Journal d'audit immuable des actions sur les dossiers.
- Tests automatisés dédiés à la non-fuite de données entre comptes — à écrire avant l'ouverture publique.

## 10 — Modèle économique

| Palier | Cible | Inclus |
|---|---|---|
| Gratuit | Découverte | 1 dossier actif, sans fonctions IA |
| Starter | PME | Dossiers illimités, vérification de conformité IA |
| Cabinet | Consultants multi-clients | Multi-organisations, extraction IA, assistant de rédaction |

Paiement carte (Stripe) et mobile money pour coller aux usages du marché local.

## 11 — Feuille de route

| Phase | Contenu | Durée estimée |
|---|---|---|
| 0 | Corrections de sécurité immédiates (fuite dashboard, routes non protégées, .gitignore) | 3–5 jours |
| 1 | Fondations multi-tenant (organisations, tenant_id, global scopes) | 2–3 semaines |
| 2 | Authentification & onboarding SaaS, facturation | 2 semaines |
| 3 | Infrastructure (stockage cloud, files d'attente) | 1 semaine |
| 4 | Module IA — extraction, pré-remplissage, vérification de conformité | 3–4 semaines |
| 5 | Durcissement, tests d'isolation, lancement bêta | 2 semaines |

Total indicatif : **12 à 14 semaines** pour une petite équipe, jusqu'à une bêta publique avec le module IA de base.

## 12 — Équipe nécessaire

- 1 développeur backend Laravel (senior de préférence, vu la sensibilité du chantier multi-tenant)
- 1 profil frontend / UI, à temps partiel, pour l'onboarding et le tableau de bord
- Intégration IA / OCR pouvant être portée par le même développeur backend avec l'API Claude
- QA / tests, en particulier sur l'isolation entre comptes avant le lancement

## 13 — Risques

- **Qualité des scans** : les pièces déposées sur le terrain sont parfois de mauvaise qualité — l'OCR doit prévoir un mode de correction manuelle.
- **Coût des appels IA** à surveiller si l'usage monte en volume — prévoir un plafond par plan.
- **Adoption** : une partie des utilisateurs cibles est peu digitalisée — l'onboarding doit rester simple, indépendamment de la richesse des fonctions IA.
- **Cadre réglementaire des marchés publics**, qui peut varier selon les pays si le produit s'étend au-delà du Bénin.

## 14 — Critères de succès

- Réduction mesurable du temps de constitution d'un dossier entre le premier usage et l'usage assisté par IA.
- Taux de dossiers déposés sans pièce manquante grâce à la vérification de conformité.
- Taux de conversion du palier gratuit vers un palier payant.
- Zéro incident de fuite de données entre organisations.

---

*Document de cadrage — à affiner avec le porteur du projet avant chiffrage définitif.*
