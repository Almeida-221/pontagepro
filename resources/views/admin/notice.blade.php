@extends('layouts.admin')

@section('title', 'Notice d\'utilisation — SB Pointage')

@section('page-title', 'Notice d\'utilisation client')

@push('scripts')
<script>
  function printNotice() { window.print(); }
</script>
<style>
  @media print {
    /* Masquer tout le chrome de l'admin */
    aside, header, .no-print { display: none !important; }
    body { background: #fff !important; }
    .main-content { padding: 0 !important; }
    .print-page { box-shadow: none !important; border-radius: 0 !important; }
    @page { size: A4; margin: 15mm 12mm; }
    .step-card { break-inside: avoid; }
  }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto py-6">

  {{-- Barre d'actions --}}
  <div class="flex items-center justify-between mb-6 no-print">
    <div>
      <h2 class="text-xl font-bold text-slate-800">📋 Notice d'utilisation — Vitrine & Inscription</h2>
      <p class="text-sm text-slate-500 mt-1">Document destiné aux clients · À imprimer et remettre lors de l'onboarding</p>
    </div>
    <button onclick="printNotice()"
      class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-lg shadow transition">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
      </svg>
      Imprimer / Télécharger PDF
    </button>
  </div>

  {{-- Document principal --}}
  <div class="print-page bg-white rounded-2xl shadow-lg overflow-hidden">

    {{-- En-tête --}}
    <div style="background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 60%, #2563eb 100%); padding: 36px 40px 28px;">
      <div style="display:flex; align-items:center; gap:14px; margin-bottom:16px">
        <div style="width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px">⏱</div>
        <div>
          <div style="color:rgba(255,255,255,.7);font-size:10pt;letter-spacing:1px;text-transform:uppercase">Notice d'utilisation officielle</div>
          <div style="color:#fff;font-size:16pt;font-weight:800">SB Pointage · Guide de démarrage</div>
        </div>
      </div>
      <div style="color:rgba(255,255,255,.85);font-size:12pt;font-weight:300">
        De la visite du site web jusqu'à la création de votre premier compte administrateur
      </div>
      <div style="display:flex;gap:14px;margin-top:18px;flex-wrap:wrap">
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:5px 14px;color:#fff;font-size:9.5pt">📅 {{ now()->format('d/m/Y') }}</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:5px 14px;color:#fff;font-size:9.5pt">🔒 Usage super admin uniquement</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:5px 14px;color:#fff;font-size:9.5pt">🌐 SB Pointage — Gestion des présences</div>
      </div>
    </div>

    <div style="padding: 32px 40px 40px;">

      {{-- Introduction --}}
      <div style="background:#EFF6FF;border-left:4px solid #2563EB;border-radius:0 8px 8px 0;padding:14px 18px;margin-bottom:28px;font-size:10pt;color:#1e3a5f;line-height:1.6">
        <strong>À qui s'adresse ce document ?</strong><br>
        Ce guide est destiné aux <strong>responsables d'entreprise</strong> (gérants, DRH, chefs de chantier) qui souhaitent utiliser la plateforme SB Pointage pour gérer les présences de leurs ouvriers ou agents de sécurité. Suivez les étapes dans l'ordre pour démarrer rapidement.
      </div>

      {{-- ══ ÉTAPE 1 — VITRINE ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#EFF6FF,#F0F9FF);border-bottom:2px solid #BFDBFE">
          <div style="width:36px;height:36px;background:#2563EB;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">1</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#1e3a5f">🌐 Visiter le site web</div>
            <div style="font-size:9.5pt;color:#64748b">Découvrez les fonctionnalités et les offres disponibles</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Ouvrez votre navigateur (Chrome, Firefox, Edge…) et saisissez l'adresse du site SB Pointage.
            La page d'accueil présente l'application et toutes ses fonctionnalités.
          </p>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px">
              <div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:6px">📱 Ce que vous verrez</div>
              <ul style="font-size:9pt;color:#555;line-height:1.8;padding-left:14px">
                <li>Présentation de l'application mobile</li>
                <li>Les 5 fonctionnalités principales</li>
                <li>Les tarifs par activité et par plan</li>
                <li>Les téléchargements des applications</li>
              </ul>
            </div>
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px">
              <div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:6px">🔑 Boutons d'action</div>
              <ul style="font-size:9pt;color:#555;line-height:1.8;padding-left:14px">
                <li><strong>Démarrer gratuitement</strong> → Inscription</li>
                <li><strong>Se connecter</strong> → Espace client</li>
                <li><strong>Voir les tarifs</strong> → Section prix</li>
                <li><strong>Télécharger</strong> → Apps mobiles</li>
              </ul>
            </div>
          </div>
          <div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">
            💡 <strong>Conseil :</strong> Parcourez la section <em>"Comment ça marche"</em> et la section <em>"Tarifs"</em> avant de vous inscrire pour choisir le plan le mieux adapté à votre activité.
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 2 — CHOISIR MODULE ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#F0FDF4,#ECFDF5);border-bottom:2px solid #86EFAC">
          <div style="width:36px;height:36px;background:#16A34A;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">2</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#14532D">🏗️ Choisir votre activité</div>
            <div style="font-size:9.5pt;color:#64748b">Sélectionnez le module qui correspond à votre secteur</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Cliquez sur <strong>"Démarrer gratuitement"</strong>. Vous arrivez sur la page de sélection de l'activité. Trois modules sont disponibles :
          </p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🏗️</div>
              <div style="font-weight:700;font-size:10pt;color:#9A3412">Pointage Bâtiment</div>
              <div style="font-size:8.5pt;color:#78350F;margin-top:4px">Chantiers, construction, ouvriers journaliers</div>
            </div>
            <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🌾</div>
              <div style="font-weight:700;font-size:10pt;color:#14532D">Pointage Agriculture</div>
              <div style="font-size:8.5pt;color:#166534;margin-top:4px">Exploitation agricole, saisonniers</div>
            </div>
            <div style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:10px;padding:14px;text-align:center">
              <div style="font-size:22px;margin-bottom:6px">🛡️</div>
              <div style="font-weight:700;font-size:10pt;color:#1e3a5f">Sécurité Privée</div>
              <div style="font-size:8.5pt;color:#1e40af;margin-top:4px">Agences de sécurité, agents de garde</div>
            </div>
          </div>
          <p style="font-size:9.5pt;color:#555">
            Cliquez sur votre activité pour accéder aux plans tarifaires correspondants.
          </p>
        </div>
      </div>

      {{-- ══ ÉTAPE 3 — CHOISIR PLAN ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#FDF4FF,#FAF5FF);border-bottom:2px solid #D8B4FE">
          <div style="width:36px;height:36px;background:#9333EA;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">3</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#4C1D95">💳 Choisir un plan tarifaire</div>
            <div style="font-size:9.5pt;color:#64748b">Sélectionnez l'offre adaptée à la taille de votre équipe</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Chaque module propose plusieurs plans selon le nombre d'ouvriers/agents à gérer. Comparez les offres et cliquez sur <strong>"Choisir ce plan"</strong>.
          </p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#F8FAFC;border:1px solid #CBD5E1;border-radius:8px;padding:12px;text-align:center">
              <div style="font-weight:700;font-size:10pt;color:#334155">Plan Starter</div>
              <div style="font-size:8.5pt;color:#64748b;margin-top:4px">Petites équipes<br/>Jusqu'à 20 ouvriers</div>
            </div>
            <div style="background:#EDE9FE;border:2px solid #8B5CF6;border-radius:8px;padding:12px;text-align:center;position:relative">
              <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#8B5CF6;color:#fff;font-size:8pt;font-weight:700;padding:2px 10px;border-radius:12px">Populaire</div>
              <div style="font-weight:700;font-size:10pt;color:#4C1D95">Plan Standard</div>
              <div style="font-size:8.5pt;color:#5B21B6;margin-top:4px">Équipes moyennes<br/>Jusqu'à 50 ouvriers</div>
            </div>
            <div style="background:#F8FAFC;border:1px solid #CBD5E1;border-radius:8px;padding:12px;text-align:center">
              <div style="font-weight:700;font-size:10pt;color:#334155">Plan Premium</div>
              <div style="font-size:8.5pt;color:#64748b;margin-top:4px">Grandes entreprises<br/>Ouvriers illimités</div>
            </div>
          </div>
          <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#7F1D1D">
            ⚠️ <strong>Attention :</strong> Choisissez bien le nombre maximal d'ouvriers selon votre équipe — vous pourrez toujours upgrader plus tard depuis votre espace client.
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 4 — INFOS PROPRIÉTAIRE ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#FFF7ED,#FFFBEB);border-bottom:2px solid #FED7AA">
          <div style="width:36px;height:36px;background:#EA580C;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">4</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#7C2D12">👤 Saisir vos informations personnelles</div>
            <div style="font-size:9.5pt;color:#64748b">Renseignez les données du propriétaire / responsable légal</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Remplissez le formulaire avec vos informations personnelles. Ces données servent à créer votre <strong>compte client</strong> sur la plateforme.
          </p>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px">
            @php
              $champsProp = [
                ['Prénom *', 'Votre prénom'],
                ['Nom *', 'Votre nom de famille'],
                ['Email *', 'ex: jean@monentreprise.com (sert à la connexion)'],
                ['Téléphone *', 'ex: +221 77 000 00 00'],
                ['Adresse', 'Votre adresse personnelle'],
                ['Mot de passe *', 'Minimum 8 caractères — gardez-le précieusement'],
              ];
            @endphp
            @foreach($champsProp as [$label, $desc])
            <div style="background:#FFFBF5;border:1px solid #FED7AA;border-radius:6px;padding:8px 12px">
              <div style="font-weight:700;font-size:9.5pt;color:#9A3412">{{ $label }}</div>
              <div style="font-size:8.5pt;color:#78350F">{{ $desc }}</div>
            </div>
            @endforeach
          </div>
          <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#14532D">
            ✅ <strong>Important :</strong> L'adresse email sera votre identifiant de connexion au tableau de bord. Utilisez une adresse professionnelle active.
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 5 — INFOS ENTREPRISE ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#F0FDFA,#ECFEFF);border-bottom:2px solid #A7F3D0">
          <div style="width:36px;height:36px;background:#0D9488;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">5</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#134E4A">🏢 Informations de votre entreprise</div>
            <div style="font-size:9.5pt;color:#64748b">Renseignez les données de la société à gérer</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Cette étape crée le profil de votre entreprise dans le système. Toutes vos activités (ouvriers, présences, rapports) seront rattachées à cette entreprise.
          </p>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px">
            @php
              $champsEnt = [
                ['Nom de l\'entreprise *', 'ex: BTP Diallo & Frères'],
                ['Adresse de l\'entreprise *', 'ex: Avenue Bourguiba, Dakar'],
              ];
            @endphp
            @foreach($champsEnt as [$label, $desc])
            <div style="background:#F0FDFA;border:1px solid #A7F3D0;border-radius:6px;padding:8px 12px">
              <div style="font-weight:700;font-size:9.5pt;color:#0D9488">{{ $label }}</div>
              <div style="font-size:8.5pt;color:#134E4A">{{ $desc }}</div>
            </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 6 — PAIEMENT ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#FFF1F2,#FFF5F5);border-bottom:2px solid #FECDD3">
          <div style="width:36px;height:36px;background:#DC2626;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">6</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#7F1D1D">💰 Confirmer le paiement</div>
            <div style="font-size:9.5pt;color:#64748b">Validez votre abonnement via le mode de paiement disponible</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Pour les plans payants, choisissez votre mode de paiement parmi ceux disponibles. Pour les offres gratuites ou les périodes d'essai, cette étape est automatiquement validée.
          </p>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px">
            @foreach(['📱 Orange Money', '〰️ Wave', '💳 Visa / MasterCard', '🏦 Virement bancaire'] as $mode)
            <div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:20px;padding:6px 14px;font-size:9.5pt;font-weight:600;color:#9F1239">{{ $mode }}</div>
            @endforeach
          </div>
          <div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">
            💡 <strong>Plan gratuit ou essai :</strong> Votre compte est activé immédiatement sans paiement. Pour les plans payants, votre compte sera activé dès confirmation du paiement par l'administrateur.
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 7 — CONNEXION ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#EFF6FF,#EEF2FF);border-bottom:2px solid #A5B4FC">
          <div style="width:36px;height:36px;background:#4F46E5;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">7</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#1e1b4b">🔐 Se connecter à l'espace client</div>
            <div style="font-size:9.5pt;color:#64748b">Accédez à votre tableau de bord après activation du compte</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <p style="font-size:10pt;color:#374151;margin-bottom:14px">
            Une fois votre compte activé, rendez-vous sur la page <strong>Connexion</strong> du site ou cliquez sur <strong>"Se connecter"</strong> dans le menu. Saisissez votre email et mot de passe définis lors de l'inscription.
          </p>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">📧</div>
              <div style="font-size:9pt;font-weight:600;color:#334155">Identifiant</div>
              <div style="font-size:8.5pt;color:#64748b">Votre adresse email</div>
            </div>
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">🔑</div>
              <div style="font-size:9pt;font-weight:600;color:#334155">Mot de passe</div>
              <div style="font-size:8.5pt;color:#64748b">Défini à l'inscription</div>
            </div>
            <div style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">🏠</div>
              <div style="font-size:9pt;font-weight:600;color:#1e3a5f">Destination</div>
              <div style="font-size:8.5pt;color:#1e40af">Tableau de bord client</div>
            </div>
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 8 — CRÉER ADMIN ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:2px solid #2563EB;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#1e3a5f,#1d4ed8);border-bottom:2px solid #1d4ed8">
          <div style="width:36px;height:36px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#1d4ed8;font-weight:900;font-size:16pt;flex-shrink:0">8</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#fff">⭐ Créer le premier administrateur de l'entreprise</div>
            <div style="font-size:9.5pt;color:rgba(255,255,255,.8)">Étape clé — L'admin gère les ouvriers et les présences au quotidien</div>
          </div>
        </div>
        <div style="padding:18px 20px;background:#F8FBFF">
          <p style="font-size:10pt;color:#374151;margin-bottom:16px">
            Dans votre <strong>espace client</strong>, cliquez sur <strong>"Admins"</strong> dans le menu latéral, puis sur <strong>"Ajouter un administrateur"</strong>.
            Cet administrateur aura accès à la gestion quotidienne (présences, rapports, ouvriers).
          </p>
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:16px">
            <div>
              <div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">Informations à renseigner :</div>
              @php
                $champsAdmin = [
                  'Prénom et Nom' => 'Identité de l\'administrateur',
                  'Téléphone' => 'Numéro de l\'admin (connexion app mobile)',
                  'Email' => 'Pour recevoir ses identifiants',
                  'Code PIN' => '4 à 6 chiffres — pour l\'application mobile',
                  'Mot de passe' => 'Pour le tableau de bord web',
                ];
              @endphp
              @foreach($champsAdmin as $champ => $desc)
              <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px">
                <div style="width:6px;height:6px;background:#2563EB;border-radius:50%;margin-top:5px;flex-shrink:0"></div>
                <div style="font-size:9.5pt;color:#374151"><strong>{{ $champ }}</strong> — {{ $desc }}</div>
              </div>
              @endforeach
            </div>
            <div style="background:#E0F2FE;border-radius:10px;padding:14px">
              <div style="font-size:9.5pt;font-weight:700;color:#0369A1;margin-bottom:8px">🔐 Droits de l'administrateur</div>
              <ul style="font-size:9pt;color:#0c4a6e;line-height:1.9;padding-left:14px">
                <li>Gérer la liste des ouvriers / agents</li>
                <li>Enregistrer les présences quotidiennes</li>
                <li>Consulter les rapports et statistiques</li>
                <li>Gérer les zones et postes (sécurité)</li>
                <li>Valider les absences et remplacements</li>
                <li>Exporter les données (PDF / Excel)</li>
              </ul>
            </div>
          </div>
          <div style="background:#DBEAFE;border:1.5px solid #93C5FD;border-radius:8px;padding:12px 16px;font-size:9.5pt;color:#1e3a5f">
            📧 <strong>L'administrateur reçoit automatiquement un email</strong> contenant ses identifiants de connexion dès la création de son compte. Il peut se connecter sur le tableau de bord web ou l'application mobile.
          </div>
        </div>
      </div>

      {{-- ══ ÉTAPE 9 — APP MOBILE ══ --}}
      <div class="step-card" style="margin-bottom:24px; border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:linear-gradient(90deg,#F0FDF4,#ECFDF5);border-bottom:2px solid #86EFAC">
          <div style="width:36px;height:36px;background:#16A34A;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:16pt;flex-shrink:0">9</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:#14532D">📱 Télécharger et utiliser l'application mobile</div>
            <div style="font-size:9.5pt;color:#64748b">L'admin et les ouvriers/agents utilisent l'app pour les présences</div>
          </div>
        </div>
        <div style="padding:18px 20px">
          <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:14px">
            <div style="background:#F8FAFC;border:1.5px solid #CBD5E1;border-radius:10px;padding:14px">
              <div style="font-size:10pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">🏗️ SB Pointage<br/><span style="font-size:8.5pt;font-weight:400;color:#64748b">Bâtiment / Agriculture</span></div>
              <ul style="font-size:9pt;color:#374151;line-height:1.9;padding-left:14px">
                <li>Disponible sur Android (Play Store ou APK)</li>
                <li>L'admin marque les présences</li>
                <li>Un tap par ouvrier — mode hors-ligne</li>
                <li>Calcul automatique des salaires</li>
              </ul>
            </div>
            <div style="background:#F8FAFC;border:1.5px solid #CBD5E1;border-radius:10px;padding:14px">
              <div style="font-size:10pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">🛡️ SB Sécurité<br/><span style="font-size:8.5pt;font-weight:400;color:#64748b">Agences de sécurité</span></div>
              <ul style="font-size:9pt;color:#374151;line-height:1.9;padding-left:14px">
                <li>App dédiée agents et gérants de zone</li>
                <li>Pointage GPS avec validation de position</li>
                <li>Sessions de présence en temps réel</li>
                <li>Gestion des tours et plannings</li>
              </ul>
            </div>
          </div>
          <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#14532D">
            🔑 <strong>Connexion app mobile :</strong> L'admin se connecte avec son <strong>numéro de téléphone</strong> + <strong>code PIN</strong> (défini lors de la création du compte admin).
          </div>
        </div>
      </div>

      {{-- ══ RÉCAPITULATIF ══ --}}
      <div style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);border-radius:12px;padding:24px 28px;margin-bottom:8px">
        <div style="font-size:12pt;font-weight:800;color:#fff;margin-bottom:16px">🗺️ Récapitulatif du parcours</div>
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:0">
          @php
            $steps = ['Vitrine','Module','Plan','Proprio','Entreprise','Paiement','Connexion','1er Admin','App mobile'];
            $colors = ['#60A5FA','#34D399','#C084FC','#FB923C','#2DD4BF','#F87171','#818CF8','#FDE68A','#6EE7B7'];
          @endphp
          @foreach($steps as $i => $s)
          <div style="text-align:center;padding:6px 0">
            <div style="width:32px;height:32px;background:{{ $colors[$i] }};border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11pt;color:#1e3a5f">{{ $i+1 }}</div>
            <div style="font-size:8pt;color:rgba(255,255,255,.85);white-space:nowrap">{{ $s }}</div>
          </div>
          @if(!$loop->last)
          <div style="color:rgba(255,255,255,.4);font-size:14pt;padding:0 4px;margin-bottom:16px">→</div>
          @endif
          @endforeach
        </div>
      </div>

    </div>{{-- /doc-body --}}

    {{-- Pied de page --}}
    <div style="background:#F8FAFC;border-top:1.5px solid #E2E8F0;padding:14px 40px;display:flex;justify-content:space-between;align-items:center;font-size:8.5pt;color:#94A3B8">
      <div>
        <strong style="color:#1e3a5f">SB Pointage</strong> — Plateforme de gestion des présences &amp; paie
      </div>
      <div>Notice v1.0 · {{ now()->format('d/m/Y') }} · Réservé au super administrateur</div>
    </div>

  </div>{{-- /print-page --}}

  <div class="h-8"></div>
</div>
@endsection
