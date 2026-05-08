@extends('layouts.admin')
@section('title', 'Notices d\'utilisation — SB Pointage')
@section('page-title', 'Notices client')

@push('scripts')
<script>
function printNotice(id) {
    document.querySelectorAll('.notice-section').forEach(s => s.setAttribute('data-printing','0'));
    document.getElementById(id).setAttribute('data-printing','1');
    document.body.setAttribute('data-print-notice', id);
    window.print();
    setTimeout(() => { document.body.removeAttribute('data-print-notice'); }, 1000);
}
function shareWhatsapp(id) {
    const labels = {
        'notice-vitrine': 'Notice Vitrine & Inscription — SB Pointage',
        'notice-pointage': 'Notice SB Pointage Ouvriers',
        'notice-securite': 'Notice SB Sécurité'
    };
    const url = window.location.origin + '/admin/notice#' + id;
    const text = encodeURIComponent(labels[id] + '\n' + url);
    window.open('https://wa.me/?text=' + text, '_blank');
}
function copyLink(id) {
    const url = window.location.origin + '/admin/notice#' + id;
    navigator.clipboard.writeText(url).then(() => {
        const btn = document.getElementById('copy-btn-' + id);
        const orig = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copié !';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}
document.addEventListener('DOMContentLoaded', () => {
    const hash = window.location.hash.replace('#','');
    if (hash) {
        const tab = document.querySelector(`[data-tab="${hash}"]`);
        if (tab) tab.click();
    }
});
</script>
<style>
@media print {
    aside, header, .no-print, nav { display: none !important; }
    body { background: #fff !important; }
    .main-content { padding: 0 !important; }
    .print-page { box-shadow: none !important; border-radius: 0 !important; }
    @page { size: A4; margin: 15mm 12mm; }
    .step-card { break-inside: avoid; }
    .notice-section { display: none !important; }
    .notice-section[data-printing="1"] { display: block !important; }
}
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto py-6" x-data="{ tab: 'vitrine' }" x-init="
    const hash = window.location.hash.replace('#','');
    if (['notice-vitrine','notice-pointage','notice-securite'].includes(hash)) tab = hash.replace('notice-','');
">

  {{-- ONGLETS --}}
  <div class="no-print flex gap-2 mb-6 flex-wrap">
    <button @click="tab='vitrine'; history.pushState(null,null,'#notice-vitrine')" data-tab="notice-vitrine"
      :class="tab==='vitrine' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
      class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition">
      <span>🌐</span> Notice Vitrine
    </button>
    <button @click="tab='pointage'; history.pushState(null,null,'#notice-pointage')" data-tab="notice-pointage"
      :class="tab==='pointage' ? 'bg-orange-500 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
      class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition">
      <span>⏱</span> SB Pointage Ouvriers
    </button>
    <button @click="tab='securite'; history.pushState(null,null,'#notice-securite')" data-tab="notice-securite"
      :class="tab==='securite' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
      class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition">
      <span>🛡️</span> SB Sécurité
    </button>
  </div>

{{-- ═══════════════════════════════════════════════════════
     NOTICE 1 — VITRINE & INSCRIPTION
═══════════════════════════════════════════════════════ --}}
<div id="notice-vitrine" class="notice-section" x-show="tab==='vitrine'" data-printing="0">

  <div class="no-print flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">📋 Notice d'utilisation — Vitrine & Inscription</h2>
      <p class="text-sm text-slate-500 mt-1">De la visite du site jusqu'à la création du premier administrateur</p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <button onclick="printNotice('notice-vitrine')" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        PDF
      </button>
      <button onclick="shareWhatsapp('notice-vitrine')" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp
      </button>
      <button id="copy-btn-notice-vitrine" onclick="copyLink('notice-vitrine')" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-lg border text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        Copier le lien
      </button>
    </div>
  </div>

  <div class="print-page bg-white rounded-2xl shadow-lg overflow-hidden">
    <div style="background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 60%,#2563eb 100%);padding:36px 40px 28px">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
        <div style="width:48px;height:48px;background:rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:24px">🌐</div>
        <div>
          <div style="color:rgba(255,255,255,.7);font-size:10pt;letter-spacing:1px;text-transform:uppercase">Notice officielle · Vitrine & Inscription</div>
          <div style="color:#fff;font-size:16pt;font-weight:800">SB Pointage · Guide de démarrage</div>
        </div>
      </div>
      <div style="color:rgba(255,255,255,.85);font-size:11pt">De la visite du site web jusqu'à la création de votre premier administrateur</div>
      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📅 {{ now()->format('d/m/Y') }}</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">🔒 Super admin uniquement</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📖 9 étapes</div>
      </div>
    </div>

    <div style="padding:32px 40px 40px">
      <div style="background:#EFF6FF;border-left:4px solid #2563EB;border-radius:0 8px 8px 0;padding:14px 18px;margin-bottom:28px;font-size:10pt;color:#1e3a5f;line-height:1.6">
        <strong>À qui s'adresse ce document ?</strong><br>
        Ce guide est destiné aux <strong>responsables d'entreprise</strong> (gérants, DRH, chefs de chantier) qui souhaitent utiliser la plateforme SB Pointage pour gérer les présences de leurs ouvriers ou agents de sécurité.
      </div>

      @php
      $vitrine_steps = [
        ['num'=>1,'icon'=>'🌐','title'=>'Visiter le site web','color_bg'=>'linear-gradient(90deg,#EFF6FF,#F0F9FF)','color_border'=>'#BFDBFE','color_num'=>'#2563EB','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Ouvrez votre navigateur et saisissez l\'adresse du site SB Pointage. La page d\'accueil présente toutes les fonctionnalités de la plateforme.</p><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px"><div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px"><div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:6px">📱 Ce que vous verrez</div><ul style="font-size:9pt;color:#555;line-height:1.8;padding-left:14px"><li>Présentation de l\'application mobile</li><li>Les fonctionnalités principales</li><li>Les tarifs par activité et plan</li><li>Les téléchargements disponibles</li></ul></div><div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px"><div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:6px">🔑 Boutons d\'action</div><ul style="font-size:9pt;color:#555;line-height:1.8;padding-left:14px"><li><strong>Démarrer gratuitement</strong> → Inscription</li><li><strong>Se connecter</strong> → Espace client</li><li><strong>Voir les tarifs</strong> → Section prix</li><li><strong>Télécharger</strong> → Apps mobiles</li></ul></div></div><div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">💡 <strong>Conseil :</strong> Parcourez la section <em>Tarifs</em> avant de vous inscrire pour choisir le plan adapté.</div>'],
        ['num'=>2,'icon'=>'🏗️','title'=>'Choisir votre activité','color_bg'=>'linear-gradient(90deg,#F0FDF4,#ECFDF5)','color_border'=>'#86EFAC','color_num'=>'#16A34A','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Cliquez sur <strong>"Démarrer gratuitement"</strong>. Trois modules sont disponibles :</p><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px"><div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:10px;padding:14px;text-align:center"><div style="font-size:22px;margin-bottom:6px">🏗️</div><div style="font-weight:700;font-size:10pt;color:#9A3412">Pointage Bâtiment</div><div style="font-size:8.5pt;color:#78350F;margin-top:4px">Chantiers, ouvriers journaliers</div></div><div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:14px;text-align:center"><div style="font-size:22px;margin-bottom:6px">🌾</div><div style="font-weight:700;font-size:10pt;color:#14532D">Pointage Agriculture</div><div style="font-size:8.5pt;color:#166534;margin-top:4px">Exploitation agricole, saisonniers</div></div><div style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:10px;padding:14px;text-align:center"><div style="font-size:22px;margin-bottom:6px">🛡️</div><div style="font-weight:700;font-size:10pt;color:#1e3a5f">Sécurité Privée</div><div style="font-size:8.5pt;color:#1e40af;margin-top:4px">Agences de sécurité, agents</div></div></div>'],
        ['num'=>3,'icon'=>'💳','title'=>'Choisir un plan tarifaire','color_bg'=>'linear-gradient(90deg,#FDF4FF,#FAF5FF)','color_border'=>'#D8B4FE','color_num'=>'#9333EA','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Chaque module propose plusieurs plans selon le nombre d\'ouvriers à gérer. Cliquez sur <strong>"Choisir ce plan"</strong>.</p><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px"><div style="background:#F8FAFC;border:1px solid #CBD5E1;border-radius:8px;padding:12px;text-align:center"><div style="font-weight:700;font-size:10pt;color:#334155">Plan Starter</div><div style="font-size:8.5pt;color:#64748b;margin-top:4px">Petites équipes · Jusqu\'à 20</div></div><div style="background:#EDE9FE;border:2px solid #8B5CF6;border-radius:8px;padding:12px;text-align:center;position:relative"><div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#8B5CF6;color:#fff;font-size:8pt;font-weight:700;padding:2px 10px;border-radius:12px">Populaire</div><div style="font-weight:700;font-size:10pt;color:#4C1D95">Plan Standard</div><div style="font-size:8.5pt;color:#5B21B6;margin-top:4px">Équipes moyennes · Jusqu\'à 50</div></div><div style="background:#F8FAFC;border:1px solid #CBD5E1;border-radius:8px;padding:12px;text-align:center"><div style="font-weight:700;font-size:10pt;color:#334155">Plan Premium</div><div style="font-size:8.5pt;color:#64748b;margin-top:4px">Grandes entreprises · Illimité</div></div></div><div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#7F1D1D">⚠️ Vous pourrez upgrader votre plan à tout moment depuis l\'espace client.</div>'],
        ['num'=>4,'icon'=>'👤','title'=>'Informations personnelles','color_bg'=>'linear-gradient(90deg,#FFF7ED,#FFFBEB)','color_border'=>'#FED7AA','color_num'=>'#EA580C','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Remplissez le formulaire avec vos informations. Ces données créent votre <strong>compte client</strong> sur la plateforme.</p><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px"><div style="background:#FFFBF5;border:1px solid #FED7AA;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#9A3412">Prénom & Nom *</div><div style="font-size:8.5pt;color:#78350F">Votre identité complète</div></div><div style="background:#FFFBF5;border:1px solid #FED7AA;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#9A3412">Email *</div><div style="font-size:8.5pt;color:#78350F">Sert d\'identifiant de connexion</div></div><div style="background:#FFFBF5;border:1px solid #FED7AA;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#9A3412">Téléphone *</div><div style="font-size:8.5pt;color:#78350F">ex: +221 77 000 00 00</div></div><div style="background:#FFFBF5;border:1px solid #FED7AA;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#9A3412">Mot de passe *</div><div style="font-size:8.5pt;color:#78350F">Minimum 8 caractères</div></div></div><div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#14532D">✅ Utilisez une adresse email professionnelle active — c\'est votre identifiant de connexion.</div>'],
        ['num'=>5,'icon'=>'🏢','title'=>'Informations de votre entreprise','color_bg'=>'linear-gradient(90deg,#F0FDFA,#ECFEFF)','color_border'=>'#A7F3D0','color_num'=>'#0D9488','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Cette étape crée le profil de votre entreprise. Toutes vos activités seront rattachées à cette entreprise.</p><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px"><div style="background:#F0FDFA;border:1px solid #A7F3D0;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#0D9488">Nom de l\'entreprise *</div><div style="font-size:8.5pt;color:#134E4A">ex: BTP Diallo & Frères</div></div><div style="background:#F0FDFA;border:1px solid #A7F3D0;border-radius:6px;padding:8px 12px"><div style="font-weight:700;font-size:9.5pt;color:#0D9488">Adresse *</div><div style="font-size:8.5pt;color:#134E4A">ex: Avenue Bourguiba, Dakar</div></div></div>'],
        ['num'=>6,'icon'=>'💰','title'=>'Confirmer le paiement','color_bg'=>'linear-gradient(90deg,#FFF1F2,#FFF5F5)','color_border'=>'#FECDD3','color_num'=>'#DC2626','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Pour les plans payants, choisissez votre mode de paiement. Pour les essais gratuits, cette étape est automatique.</p><div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px"><div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:20px;padding:6px 14px;font-size:9.5pt;font-weight:600;color:#9F1239">📱 Orange Money</div><div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:20px;padding:6px 14px;font-size:9.5pt;font-weight:600;color:#9F1239">〰️ Wave</div><div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:20px;padding:6px 14px;font-size:9.5pt;font-weight:600;color:#9F1239">💳 Visa / MasterCard</div><div style="background:#FFF1F2;border:1px solid #FECDD3;border-radius:20px;padding:6px 14px;font-size:9.5pt;font-weight:600;color:#9F1239">🏦 Virement</div></div><div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">💡 Compte d\'essai : activé immédiatement. Compte payant : activé après confirmation du paiement.</div>'],
        ['num'=>7,'icon'=>'🔐','title'=>'Se connecter à l\'espace client','color_bg'=>'linear-gradient(90deg,#EFF6FF,#EEF2FF)','color_border'=>'#A5B4FC','color_num'=>'#4F46E5','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Une fois activé, cliquez sur <strong>"Se connecter"</strong> dans le menu. Saisissez votre email et mot de passe.</p><div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px"><div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;text-align:center"><div style="font-size:20px;margin-bottom:6px">📧</div><div style="font-size:9pt;font-weight:600;color:#334155">Email</div><div style="font-size:8.5pt;color:#64748b">Votre adresse email</div></div><div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;text-align:center"><div style="font-size:20px;margin-bottom:6px">🔑</div><div style="font-size:9pt;font-weight:600;color:#334155">Mot de passe</div><div style="font-size:8.5pt;color:#64748b">Défini à l\'inscription</div></div><div style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:8px;padding:12px;text-align:center"><div style="font-size:20px;margin-bottom:6px">🏠</div><div style="font-size:9pt;font-weight:600;color:#1e3a5f">Destination</div><div style="font-size:8.5pt;color:#1e40af">Tableau de bord</div></div></div>'],
        ['num'=>8,'icon'=>'⭐','title'=>'Créer le premier administrateur','color_bg'=>'linear-gradient(90deg,#1e3a5f,#1d4ed8)','color_border'=>'#1d4ed8','color_num'=>'#fff','content'=>'<p style="font-size:10pt;color:#374151;margin-bottom:14px">Dans votre espace client, cliquez sur <strong>"Admins"</strong> → <strong>"Ajouter un administrateur"</strong>.</p><div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:14px"><div><div style="font-size:9.5pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">Informations à renseigner :</div><div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px"><div style="width:6px;height:6px;background:#2563EB;border-radius:50%;margin-top:5px;flex-shrink:0"></div><div style="font-size:9.5pt;color:#374151"><strong>Prénom & Nom</strong> — Identité de l\'admin</div></div><div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px"><div style="width:6px;height:6px;background:#2563EB;border-radius:50%;margin-top:5px;flex-shrink:0"></div><div style="font-size:9.5pt;color:#374151"><strong>Téléphone</strong> — Connexion app mobile</div></div><div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px"><div style="width:6px;height:6px;background:#2563EB;border-radius:50%;margin-top:5px;flex-shrink:0"></div><div style="font-size:9.5pt;color:#374151"><strong>Code PIN</strong> — 4 à 6 chiffres</div></div><div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px"><div style="width:6px;height:6px;background:#2563EB;border-radius:50%;margin-top:5px;flex-shrink:0"></div><div style="font-size:9.5pt;color:#374151"><strong>Email & Mot de passe</strong> — Accès web</div></div></div><div style="background:#E0F2FE;border-radius:10px;padding:14px"><div style="font-size:9.5pt;font-weight:700;color:#0369A1;margin-bottom:8px">🔐 Droits de l\'administrateur</div><ul style="font-size:9pt;color:#0c4a6e;line-height:1.9;padding-left:14px"><li>Gérer les ouvriers / agents</li><li>Enregistrer les présences</li><li>Consulter les rapports</li><li>Valider absences & remplacements</li></ul></div></div><div style="background:#DBEAFE;border:1.5px solid #93C5FD;border-radius:8px;padding:12px 16px;font-size:9.5pt;color:#1e3a5f">📧 L\'administrateur reçoit automatiquement un email avec ses identifiants de connexion.</div>'],
        ['num'=>9,'icon'=>'📱','title'=>'Télécharger l\'application mobile','color_bg'=>'linear-gradient(90deg,#F0FDF4,#ECFDF5)','color_border'=>'#86EFAC','color_num'=>'#16A34A','content'=>'<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:14px"><div style="background:#F8FAFC;border:1.5px solid #CBD5E1;border-radius:10px;padding:14px"><div style="font-size:10pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">🏗️ SB Pointage <span style="font-size:8.5pt;font-weight:400;color:#64748b">Bâtiment / Agriculture</span></div><ul style="font-size:9pt;color:#374151;line-height:1.9;padding-left:14px"><li>Android (Play Store ou APK direct)</li><li>L\'admin pointe les ouvriers</li><li>Mode hors-ligne disponible</li><li>Calcul automatique des salaires</li></ul></div><div style="background:#F8FAFC;border:1.5px solid #CBD5E1;border-radius:10px;padding:14px"><div style="font-size:10pt;font-weight:700;color:#1e3a5f;margin-bottom:8px">🛡️ SB Sécurité <span style="font-size:8.5pt;font-weight:400;color:#64748b">Agences de sécurité</span></div><ul style="font-size:9pt;color:#374151;line-height:1.9;padding-left:14px"><li>App dédiée agents & gérants</li><li>Pointage GPS en temps réel</li><li>Sessions de présence</li><li>Gestion des tours & plannings</li></ul></div></div><div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#14532D">🔑 Connexion mobile : <strong>numéro de téléphone</strong> + <strong>code PIN</strong> (défini à la création du compte).</div>'],
      ];
      @endphp

      @foreach($vitrine_steps as $step)
      <div class="step-card" style="margin-bottom:24px;border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden{{ $step['num']==8 ? ';border-color:#2563EB' : '' }}">
        <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;background:{{ $step['color_bg'] }};border-bottom:2px solid {{ $step['color_border'] }}">
          <div style="width:36px;height:36px;background:{{ $step['color_num']=='#fff'?'rgba(255,255,255,.2)':$step['color_num'] }};border-radius:50%;display:flex;align-items:center;justify-content:center;{{ $step['color_num']=='#fff'?'color:#fff':'color:#fff' }};font-weight:900;font-size:16pt;flex-shrink:0">{{ $step['num'] }}</div>
          <div>
            <div style="font-size:13pt;font-weight:800;color:{{ $step['num']==8?'#fff':'#1e3a5f' }}">{{ $step['icon'] }} {{ $step['title'] }}</div>
          </div>
        </div>
        <div style="padding:18px 20px{{ $step['num']==8?';background:#F8FBFF':'' }}">{!! $step['content'] !!}</div>
      </div>
      @endforeach

      {{-- Récapitulatif --}}
      <div style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);border-radius:12px;padding:24px 28px">
        <div style="font-size:12pt;font-weight:800;color:#fff;margin-bottom:16px">🗺️ Récapitulatif du parcours</div>
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:0">
          @php $steps=['Vitrine','Module','Plan','Proprio','Entreprise','Paiement','Connexion','1er Admin','App mobile'];$colors=['#60A5FA','#34D399','#C084FC','#FB923C','#2DD4BF','#F87171','#818CF8','#FDE68A','#6EE7B7']; @endphp
          @foreach($steps as $i=>$s)
          <div style="text-align:center;padding:6px 0">
            <div style="width:32px;height:32px;background:{{ $colors[$i] }};border-radius:50%;margin:0 auto 4px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11pt;color:#1e3a5f">{{ $i+1 }}</div>
            <div style="font-size:8pt;color:rgba(255,255,255,.85);white-space:nowrap">{{ $s }}</div>
          </div>
          @if(!$loop->last)<div style="color:rgba(255,255,255,.4);font-size:14pt;padding:0 4px;margin-bottom:16px">→</div>@endif
          @endforeach
        </div>
      </div>
    </div>

    <div style="background:#F8FAFC;border-top:1.5px solid #E2E8F0;padding:14px 40px;display:flex;justify-content:space-between;align-items:center;font-size:8.5pt;color:#94A3B8">
      <div><strong style="color:#1e3a5f">SB Pointage</strong> — Plateforme de gestion des présences</div>
      <div>Notice Vitrine v2.0 · {{ now()->format('d/m/Y') }}</div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     NOTICE 2 — SB POINTAGE OUVRIERS
═══════════════════════════════════════════════════════ --}}
<div id="notice-pointage" class="notice-section" x-show="tab==='pointage'" data-printing="0">

  <div class="no-print flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">⏱ Notice SB Pointage Ouvriers</h2>
      <p class="text-sm text-slate-500 mt-1">Guide complet pour les comptes Ouvrier, Gérant et Administrateur</p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <button onclick="printNotice('notice-pointage')" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        PDF
      </button>
      <button onclick="shareWhatsapp('notice-pointage')" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp
      </button>
      <button id="copy-btn-notice-pointage" onclick="copyLink('notice-pointage')" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-lg border text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        Copier le lien
      </button>
    </div>
  </div>

  <div class="print-page bg-white rounded-2xl shadow-lg overflow-hidden">

    {{-- En-tête --}}
    <div style="background:linear-gradient(135deg,#7c2d12 0%,#c2410c 50%,#ea580c 100%);padding:36px 40px 28px">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
        <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px">⏱</div>
        <div>
          <div style="color:rgba(255,255,255,.7);font-size:10pt;letter-spacing:1px;text-transform:uppercase">Notice officielle · Application Mobile</div>
          <div style="color:#fff;font-size:16pt;font-weight:800">SB Pointage — Guide Utilisateur</div>
        </div>
      </div>
      <div style="color:rgba(255,255,255,.85);font-size:11pt">Gestion des présences, paiements et transferts — Ouvrier · Gérant · Admin</div>
      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📅 {{ now()->format('d/m/Y') }}</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📱 Android</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">🔌 Mode hors-ligne disponible</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">3 types de comptes</div>
      </div>
    </div>

    <div style="padding:32px 40px 40px">

      {{-- Intro --}}
      <div style="background:#FFF7ED;border-left:4px solid #EA580C;border-radius:0 8px 8px 0;padding:14px 18px;margin-bottom:32px;font-size:10pt;color:#7c2d12;line-height:1.6">
        <strong>À qui s'adresse ce document ?</strong><br>
        Ce guide est destiné aux trois types d'utilisateurs de l'application mobile <strong>SB Pointage</strong> : les <strong>Ouvriers</strong> (salariés journaliers), les <strong>Gérants</strong> (superviseurs de terrain) et les <strong>Administrateurs</strong> (responsables d'entreprise). Chaque rôle dispose de fonctionnalités spécifiques décrites ci-dessous.
      </div>

      {{-- Connexion commune --}}
      <div class="step-card" style="margin-bottom:28px;border:1.5px solid #FED7AA;border-radius:12px;overflow:hidden">
        <div style="padding:14px 20px;background:linear-gradient(90deg,#FFF7ED,#FFEDD5);border-bottom:2px solid #FED7AA">
          <div style="font-size:13pt;font-weight:800;color:#7c2d12">🔐 Connexion à l'application (tous les comptes)</div>
        </div>
        <div style="padding:18px 20px">
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">1️⃣</div>
              <div style="font-size:9pt;font-weight:700;color:#9A3412">Numéro de tél.</div>
              <div style="font-size:8pt;color:#78350F">Saisissez votre numéro</div>
            </div>
            <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">2️⃣</div>
              <div style="font-size:9pt;font-weight:700;color:#9A3412">Entreprise</div>
              <div style="font-size:8pt;color:#78350F">Sélectionnez la vôtre</div>
            </div>
            <div style="background:#FFF7ED;border:1px solid #FED7AA;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">3️⃣</div>
              <div style="font-size:9pt;font-weight:700;color:#9A3412">Code PIN</div>
              <div style="font-size:8pt;color:#78350F">4 à 6 chiffres</div>
            </div>
            <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">✅</div>
              <div style="font-size:9pt;font-weight:700;color:#14532D">Accès</div>
              <div style="font-size:8pt;color:#166534">Tableau de bord</div>
            </div>
          </div>
          <div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">
            💡 En cas d'oubli du PIN ou de 5 tentatives échouées : contactez votre administrateur pour le réinitialiser. L'application fonctionne également en mode hors-ligne grâce au PIN enregistré localement.
          </div>
        </div>
      </div>

      {{-- ══ COMPTE OUVRIER ══ --}}
      <div style="background:linear-gradient(90deg,#1e3a5f,#0f2d4a);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">👷</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Ouvrier</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Salarié journalier · Suivi de solde, présences et transferts</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:28px">
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">🏠 Tableau de bord (Accueil)</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• <strong>Solde actuel</strong> en FCFA affiché en haut</div>
            <div style="margin-bottom:6px">• <strong>Badge QR</strong> personnel (accès rapide)</div>
            <div style="margin-bottom:6px">• Boutons : <em>Envoyer</em> / <em>Recevoir</em></div>
            <div style="margin-bottom:6px">• <strong>Activités récentes</strong> : présences, paiements et transferts mélangés</div>
            <div style="margin-bottom:6px">• Compteur de jours travaillés ce mois</div>
            <div>• Indicateur de connexion (en ligne / hors-ligne)</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">📛 Mon Badge QR</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Code QR unique et personnel</div>
            <div style="margin-bottom:6px">• Valide <strong>24 heures</strong> après génération</div>
            <div style="margin-bottom:6px">• À présenter au gérant pour le pointage</div>
            <div style="margin-bottom:6px">• Affiche votre nom et statut de validité</div>
            <div>• Se renouvelle automatiquement chaque jour</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">💸 Envoyer de l'argent</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Rechercher le destinataire par <strong>numéro de téléphone</strong> ou <strong>scan QR</strong></div>
            <div style="margin-bottom:6px">• Saisir le montant à envoyer</div>
            <div style="margin-bottom:6px">• Vérification automatique du solde disponible</div>
            <div style="margin-bottom:6px">• Boîte de confirmation avant envoi</div>
            <div>• ⚠️ Nécessite une connexion internet</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">📥 Recevoir de l'argent</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Saisir d'abord le montant attendu</div>
            <div style="margin-bottom:6px">• Scanner le QR du payeur avec la caméra</div>
            <div style="margin-bottom:6px">• Vérification de l'identité du payeur</div>
            <div style="margin-bottom:6px">• Confirmation et crédit automatique</div>
            <div>• Option pour scanner un autre payeur</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">📋 Historique</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Toutes vos <strong>présences</strong> avec heures d'entrée/sortie</div>
            <div style="margin-bottom:6px">• Montant gagné par journée</div>
            <div style="margin-bottom:6px">• Paiements reçus avec date et source</div>
            <div>• Transferts envoyés et reçus</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#F8FAFC;padding:12px 16px;border-bottom:1px solid #E2E8F0">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">👤 Profil</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Vos informations : nom, téléphone, entreprise</div>
            <div style="margin-bottom:6px">• Changer votre <strong>code PIN</strong></div>
            <div style="margin-bottom:6px">• Statut du mode hors-ligne</div>
            <div>• Bouton de déconnexion</div>
          </div>
        </div>
      </div>

      {{-- ══ COMPTE GÉRANT ══ --}}
      <div style="background:linear-gradient(90deg,#1e3a5f,#0f2d4a);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">👨‍💼</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Gérant</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Superviseur de terrain · Pointage, paiements et gestion d'équipe</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:28px">
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">🏠 Tableau de bord</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Votre solde personnel + badge QR</div>
            <div style="margin-bottom:6px">• Votre taux journalier</div>
            <div style="margin-bottom:6px">• <strong>Présents aujourd'hui</strong> / total ouvriers</div>
            <div style="margin-bottom:6px">• Boutons : <em>Pointer maintenant</em> / <em>Paiement</em></div>
            <div style="margin-bottom:6px">• Vos présences personnelles</div>
            <div>• Fil d'activité de toute l'équipe</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">📷 Scanner QR — Pointer un ouvrier</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Onglet <em>Scanner</em> dans la navigation</div>
            <div style="margin-bottom:6px">• Pointer la caméra sur le badge QR de l'ouvrier</div>
            <div style="margin-bottom:6px">• La fiche de l'ouvrier s'affiche</div>
            <div style="margin-bottom:6px">• Bouton <strong>Pointer Entrée</strong> ou <strong>Pointer Sortie</strong></div>
            <div>• Actions rapides : Payer · Transférer</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">👷 Gestion des ouvriers</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Liste de tous vos ouvriers</div>
            <div style="margin-bottom:6px">• Recherche par nom ou téléphone</div>
            <div style="margin-bottom:6px">• Ajouter un nouvel ouvrier (bouton +)</div>
            <div style="margin-bottom:6px">• Voir solde, statut et taux journalier</div>
            <div>• Activer / désactiver un ouvrier</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">💳 Paiement individuel</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Sélectionner un ouvrier</div>
            <div style="margin-bottom:6px">• Saisir le montant ou activer <strong>Tout payer</strong></div>
            <div style="margin-bottom:6px">• Vérification contre le solde dû</div>
            <div>• Confirmation avec détails avant envoi</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">💰 Paiement en lot</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Sélectionner plusieurs ouvriers à la fois</div>
            <div style="margin-bottom:6px">• Saisir un montant individuel par ouvrier</div>
            <div style="margin-bottom:6px">• Option <em>Sélectionner tout</em></div>
            <div style="margin-bottom:6px">• Total calculé automatiquement</div>
            <div>• Confirmation groupée et résultats détaillés</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF7ED;padding:12px 16px;border-bottom:1px solid #FED7AA">
            <div style="font-size:10.5pt;font-weight:700;color:#9A3412">🔄 Transfert d'argent</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Envoyer de l'argent à un ouvrier</div>
            <div style="margin-bottom:6px">• Recherche par téléphone ou scan QR</div>
            <div style="margin-bottom:6px">• Saisie du montant + confirmation</div>
            <div>• ⚠️ Nécessite une connexion internet</div>
          </div>
        </div>
      </div>

      {{-- ══ COMPTE ADMIN ══ --}}
      <div style="background:linear-gradient(90deg,#1e3a5f,#0f2d4a);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">🏛️</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Administrateur</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Responsable d'entreprise · Contrôle total de la plateforme mobile</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:28px">
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#EFF6FF;padding:12px 16px;border-bottom:1px solid #BFDBFE">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">📊 Tableau de bord</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Statistiques : Gérants · Ouvriers · Présents</div>
            <div style="margin-bottom:6px">• <strong>Total à payer</strong> (toute l'entreprise)</div>
            <div style="margin-bottom:6px">• Boutons : <em>Scanner un badge</em> / <em>Paiement</em></div>
            <div>• Activités récentes de l'entreprise</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#EFF6FF;padding:12px 16px;border-bottom:1px solid #BFDBFE">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">👥 Gestion des utilisateurs</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• <strong>Onglet Ouvriers</strong> : liste, recherche, ajout</div>
            <div style="margin-bottom:6px">• <strong>Onglet Gérants</strong> : même fonctionnalités</div>
            <div style="margin-bottom:6px">• Modifier taux journalier par dialogue</div>
            <div style="margin-bottom:6px">• Pointer manuellement (entrée/sortie)</div>
            <div>• Activer / désactiver / supprimer</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#EFF6FF;padding:12px 16px;border-bottom:1px solid #BFDBFE">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">📷 Scanner QR (étendu)</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Scanner <strong>ouvriers ET gérants</strong></div>
            <div style="margin-bottom:6px">• Fiche détaillée de l'utilisateur scanné</div>
            <div style="margin-bottom:6px">• Pointer entrée / sortie</div>
            <div>• Payer · Transférer depuis la fiche</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#EFF6FF;padding:12px 16px;border-bottom:1px solid #BFDBFE">
            <div style="font-size:10.5pt;font-weight:700;color:#1e3a5f">🛠️ Professions & Métiers</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Créer des catégories de métiers</div>
            <div style="margin-bottom:6px">• Définir le <strong>taux journalier</strong> par catégorie</div>
            <div style="margin-bottom:6px">• Ajouter, modifier, supprimer catégories</div>
            <div>• Affecter un ouvrier à une catégorie</div>
          </div>
        </div>
      </div>

      {{-- Tableau récapitulatif --}}
      <div style="border:1.5px solid #E2E8F0;border-radius:12px;overflow:hidden;margin-bottom:8px">
        <div style="background:#1e3a5f;padding:12px 20px">
          <div style="color:#fff;font-size:11pt;font-weight:700">📊 Tableau récapitulatif des fonctionnalités</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:9pt">
          <thead>
            <tr style="background:#F1F5F9">
              <th style="padding:10px 16px;text-align:left;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0">Fonctionnalité</th>
              <th style="padding:10px 12px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">👷 Ouvrier</th>
              <th style="padding:10px 12px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">👨‍💼 Gérant</th>
              <th style="padding:10px 12px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">🏛️ Admin</th>
            </tr>
          </thead>
          <tbody>
            @php
            $ptRows = [
              ['Voir son solde & badge QR','✅','✅','—'],
              ['Pointer entrée / sortie','—','✅','✅'],
              ['Scanner badge QR','—','✅','✅'],
              ['Payer des ouvriers','—','✅','✅'],
              ['Paiement en lot','—','✅','✅'],
              ['Transférer de l\'argent','✅','✅','—'],
              ['Gérer les ouvriers','—','✅','✅'],
              ['Gérer les gérants','—','—','✅'],
              ['Gérer les professions / taux','—','—','✅'],
              ['Voir statistiques entreprise','—','✅ (partiel)','✅'],
            ];
            @endphp
            @foreach($ptRows as $i=>$row)
            <tr style="background:{{ $i%2==0?'#fff':'#F8FAFC' }}">
              <td style="padding:9px 16px;color:#374151;border-bottom:1px solid #F1F5F9">{{ $row[0] }}</td>
              <td style="padding:9px 12px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ $row[1]=='✅'?'#16A34A':'#94A3B8' }};font-weight:{{ $row[1]=='✅'?'700':'400' }}">{{ $row[1] }}</td>
              <td style="padding:9px 12px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ str_contains($row[2],'✅')?'#16A34A':'#94A3B8' }};font-weight:{{ str_contains($row[2],'✅')?'700':'400' }}">{{ $row[2] }}</td>
              <td style="padding:9px 12px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ $row[3]=='✅'?'#16A34A':'#94A3B8' }};font-weight:{{ $row[3]=='✅'?'700':'400' }}">{{ $row[3] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>

    <div style="background:#F8FAFC;border-top:1.5px solid #E2E8F0;padding:14px 40px;display:flex;justify-content:space-between;align-items:center;font-size:8.5pt;color:#94A3B8">
      <div><strong style="color:#7c2d12">SB Pointage</strong> — Application mobile de gestion des présences & paie</div>
      <div>Notice Pointage v1.0 · {{ now()->format('d/m/Y') }}</div>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     NOTICE 3 — SB SÉCURITÉ
═══════════════════════════════════════════════════════ --}}
<div id="notice-securite" class="notice-section" x-show="tab==='securite'" data-printing="0">

  <div class="no-print flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="text-xl font-bold text-slate-800">🛡️ Notice SB Sécurité</h2>
      <p class="text-sm text-slate-500 mt-1">Guide complet pour les comptes Agent, Gérant et Administrateur</p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <button onclick="printNotice('notice-securite')" class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        PDF
      </button>
      <button onclick="shareWhatsapp('notice-securite')" class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow text-sm transition">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp
      </button>
      <button id="copy-btn-notice-securite" onclick="copyLink('notice-securite')" class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-lg border text-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        Copier le lien
      </button>
    </div>
  </div>

  <div class="print-page bg-white rounded-2xl shadow-lg overflow-hidden">

    {{-- En-tête --}}
    <div style="background:linear-gradient(135deg,#450a0a 0%,#991b1b 50%,#dc2626 100%);padding:36px 40px 28px">
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
        <div style="width:52px;height:52px;background:rgba(255,255,255,.2);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px">🛡️</div>
        <div>
          <div style="color:rgba(255,255,255,.7);font-size:10pt;letter-spacing:1px;text-transform:uppercase">Notice officielle · Application Mobile</div>
          <div style="color:#fff;font-size:16pt;font-weight:800">SB Sécurité — Guide Utilisateur</div>
        </div>
      </div>
      <div style="color:rgba(255,255,255,.85);font-size:11pt">Gestion des agents, pointage GPS et rapports — Agent · Gérant · Administrateur</div>
      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📅 {{ now()->format('d/m/Y') }}</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">📍 Pointage GPS</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">🌙 Tours Matin / Soir / Nuit</div>
        <div style="background:rgba(255,255,255,.15);border-radius:6px;padding:4px 12px;color:#fff;font-size:9pt">3 types de comptes</div>
      </div>
    </div>

    <div style="padding:32px 40px 40px">

      {{-- Intro --}}
      <div style="background:#FEF2F2;border-left:4px solid #DC2626;border-radius:0 8px 8px 0;padding:14px 18px;margin-bottom:32px;font-size:10pt;color:#7F1D1D;line-height:1.6">
        <strong>À qui s'adresse ce document ?</strong><br>
        Ce guide est destiné aux trois profils de l'application mobile <strong>SB Sécurité</strong> : les <strong>Agents de sécurité</strong> (personnel de terrain), les <strong>Gérants de zone</strong> (superviseurs) et les <strong>Administrateurs</strong> (direction). Chaque rôle dispose d'un tableau de bord et de fonctionnalités adaptés.
      </div>

      {{-- Connexion --}}
      <div class="step-card" style="margin-bottom:28px;border:1.5px solid #FECACA;border-radius:12px;overflow:hidden">
        <div style="padding:14px 20px;background:linear-gradient(90deg,#FEF2F2,#FFF5F5);border-bottom:2px solid #FECACA">
          <div style="font-size:13pt;font-weight:800;color:#7F1D1D">🔐 Connexion à l'application (tous les comptes)</div>
        </div>
        <div style="padding:18px 20px">
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px">
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">📱</div>
              <div style="font-size:9pt;font-weight:700;color:#991B1B">Numéro de tél.</div>
              <div style="font-size:8pt;color:#7F1D1D">Saisissez votre numéro</div>
            </div>
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">🔢</div>
              <div style="font-size:9pt;font-weight:700;color:#991B1B">Code PIN</div>
              <div style="font-size:8pt;color:#7F1D1D">Fourni par l'administrateur</div>
            </div>
            <div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:12px;text-align:center">
              <div style="font-size:20px;margin-bottom:6px">✅</div>
              <div style="font-size:9pt;font-weight:700;color:#14532D">Accès direct</div>
              <div style="font-size:8pt;color:#166534">Tableau de bord</div>
            </div>
          </div>
          <div style="background:#FEF9C3;border:1px solid #FDE047;border-radius:8px;padding:10px 14px;font-size:9.5pt;color:#713F12">
            💡 Première connexion : l'application propose de configurer votre PIN. En cas de blocage (5 tentatives échouées), contactez votre administrateur.
          </div>
        </div>
      </div>

      {{-- ══ AGENT DE SÉCURITÉ ══ --}}
      <div style="background:linear-gradient(90deg,#450a0a,#7f1d1d);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">👮</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Agent de Sécurité</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Personnel de terrain · Présence GPS, planning et justifications</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:28px">
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">🏠 Tableau de bord</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Carte : nom, zone, poste assigné</div>
            <div style="margin-bottom:6px">• Compteur de présences du mois en cours</div>
            <div style="margin-bottom:6px">• <strong>Tours assignés</strong> (Matin / Soir / Nuit) avec horaires</div>
            <div style="margin-bottom:6px">• Bouton <em>Confirmer ma présence</em> (GPS)</div>
            <div style="margin-bottom:6px">• Badge QR personnel pour scan local</div>
            <div>• Fil de communications de la direction</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📍 Confirmer sa présence (GPS)</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Le gérant lance une <strong>session de présence</strong></div>
            <div style="margin-bottom:6px">• Vous recevez une notification sur l'appli</div>
            <div style="margin-bottom:6px">• Appuyez sur <strong>Confirmer</strong> depuis votre poste</div>
            <div style="margin-bottom:6px">• Le système valide votre position GPS</div>
            <div>• ⏱ Répondre dans le délai imparti de la session</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📅 Mon Planning</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Planning mensuel complet</div>
            <div style="margin-bottom:6px">• Jours de repos et jours off marqués</div>
            <div style="margin-bottom:6px">• Tours assignés par jour</div>
            <div style="margin-bottom:6px">• Type de contrat (CDI / CDD / Prestataire)</div>
            <div>• Salaire et informations financières</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📝 Justifications d'absence</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• <strong>Motifs</strong> : Maladie · Voyage · Mariage · Baptême · Décès · Visite</div>
            <div style="margin-bottom:6px">• Joindre un document (photo / PDF)</div>
            <div style="margin-bottom:6px">• Suivi du statut : <em>En attente</em> / <em>Validé</em> / <em>Rejeté</em></div>
            <div>• Commentaire du gérant visible</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">🔁 Remplacements de tour</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• <strong>Agent sortant</strong> : initie le remplacement via QR</div>
            <div style="margin-bottom:6px">• <strong>Agent entrant</strong> : scanne le badge de l'agent sortant</div>
            <div style="margin-bottom:6px">• Validation automatique du remplacement</div>
            <div>• Historique complet des remplacements effectués</div>
          </div>
        </div>
        <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
          <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
            <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📊 Statistiques & Activités</div>
          </div>
          <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
            <div style="margin-bottom:6px">• Présences du mois en cours (jours comptés)</div>
            <div style="margin-bottom:6px">• Historique mensuel annuel</div>
            <div style="margin-bottom:6px">• Journal complet des activités</div>
            <div>• Toutes les notifications reçues</div>
          </div>
        </div>
      </div>

      {{-- ══ GÉRANT SÉCURITÉ ══ --}}
      <div style="background:linear-gradient(90deg,#450a0a,#7f1d1d);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">👨‍💼</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Gérant de Zone</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Superviseur · Sessions de présence, rapports et justifications</div>
        </div>
      </div>

      <div style="margin-bottom:28px">
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:14px 18px;margin-bottom:14px;font-size:9.5pt;color:#7F1D1D">
          📌 Le gérant dispose d'un tableau de bord à <strong>5 onglets</strong> : Agents · Sessions · Rapports · Activités · Justifications
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">👮 Onglet Agents</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Liste de tous les agents de la zone</div>
              <div style="margin-bottom:6px">• Statut actif / inactif de chaque agent</div>
              <div>• Poste d'affectation affiché</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📡 Onglet Sessions de présence</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• <strong>Lancer une session</strong> : choisir le tour (Matin/Soir/Nuit)</div>
              <div style="margin-bottom:6px">• Sélectionner les agents à convoquer</div>
              <div style="margin-bottom:6px">• Suivi en temps réel : Confirmé / Absent / En attente</div>
              <div style="margin-bottom:6px">• <strong>Pointage local</strong> : scanner les badges QR sur place</div>
              <div>• Historique des sessions passées</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📄 Onglet Rapports</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• <strong>Générer un rapport journalier</strong></div>
              <div style="margin-bottom:6px">• Nombre d'agents présents / absents par zone</div>
              <div style="margin-bottom:6px">• Statut de validation du rapport</div>
              <div>• Date, zone, total agents</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FFF5F5;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">✅ Onglet Justifications</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Voir toutes les justifications des agents</div>
              <div style="margin-bottom:6px">• Filtrer par statut : En attente / Validé / Rejeté</div>
              <div style="margin-bottom:6px">• Voir le motif et le document joint</div>
              <div>• <strong>Valider ou rejeter</strong> avec commentaire</div>
            </div>
          </div>
        </div>
      </div>

      {{-- ══ ADMIN SÉCURITÉ ══ --}}
      <div style="background:linear-gradient(90deg,#450a0a,#7f1d1d);border-radius:10px;padding:12px 20px;margin-bottom:16px;display:flex;align-items:center;gap:12px">
        <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">🏛️</div>
        <div>
          <div style="color:#fff;font-size:13pt;font-weight:800">Compte Administrateur</div>
          <div style="color:rgba(255,255,255,.7);font-size:9.5pt">Direction · Contrôle total de l'agence de sécurité</div>
        </div>
      </div>

      <div style="margin-bottom:28px">
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:14px 18px;margin-bottom:14px;font-size:9.5pt;color:#7F1D1D">
          📌 L'administrateur gère l'ensemble de l'agence : zones, postes, agents, sessions, rapports avancés et communications.
        </div>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:14px">
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">🗺️ Gestion des Zones</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Créer, visualiser et supprimer des zones</div>
              <div style="margin-bottom:6px">• Nom, description, nombre d'agents et de postes</div>
              <div>• Affecter un gérant responsable à chaque zone</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📍 Gestion des Postes</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Créer des postes avec <strong>position GPS</strong></div>
              <div style="margin-bottom:6px">• Définir le rayon de validation (en mètres)</div>
              <div style="margin-bottom:6px">• Affecter un agent à chaque poste</div>
              <div>• Voir l'agent actuellement affecté</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">👮 Gestion des Agents</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Ajouter un agent avec <strong>profil complet</strong></div>
              <div style="margin-bottom:6px">• Photos : profil, recto & verso pièce d'identité</div>
              <div style="margin-bottom:6px">• Contrat : CDI / CDD / Prestataire, salaire, dates</div>
              <div style="margin-bottom:6px">• Planning : jours de repos, jours off, tours assignés</div>
              <div style="margin-bottom:6px">• Affectation à une zone et un poste</div>
              <div>• Activer / désactiver un agent</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📡 Sessions de Pointage</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Lancer des sessions <strong>à distance</strong> (GPS)</div>
              <div style="margin-bottom:6px">• Pointage <strong>local</strong> par scan de badge QR</div>
              <div style="margin-bottom:6px">• Vue de toutes les sessions du jour</div>
              <div>• Suivi en temps réel : Présent / Absent / En attente</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📊 Rapports Avancés</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• <strong>Rapport de Pointage</strong> : présences détaillées par zone</div>
              <div style="margin-bottom:6px">• <strong>Rapport de Remplacements</strong> : analytique des échanges</div>
              <div style="margin-bottom:6px">• Statistiques mensuelles et annuelles</div>
              <div>• Validation et archivage des rapports journaliers</div>
            </div>
          </div>
          <div class="step-card" style="border:1px solid #E2E8F0;border-radius:10px;overflow:hidden">
            <div style="background:#FEF2F2;padding:12px 16px;border-bottom:1px solid #FECACA">
              <div style="font-size:10.5pt;font-weight:700;color:#991B1B">📢 Communications</div>
            </div>
            <div style="padding:14px 16px;font-size:9pt;color:#374151;line-height:1.9">
              <div style="margin-bottom:6px">• Diffuser un <strong>message texte</strong> à tous les agents</div>
              <div style="margin-bottom:6px">• Envoyer un <strong>message audio</strong> (vocal)</div>
              <div style="margin-bottom:6px">• Cibler par poste, zone ou tour</div>
              <div>• Messages avec date d'expiration automatique</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Tableau récapitulatif --}}
      <div style="border:1.5px solid #E2E8F0;border-radius:12px;overflow:hidden;margin-bottom:8px">
        <div style="background:#7F1D1D;padding:12px 20px">
          <div style="color:#fff;font-size:11pt;font-weight:700">📊 Tableau récapitulatif des fonctionnalités</div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:9pt">
          <thead>
            <tr style="background:#F1F5F9">
              <th style="padding:10px 16px;text-align:left;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0">Fonctionnalité</th>
              <th style="padding:10px 10px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">👮 Agent</th>
              <th style="padding:10px 10px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">👨‍💼 Gérant</th>
              <th style="padding:10px 10px;text-align:center;color:#374151;font-weight:700;border-bottom:1px solid #E2E8F0;white-space:nowrap">🏛️ Admin</th>
            </tr>
          </thead>
          <tbody>
            @php
            $secRows = [
              ['Confirmer présence (GPS)','✅','—','—'],
              ['Afficher son badge QR','✅','✅','—'],
              ['Consulter son planning','✅','—','—'],
              ['Soumettre une justification','✅','✅','—'],
              ['Participer à un remplacement','✅','✅','—'],
              ['Lancer une session de présence','—','✅','✅'],
              ['Scanner les badges QR (local)','—','✅','✅'],
              ['Générer un rapport journalier','—','✅','✅'],
              ['Valider les justifications','—','✅','—'],
              ['Gérer zones & postes','—','—','✅'],
              ['Créer / modifier des agents','—','—','✅'],
              ['Rapports avancés & analytics','—','—','✅'],
              ['Diffuser communications','—','—','✅'],
            ];
            @endphp
            @foreach($secRows as $i=>$row)
            <tr style="background:{{ $i%2==0?'#fff':'#F8FAFC' }}">
              <td style="padding:9px 16px;color:#374151;border-bottom:1px solid #F1F5F9">{{ $row[0] }}</td>
              <td style="padding:9px 10px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ $row[1]=='✅'?'#16A34A':'#94A3B8' }};font-weight:{{ $row[1]=='✅'?'700':'400' }}">{{ $row[1] }}</td>
              <td style="padding:9px 10px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ $row[2]=='✅'?'#16A34A':'#94A3B8' }};font-weight:{{ $row[2]=='✅'?'700':'400' }}">{{ $row[2] }}</td>
              <td style="padding:9px 10px;text-align:center;border-bottom:1px solid #F1F5F9;color:{{ $row[3]=='✅'?'#16A34A':'#94A3B8' }};font-weight:{{ $row[3]=='✅'?'700':'400' }}">{{ $row[3] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>

    <div style="background:#F8FAFC;border-top:1.5px solid #E2E8F0;padding:14px 40px;display:flex;justify-content:space-between;align-items:center;font-size:8.5pt;color:#94A3B8">
      <div><strong style="color:#7F1D1D">SB Sécurité</strong> — Application mobile de gestion des agents de sécurité</div>
      <div>Notice Sécurité v1.0 · {{ now()->format('d/m/Y') }}</div>
    </div>
  </div>
</div>

<div class="h-8"></div>
</div>
@endsection
