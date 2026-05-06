<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasActiveCompany;
use App\Models\OuvrierPointage;
use App\Models\OuvrierPaiement;
use App\Models\SecNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OuvrierController extends Controller
{
    use HasActiveCompany;

    private function company()
    {
        $company = $this->activeCompany();

        if ($company->module?->slug !== 'pointage-ouvriers') {
            $message = $company->subscriptions()
                ->whereHas('plan.module', fn($m) => $m->where('slug', 'pointage-ouvriers'))
                ->exists()
                ? 'Votre abonnement Pointage Ouvriers est expiré. Veuillez renouveler votre abonnement pour accéder à ce module.'
                : 'Cette entreprise ne possède pas le module Pointage Ouvriers.';

            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('client.subscription')->with('error', $message)
            );
        }

        return $company;
    }

    /** Jours travaillés d'un user sur une période — source: attendances (mobile) */
    private function joursTravailles(int $userId, ?Carbon $debut = null, ?Carbon $fin = null): float
    {
        $q = \App\Models\Attendance::where('worker_id', $userId)->whereNotNull('exit_time');
        if ($debut) $q->where('date', '>=', $debut->toDateString());
        if ($fin)   $q->where('date', '<=', $fin->toDateString());
        return (float) $q->count();
    }

    private function montantGagne(User $user, ?Carbon $debut = null, ?Carbon $fin = null): float
    {
        $q = \App\Models\Attendance::where('worker_id', $user->id)->whereNotNull('amount_earned');
        if ($debut) $q->where('date', '>=', $debut->toDateString());
        if ($fin)   $q->where('date', '<=', $fin->toDateString());
        return (float) $q->sum('amount_earned');
    }

    private function totalPaye(int $userId, ?Carbon $debut = null, ?Carbon $fin = null): float
    {
        $q = OuvrierPaiement::where('user_id', $userId);
        if ($debut) $q->where('date', '>=', $debut->toDateString());
        if ($fin)   $q->where('date', '<=', $fin->toDateString());
        return (float) $q->sum('montant');
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function index()
    {
        $company = $this->company();

        $ouvriers = User::where('company_id', $company->id)
            ->where('role', 'worker')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $gerants = User::where('company_id', $company->id)
            ->where('role', 'manager')
            ->orderBy('name')
            ->get();

        $today = today();
        $pointagesAujourdhui = OuvrierPointage::where('company_id', $company->id)
            ->whereDate('date', $today)
            ->get();

        $debutMois = $today->copy()->startOfMonth();
        $finMois   = $today->copy()->endOfMonth();

        $totalMoisGagne = $ouvriers->sum(fn($o) => $this->montantGagne($o, $debutMois, $finMois));
        $totalMoisPaye  = $ouvriers->sum(fn($o) => $this->totalPaye($o->id, $debutMois, $finMois));
        $totalSolde     = $ouvriers->sum(fn($o) => $this->montantGagne($o) - $this->totalPaye($o->id));

        // Pré-calcul des soldes par ouvrier pour la vue
        $soldes = $ouvriers->mapWithKeys(fn($o) => [
            $o->id => $this->montantGagne($o) - $this->totalPaye($o->id),
        ]);

        return view('client.ouvriers.index', compact(
            'company', 'ouvriers', 'gerants',
            'pointagesAujourdhui', 'today',
            'totalMoisGagne', 'totalMoisPaye', 'totalSolde', 'soldes',
        ));
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $company = $this->company();
        $v = $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => 'nullable|string|max:20',
            'role'           => 'required|in:worker,manager',
            'profession'     => 'nullable|string|max:100',
            'taux_journalier'=> 'required|numeric|min:0',
        ]);

        User::create([
            'name'            => $v['name'],
            'phone'           => $v['phone'] ?? null,
            'role'            => $v['role'],
            'company_id'      => $company->id,
            'taux_journalier' => $v['taux_journalier'],
            'is_active'       => true,
            'password'        => Hash::make(Str::random(16)),
        ]);

        return back()->with('success', "\"{$v['name']}\" ajouté.");
    }

    public function update(Request $request, User $ouvrier)
    {
        $company = $this->company();
        abort_if($ouvrier->company_id !== $company->id, 403);
        $v = $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => 'nullable|string|max:20',
            'taux_journalier'=> 'required|numeric|min:0',
        ]);
        $ouvrier->update($v);
        return back()->with('success', "\"{$ouvrier->name}\" mis à jour.");
    }

    public function destroy(User $ouvrier)
    {
        $company = $this->company();
        abort_if($ouvrier->company_id !== $company->id, 403);
        $name = $ouvrier->name;
        $ouvrier->delete();
        return back()->with('success', "\"$name\" supprimé.");
    }

    public function toggle(User $ouvrier)
    {
        $company = $this->company();
        abort_if($ouvrier->company_id !== $company->id, 403);
        $ouvrier->update(['is_active' => !$ouvrier->is_active]);
        $msg = $ouvrier->is_active ? "{$ouvrier->name} activé." : "{$ouvrier->name} désactivé.";
        return back()->with('success', $msg);
    }

    // ── Pointage journalier ───────────────────────────────────────────────────
    public function pointage(Request $request)
    {
        $company = $this->company();
        $date    = $request->get('date', today()->toDateString());

        $ouvriers = User::where('company_id', $company->id)
            ->where('role', 'worker')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $pointagesMap = OuvrierPointage::where('company_id', $company->id)
            ->whereDate('date', $date)
            ->pluck('statut', 'user_id');

        return view('client.ouvriers.pointage', compact('company', 'ouvriers', 'date', 'pointagesMap'));
    }

    public function savePointage(Request $request)
    {
        $company = $this->company();
        $v = $request->validate([
            'date'       => 'required|date',
            'pointages'  => 'nullable|array',
            'pointages.*'=> 'in:present,absent,demi',
        ]);

        $ouvriers = User::where('company_id', $company->id)
            ->where('role', 'worker')
            ->where('is_active', true)
            ->pluck('id');

        foreach ($ouvriers as $userId) {
            $statut = $v['pointages'][$userId] ?? 'absent';
            OuvrierPointage::updateOrCreate(
                ['user_id' => $userId, 'date' => $v['date']],
                ['company_id' => $company->id, 'statut' => $statut, 'initiated_by' => auth()->id()]
            );
        }

        return back()->with('success', "Pointage du {$v['date']} enregistré.");
    }

    // ── Historique ────────────────────────────────────────────────────────────
    public function historique(Request $request)
    {
        $company        = $this->company();
        $mois           = (int) $request->get('mois', now()->month);
        $annee          = (int) $request->get('annee', now()->year);
        $debut          = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin            = Carbon::create($annee, $mois, 1)->endOfMonth();
        $ouvrierFilter  = $request->get('ouvrier_id');
        $categoryFilter = $request->get('category_id');
        $professionFilter = $request->get('profession_id');

        $professions = \DB::table('professions')->where('company_id', $company->id)->orderBy('name')->get();
        $categories  = \DB::table('worker_categories')
            ->whereIn('profession_id', $professions->pluck('id'))
            ->orderBy('name')->get();

        $query = User::where('company_id', $company->id)
            ->whereIn('role', ['worker', 'manager'])
            ->orderBy('name');

        if ($categoryFilter)  $query->where('category_id',  $categoryFilter);
        if ($professionFilter) $query->where('profession_id', $professionFilter);

        $ouvriers = User::where('company_id', $company->id)->whereIn('role', ['worker', 'manager'])->orderBy('name')->get();
        $ouvriersFiltres = $query->get();

        $stats = $ouvriersFiltres
            ->when($ouvrierFilter, fn($c) => $c->where('id', $ouvrierFilter))
            ->map(function (User $o) use ($debut, $fin) {
                $jours = $this->joursTravailles($o->id, $debut, $fin);
                $gagne = $this->montantGagne($o, $debut, $fin);
                $paye  = $this->totalPaye($o->id, $debut, $fin);
                $solde = $gagne - $paye;
                return compact('o', 'jours', 'gagne', 'paye', 'solde');
            });

        // Détail jour par jour d'un ouvrier spécifique
        $detail = collect();
        if ($ouvrierFilter) {
            $detail = OuvrierPointage::where('user_id', $ouvrierFilter)
                ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
                ->orderBy('date')->get();
        }

        // Liste globale des pointages par jour (tous les ouvriers filtrés)
        $pointagesParJour = OuvrierPointage::where('company_id', $company->id)
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->whereIn('user_id', $ouvriersFiltres->pluck('id'))
            ->when($ouvrierFilter, fn($q) => $q->where('user_id', $ouvrierFilter))
            ->with('user:id,name,category_id,profession_id')
            ->orderByDesc('date')
            ->orderBy('user_id')
            ->get()
            ->groupBy('date');

        $totalGagne = $stats->sum('gagne');
        $totalPaye  = $stats->sum('paye');
        $totalSolde = $stats->sum('solde');

        // Paiements du mois filtrés
        $paiementsDuMois = OuvrierPaiement::where('company_id', $company->id)
            ->whereBetween('date', [$debut->toDateString(), $fin->toDateString()])
            ->whereIn('user_id', $ouvriersFiltres->pluck('id'))
            ->when($ouvrierFilter, fn($q) => $q->where('user_id', $ouvrierFilter))
            ->with('user:id,name')
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return view('client.ouvriers.historique', compact(
            'company', 'ouvriers', 'ouvriersFiltres', 'stats', 'detail',
            'mois', 'annee', 'debut', 'ouvrierFilter', 'categoryFilter', 'professionFilter',
            'totalGagne', 'totalPaye', 'totalSolde',
            'professions', 'categories', 'pointagesParJour', 'paiementsDuMois',
        ));
    }

    // ── Paiement ─────────────────────────────────────────────────────────────
    public function storePaiement(Request $request, User $ouvrier)
    {
        $company = $this->company();
        abort_if($ouvrier->company_id !== $company->id, 403);
        $v = $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'date'    => 'required|date',
            'note'    => 'nullable|string|max:255',
        ]);
        OuvrierPaiement::create([
            ...$v,
            'company_id' => $company->id,
            'user_id'    => $ouvrier->id,
            'created_by' => auth()->id(),
        ]);

        // Créer aussi dans salary_payments → visible dans l'activité récente app mobile
        \App\Models\Payment::create([
            'company_id' => $company->id,
            'worker_id'  => $ouvrier->id,
            'paid_by_id' => auth()->id(),
            'amount'     => $v['montant'],
            'note'       => $v['note'] ?? null,
        ]);

        // Mettre à jour le solde visible dans l'app mobile
        $ouvrier->decrement('balance', $v['montant']);

        // Notification push pour l'ouvrier (visible dans son app mobile)
        $montantFmt = number_format((float)$v['montant'], 0, ',', ' ');
        SecNotification::notifier(
            $ouvrier->id,
            'paiement_recu',
            'Paiement reçu 💰',
            "Vous avez reçu un paiement de {$montantFmt} FCFA." . ($v['note'] ? " ({$v['note']})" : ''),
            ['amount' => (float)$v['montant'], 'date' => $v['date']]
        );

        return back()->with('success', "Paiement de {$v['montant']} enregistré pour {$ouvrier->name}.");
    }

    // ── Fiche ─────────────────────────────────────────────────────────────────
    public function show(User $ouvrier)
    {
        $company = $this->company();
        abort_if($ouvrier->company_id !== $company->id, 403);

        $debutMois = now()->startOfMonth();
        $finMois   = now()->endOfMonth();

        $pointagesMois = OuvrierPointage::where('user_id', $ouvrier->id)
            ->whereBetween('date', [$debutMois->toDateString(), $finMois->toDateString()])
            ->orderBy('date')->get();

        $paiements = OuvrierPaiement::where('user_id', $ouvrier->id)
            ->orderByDesc('date')->limit(20)->get();

        $joursMois  = $this->joursTravailles($ouvrier->id, $debutMois, $finMois);
        $gagneTotal = $this->montantGagne($ouvrier);
        $payeTotal  = $this->totalPaye($ouvrier->id);
        $solde      = $gagneTotal - $payeTotal;

        return view('client.ouvriers.show', compact(
            'company', 'ouvrier', 'pointagesMois',
            'paiements', 'joursMois', 'gagneTotal', 'payeTotal', 'solde',
        ));
    }
}
