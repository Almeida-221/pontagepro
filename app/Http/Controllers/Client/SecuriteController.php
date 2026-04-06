<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasActiveCompany;
use App\Models\SecAffectation;
use App\Models\SecCommunication;
use App\Models\SecJustification;
use App\Models\SecNotification;
use App\Models\SecPointage;
use App\Models\SecPointageResponse;
use App\Models\SecPoste;
use App\Models\SecRemplacement;
use App\Models\SecTour;
use App\Models\SecZone;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Jobs\SendFcmNotifications;
use App\Services\FcmService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SecuriteController extends Controller
{
    use HasActiveCompany;

    private function company()
    {
        $company = $this->activeCompany();
        abort_unless($company->module?->slug === 'securite-privee', 403,
            'Cette entreprise n\'a pas le module Sécurité Privée.');
        return $company;
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────
    public function index()
    {
        $company = $this->company();

        $zones   = SecZone::where('company_id', $company->id)->withCount('postes')->get();
        $postes  = SecPoste::where('company_id', $company->id)->count();
        $agents  = User::where('company_id', $company->id)->where('role', 'agent_securite')->where('is_active', true)->count();
        $gerants = User::where('company_id', $company->id)->where('role', 'gerant_securite')->where('is_active', true)->count();

        $todayPointages = SecPointage::where('company_id', $company->id)
            ->whereDate('date', today())
            ->with(['zone', 'poste'])
            ->orderByDesc('created_at')
            ->get();

        return view('client.securite.index', compact('company', 'zones', 'postes', 'agents', 'gerants', 'todayPointages'));
    }

    // ── Zones ─────────────────────────────────────────────────────────────────
    public function zones()
    {
        $company = $this->company();
        $zones   = SecZone::where('company_id', $company->id)->withCount('postes')->latest()->get();
        return view('client.securite.zones', compact('company', 'zones'));
    }

    public function storeZone(Request $request)
    {
        $company = $this->company();
        $v = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        SecZone::create([...$v, 'company_id' => $company->id]);
        return back()->with('success', "Zone \"{$v['name']}\" créée.");
    }

    public function destroyZone(SecZone $zone)
    {
        $company = $this->company();
        abort_if($zone->company_id !== $company->id, 403);
        $zone->delete();
        return back()->with('success', "Zone supprimée.");
    }

    // ── Postes ────────────────────────────────────────────────────────────────
    public function postes()
    {
        $company = $this->company();
        $postes  = SecPoste::where('company_id', $company->id)->with('zone')->latest()->get();
        $zones   = SecZone::where('company_id', $company->id)->get();
        return view('client.securite.postes', compact('company', 'postes', 'zones'));
    }

    public function storePoste(Request $request)
    {
        $company = $this->company();
        $v = $request->validate([
            'name'        => 'required|string|max:100',
            'zone_id'     => 'required|exists:sec_zones,id',
            'description' => 'nullable|string|max:255',
        ]);
        abort_if(SecZone::find($v['zone_id'])?->company_id !== $company->id, 403);
        SecPoste::create([...$v, 'company_id' => $company->id]);
        return back()->with('success', "Poste \"{$v['name']}\" créé.");
    }

    public function destroyPoste(SecPoste $poste)
    {
        $company = $this->company();
        abort_if($poste->company_id !== $company->id, 403);
        $poste->delete();
        return back()->with('success', "Poste supprimé.");
    }

    // ── Tours de travail ──────────────────────────────────────────────────────
    public function tours()
    {
        $company = $this->company();
        $tours   = SecTour::where('company_id', $company->id)->orderBy('ordre')->get();
        return view('client.securite.tours', compact('company', 'tours'));
    }

    public function storeTour(Request $request)
    {
        $company = $this->company();
        $count   = SecTour::where('company_id', $company->id)->count();
        if ($count >= 4) {
            return back()->with('error', 'Maximum 4 tours par entreprise.');
        }
        $v = $request->validate([
            'nom'         => 'required|string|max:100',
            'emoji'       => 'nullable|string|max:10',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin'   => 'nullable|date_format:H:i',
        ]);
        SecTour::create([
            'company_id'  => $company->id,
            'nom'         => $v['nom'],
            'emoji'       => $v['emoji'] ?? '🕐',
            'heure_debut' => $v['heure_debut'] ?? null,
            'heure_fin'   => $v['heure_fin']   ?? null,
            'ordre'       => $count + 1,
        ]);
        return back()->with('success', "Tour \"{$v['nom']}\" créé.");
    }

    public function updateTour(Request $request, SecTour $tour)
    {
        $company = $this->company();
        abort_if($tour->company_id !== $company->id, 403);
        $v = $request->validate([
            'nom'         => 'required|string|max:100',
            'emoji'       => 'nullable|string|max:10',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin'   => 'nullable|date_format:H:i',
        ]);
        $tour->update($v);
        return back()->with('success', "Tour mis à jour.");
    }

    public function destroyTour(SecTour $tour)
    {
        $company = $this->company();
        abort_if($tour->company_id !== $company->id, 403);
        $nom = $tour->nom;
        $tour->delete();
        // Re-numéroter
        SecTour::where('company_id', $company->id)->orderBy('ordre')->each(function ($t, $i) {
            $t->update(['ordre' => $i + 1]);
        });
        return back()->with('success', "Tour \"$nom\" supprimé.");
    }

    // ── Agents & Gérants ──────────────────────────────────────────────────────
    public function agents(Request $request)
    {
        $company     = $this->company();
        $role        = $request->get('role', 'all');
        $search      = $request->get('search');
        $zoneFilter  = $request->get('zone_id');
        $posteFilter = $request->get('poste_id');

        $query = User::where('company_id', $company->id)
            ->whereIn('role', ['agent_securite', 'gerant_securite'])
            ->with(['planning', 'affectation.poste', 'zone'])
            ->latest();

        if ($role === 'agent')  $query->where('role', 'agent_securite');
        if ($role === 'gerant') $query->where('role', 'gerant_securite');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        if ($zoneFilter)  $query->where('zone_id', $zoneFilter);
        if ($posteFilter) $query->whereHas('affectation', fn($q) => $q->where('poste_id', $posteFilter)->where('is_active', true));

        $members = $query->paginate(20)->withQueryString();
        $zones   = SecZone::where('company_id', $company->id)->get();
        $postes  = \App\Models\SecPoste::where('company_id', $company->id)->with('zone')->get();
        return view('client.securite.agents.index', compact('company', 'members', 'zones', 'postes', 'role', 'search', 'zoneFilter', 'posteFilter'));
    }

    public function createAgent()
    {
        $company = $this->company();
        $zones   = SecZone::where('company_id', $company->id)->get();
        $postes  = SecPoste::where('company_id', $company->id)->with('zone')->get();
        $tours   = SecTour::where('company_id', $company->id)->orderBy('ordre')->get();
        return view('client.securite.agents.create', compact('company', 'zones', 'postes', 'tours'));
    }

    public function storeAgent(Request $request)
    {
        $company = $this->company();

        $v = $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => 'required|string|max:20|unique:users,phone',
            'role'           => 'required|in:agent_securite,gerant_securite',
            'gender'         => 'required|in:m,f',
            'zone_id'        => 'nullable|exists:sec_zones,id',
            'poste_id'       => 'nullable|exists:sec_postes,id',
            'photo'          => 'nullable|image|max:3072',
            'id_photo_front' => 'nullable|image|max:3072',
            'id_photo_back'  => 'nullable|image|max:3072',
            // Contrat
            'is_employed'    => 'nullable|boolean',
            'salary'         => 'nullable|numeric|min:0',
            'contract_type'  => 'nullable|in:CDI,CDD,prestataire',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date|after:contract_start',
            // Planning
            'rest_days'      => 'nullable|array',
            'rest_days.*'    => 'integer|min:1|max:7',
            'off_days'       => 'nullable|array',
            'off_days.*'     => 'integer|min:1|max:31',
            'tours'          => 'nullable|array',
        ]);

        // Store photos
        $photo   = $request->file('photo')?->store("agents/{$company->id}/photos", 'public');
        $front   = $request->file('id_photo_front')?->store("agents/{$company->id}/ids", 'public');
        $back    = $request->file('id_photo_back')?->store("agents/{$company->id}/ids", 'public');

        $agent = User::create([
            'name'           => $v['name'],
            'phone'          => $v['phone'],
            'role'           => $v['role'],
            'gender'         => $v['gender'],
            'company_id'     => $company->id,
            'zone_id'        => $v['zone_id'] ?? null,
            'password'       => Hash::make(Str::random(16)),
            'pin_code'       => null,
            'is_active'      => true,
            'is_employed'    => $request->boolean('is_employed'),
            'salary'         => $v['salary'] ?? null,
            'contract_type'  => $v['contract_type'] ?? null,
            'contract_start' => $v['contract_start'] ?? null,
            'contract_end'   => $v['contract_end'] ?? null,
            'photo'          => $photo,
            'id_photo_front' => $front,
            'id_photo_back'  => $back,
        ]);

        // Affectation + planning dans le même enregistrement
        if ($v['role'] === 'agent_securite' && !empty($v['poste_id'])) {
            $poste = SecPoste::where('id', $v['poste_id'])->where('company_id', $company->id)->first();
            if ($poste) {
                $companyTours = SecTour::where('company_id', $company->id)->get();
                SecAffectation::create([
                    'agent_id'    => $agent->id,
                    'poste_id'    => $poste->id,
                    'assigned_by' => auth()->id(),
                    'started_at'  => now(),
                    'is_active'   => true,
                    'rest_days'   => array_values(array_unique(array_map('intval', $v['rest_days'] ?? []))),
                    'off_days'    => array_values(array_unique(array_map('intval', $v['off_days']  ?? []))),
                    'tours'       => $this->parseTours($request->input('tours', []), $companyTours),
                ]);

                // Notify agent of initial post assignment
                SecNotification::notifier(
                    $agent->id,
                    'affectation',
                    '📍 Nouvelle affectation',
                    "Vous avez été affecté au poste « {$poste->name} ».",
                    ['poste_id' => $poste->id, 'poste_name' => $poste->name],
                );
                if ($agent->fcm_token) {
                    FcmService::sendToTokens(
                        [$agent->fcm_token],
                        '📍 Nouvelle affectation',
                        "Vous avez été affecté au poste « {$poste->name} ».",
                        ['type' => 'affectation', 'poste_id' => (string) $poste->id],
                    );
                }
            }
        }

        return redirect()->route('client.securite.agents')
            ->with('success', "{$agent->name} ajouté avec succès.");
    }

    public function editAgent(User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);
        $agent->load(['affectation.poste', 'zone']);
        $zones  = SecZone::where('company_id', $company->id)->get();
        $postes = SecPoste::where('company_id', $company->id)->with('zone')->get();
        $tours  = SecTour::where('company_id', $company->id)->orderBy('ordre')->get();

        // Historique des affectations (toutes, archivées + active)
        $historique = SecAffectation::where('agent_id', $agent->id)
            ->with(['poste', 'poste.zone'])
            ->orderByDesc('started_at')
            ->get();

        // Compteurs remplacements
        $remplacementsCount = [
            'sortant' => SecRemplacement::where('agent_sortant_id', $agent->id)->count(),
            'entrant' => SecRemplacement::where('agent_entrant_id', $agent->id)->count(),
        ];

        return view('client.securite.agents.edit', compact('company', 'agent', 'zones', 'postes', 'tours', 'historique', 'remplacementsCount'));
    }

    public function agentPlanning(User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);

        $affectations = SecAffectation::where('agent_id', $agent->id)
            ->with(['poste', 'poste.zone'])
            ->orderByDesc('started_at')
            ->get();

        $remplacements = SecRemplacement::where('company_id', $company->id)
            ->where(fn($q) => $q->where('agent_sortant_id', $agent->id)
                                ->orWhere('agent_entrant_id', $agent->id))
            ->with(['poste', 'zone', 'agentSortant', 'agentEntrant'])
            ->orderByDesc('date')
            ->get();

        return view('client.securite.agents.planning', compact('agent', 'affectations', 'remplacements'));
    }

    public function updateAgent(Request $request, User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);

        $v = $request->validate([
            'name'           => 'required|string|max:150',
            'phone'          => "required|string|max:20|unique:users,phone,{$agent->id}",
            'gender'         => 'required|in:m,f',
            'zone_id'        => 'nullable|exists:sec_zones,id',
            'poste_id'       => 'nullable|exists:sec_postes,id',
            'is_employed'    => 'nullable|boolean',
            'salary'         => 'nullable|numeric|min:0',
            'contract_type'  => 'nullable|in:CDI,CDD,prestataire',
            'contract_start' => 'nullable|date',
            'contract_end'   => 'nullable|date',
            'rest_days'      => 'nullable|array',
            'rest_days.*'    => 'integer|min:1|max:7',
            'off_days'       => 'nullable|array',
            'off_days.*'     => 'integer|min:1|max:31',
            'tours'          => 'nullable|array',
        ]);

        // Update user fields
        $agent->update([
            'name'           => $v['name'],
            'phone'          => $v['phone'],
            'gender'         => $v['gender'],
            'zone_id'        => $v['zone_id'] ?? null,
            'is_employed'    => $request->boolean('is_employed'),
            'salary'         => $v['salary'] ?? null,
            'contract_type'  => $v['contract_type'] ?? null,
            'contract_start' => $v['contract_start'] ?? null,
            'contract_end'   => $v['contract_end'] ?? null,
        ]);

        // Update planning if agent — archive old affectation, create new
        if ($agent->role === 'agent_securite') {
            $posteId      = $v['poste_id'] ?? null;
            $restDays     = array_values(array_unique(array_map('intval', $v['rest_days'] ?? [])));
            $offDays      = array_values(array_unique(array_map('intval', $v['off_days']  ?? [])));
            $companyTours = SecTour::where('company_id', $company->id)->get();
            $tours        = $this->parseTours($request->input('tours', []), $companyTours);

            // Close current affectation
            SecAffectation::where('agent_id', $agent->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'ended_at' => now()]);

            // Create new affectation
            if ($posteId) {
                SecAffectation::create([
                    'agent_id'    => $agent->id,
                    'poste_id'    => $posteId,
                    'assigned_by' => auth()->id(),
                    'started_at'  => now(),
                    'is_active'   => true,
                    'rest_days'   => $restDays,
                    'off_days'    => $offDays,
                    'tours'       => $tours,
                ]);

                // Notify agent of new post assignment
                $newPoste = SecPoste::find($posteId);
                if ($newPoste) {
                    $posteName = $newPoste->name;
                    SecNotification::notifier(
                        $agent->id,
                        'affectation',
                        '📍 Nouvelle affectation',
                        "Vous avez été affecté au poste « {$posteName} ».",
                        ['poste_id' => $newPoste->id, 'poste_name' => $posteName],
                    );
                    if ($agent->fcm_token) {
                        FcmService::sendToTokens(
                            [$agent->fcm_token],
                            '📍 Nouvelle affectation',
                            "Vous avez été affecté au poste « {$posteName} ».",
                            ['type' => 'affectation', 'poste_id' => (string) $newPoste->id],
                        );
                    }
                }
            } else {
                // Seul le planning a changé (pas de nouveau poste) → notifier quand même
                $details = [];
                if (!empty($restDays)) $details[] = 'jours de repos';
                if (!empty($offDays))  $details[] = 'jours de congé';
                if (!empty($tours))    $details[] = 'tours de travail';
                if (!empty($details)) {
                    $detailMsg = implode(', ', $details) . ' modifié(s)';
                    SecNotification::notifier(
                        $agent->id,
                        'planning_changed',
                        '📅 Emploi du temps modifié',
                        "Votre planning a été mis à jour : {$detailMsg}.",
                        [],
                    );
                    if ($agent->fcm_token) {
                        FcmService::sendToTokens(
                            [$agent->fcm_token],
                            '📅 Emploi du temps modifié',
                            "Votre planning a été mis à jour : {$detailMsg}.",
                            ['type' => 'planning_changed'],
                        );
                    }
                }
            }
        }

        return redirect()->route('client.securite.agents')
            ->with('success', "{$agent->name} mis à jour.");
    }

    /**
     * Normalise le tableau tours soumis par le formulaire HTML.
     * Clés = tour IDs, résolution du nom via $companyTours.
     */
    private function parseTours(array $raw, $companyTours = null): array
    {
        $result = [];
        foreach ($raw as $tourId => $data) {
            if (empty($data['start']) || empty($data['end'])) continue;
            // Si on a la collection des tours, on résout le nom par ID
            if ($companyTours) {
                $tour = $companyTours->firstWhere('id', (int) $tourId);
                if (!$tour) continue;
                $result[] = ['type' => $tour->nom, 'start' => $data['start'], 'end' => $data['end']];
            } else {
                // Compatibilité ancienne (clé = nom du tour)
                $result[] = ['type' => $tourId, 'start' => $data['start'], 'end' => $data['end']];
            }
        }
        return $result;
    }

    public function toggleAgent(User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);
        $agent->update(['is_active' => !$agent->is_active]);
        $status = $agent->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "{$agent->name} $status.");
    }

    public function resetPin(User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);
        // Use DB directly to guarantee the NULL is written (Eloquent may skip unchanged values)
        \DB::table('users')->where('id', $agent->id)->update(['pin_code' => null]);
        // Revoke all mobile tokens so the agent must go through setup again
        $agent->tokens()->delete();
        return back()->with('success', "PIN de {$agent->name} réinitialisé. Il pourra définir un nouveau PIN depuis l'application.");
    }

    public function destroyAgent(User $agent)
    {
        $company = $this->company();
        abort_if($agent->company_id !== $company->id, 403);
        $name = $agent->name;
        $agent->delete();
        return back()->with('success', "$name supprimé.");
    }

    // ── Lancer pointage à distance ────────────────────────────────────────────
    public function lancerPointage(Request $request)
    {
        $company    = $this->company();
        $tourNoms   = SecTour::where('company_id', $company->id)->pluck('nom')->toArray();
        // Fallback pour les anciennes entreprises sans tours configurés
        if (empty($tourNoms)) $tourNoms = ['matin', 'soir', 'nuit'];

        $request->validate([
            'tour'     => ['required', Rule::in($tourNoms)],
            'zone_id'  => 'nullable|exists:sec_zones,id',
            'poste_id' => 'nullable|exists:sec_postes,id',
        ]);

        // Vérifier unicité zone/poste/tour/jour
        $duplicate = SecPointage::where('company_id', $company->id)
            ->where('type', 'remote')
            ->whereDate('date', today())
            ->where('tour', $request->tour)
            ->when($request->zone_id,  fn($q) => $q->where('zone_id',  $request->zone_id))
            ->when($request->poste_id, fn($q) => $q->where('poste_id', $request->poste_id))
            ->exists();

        if ($duplicate) {
            return back()->with('error',
                'Un pointage a déjà été lancé pour cette zone/poste/tour aujourd\'hui.');
        }

        // Find eligible agents: active, affectation active, filtered by zone/poste
        $today   = Carbon::today();
        $weekday = $today->dayOfWeekIso; // 1=Lun … 7=Dim

        $agents = User::where('company_id', $company->id)
            ->where('role', 'agent_securite')
            ->where('is_active', true)
            ->whereHas('secAffectationActive', function ($q) use ($request) {
                if ($request->poste_id) {
                    $q->where('poste_id', $request->poste_id);
                } elseif ($request->zone_id) {
                    $q->whereHas('poste', fn($p) => $p->where('zone_id', $request->zone_id));
                }
            })
            ->with('secAffectationActive.poste', 'secAffectationActive')
            ->get();

        if ($agents->isEmpty()) {
            return back()->with('error', 'Aucun agent actif affecté trouvé pour ces critères.');
        }

        // Filtrer selon jour de repos / congé / tour (comme l'API mobile)
        $tourDemande = mb_strtolower($request->tour);
        $agents = $agents->filter(function (User $agent) use ($tourDemande, $weekday, $today) {
            $planning = $agent->secAffectationActive;
            if (!$planning) return true;

            $restDays = is_array($planning->rest_days) ? $planning->rest_days : [];
            if (in_array($weekday, array_map('intval', $restDays))) return false;

            $offDays = is_array($planning->off_days) ? $planning->off_days : [];
            if (in_array((int) $today->day, array_map('intval', $offDays))) return false;

            $tours = is_array($planning->tours) ? $planning->tours : [];
            if (!empty($tours)) {
                $hasTour = collect($tours)->contains(
                    fn($t) => mb_strtolower($t['type'] ?? '') === $tourDemande
                );
                if (!$hasTour) return false;
            }

            return true;
        });

        if ($agents->isEmpty()) {
            return back()->with('error',
                'Aucun agent disponible pour ce tour (tous en repos, congé, ou non affectés à ce tour).');
        }

        // Exclure les agents déjà pointés présents pour ce tour aujourd'hui
        $dejaPointesIds = SecPointageResponse::where('status', 'present')
            ->whereHas('pointage', fn($q) => $q
                ->where('company_id', $company->id)
                ->whereDate('date', today())
                ->where('tour', $request->tour)
            )
            ->pluck('agent_id')
            ->toArray();

        $agents = $agents->reject(fn($a) => in_array($a->id, $dejaPointesIds));

        if ($agents->isEmpty()) {
            return back()->with('error',
                "Tous les agents ont déjà été pointés pour le tour « {$request->tour} » aujourd'hui.");
        }

        $tourLabel  = $request->tour;

        // Create the pointage session
        $pointage = SecPointage::create([
            'company_id'   => $company->id,
            'initiated_by' => auth()->id(),
            'zone_id'      => $request->zone_id,
            'poste_id'     => $request->poste_id,
            'tour'         => $request->tour,
            'type'         => 'remote',
            'status'       => 'pending',
            'date'         => today(),
            'expires_at'   => now()->addMinutes(15),
        ]);

        // Create response records and in-app notifications for each agent
        foreach ($agents as $agent) {
            $affectation = $agent->secAffectationActive;

            SecPointageResponse::create([
                'pointage_id' => $pointage->id,
                'agent_id'    => $agent->id,
                'zone_id'     => $affectation?->poste?->zone_id,
                'poste_id'    => $affectation?->poste_id,
                'status'      => 'pending',
            ]);

            SecNotification::create([
                'user_id' => $agent->id,
                'type'    => 'pointage',
                'title'   => "Pointage {$tourLabel}",
                'message' => "Confirmez votre présence dans les 15 minutes (tour {$tourLabel}).",
                'data'    => ['pointage_id' => $pointage->id, 'tour' => $request->tour],
            ]);
        }

        // ── Push FCM notifications (asynchrone via job) ───────────────────────
        $fcmTokens = $agents->pluck('fcm_token')->filter()->values()->toArray();
        if (!empty($fcmTokens)) {
            SendFcmNotifications::dispatch(
                $fcmTokens,
                "🔔 Pointage {$tourLabel} — Action requise",
                "Confirmez votre présence dans les 15 minutes.",
                [
                    'type'        => 'pointage',
                    'pointage_id' => (string) $pointage->id,
                    'tour'        => $request->tour,
                ]
            );
        }

        $notified = count($fcmTokens);
        return redirect()
            ->route('client.securite.pointage', ['date' => today()->toDateString()])
            ->with('success', "Pointage {$tourLabel} lancé pour {$agents->count()} agent(s). {$notified} notification(s) push envoyée(s).");
    }

    // ── Pointage report ───────────────────────────────────────────────────────
    public function pointage(Request $request)
    {
        $company    = $this->company();
        $date       = $request->get('date', today()->toDateString());
        $zoneFilter  = $request->get('zone_id');
        $posteFilter = $request->get('poste_id');
        $tourFilter  = $request->get('tour');

        $query = SecPointage::where('company_id', $company->id)
            ->whereDate('date', $date)
            ->with(['zone', 'poste', 'initiator', 'responses.agent', 'responses.poste', 'responses.zone'])
            ->orderByDesc('created_at');

        if ($zoneFilter)  $query->where('zone_id',  $zoneFilter);
        if ($posteFilter) $query->where('poste_id', $posteFilter);
        if ($tourFilter)  $query->where('tour',      $tourFilter);

        $pointages = $query->get();
        $zones     = \App\Models\SecZone::where('company_id', $company->id)->get();
        $postes    = \App\Models\SecPoste::where('company_id', $company->id)->with('zone')->get();
        $tours     = SecTour::where('company_id', $company->id)->orderBy('ordre')->get();

        return view('client.securite.pointage', compact('company', 'pointages', 'date', 'zones', 'postes', 'tours', 'zoneFilter', 'posteFilter', 'tourFilter'));
    }

    public function carte(Request $request)
    {
        $company  = $this->company();
        $date     = $request->get('date', today()->toDateString());
        $zoneFilter  = $request->get('zone_id');
        $posteFilter = $request->get('poste_id');
        $tourFilter  = $request->get('tour');

        // Load all pointages for the date with full relations
        $query = SecPointage::where('company_id', $company->id)
            ->whereDate('date', $date)
            ->with([
                'zone:id,name',
                'poste:id,name,zone_id,latitude,longitude,address',
                'poste.zone:id,name',
                'responses.agent:id,name',
                'responses.poste:id,name,latitude,longitude',
                'responses.zone:id,name',
            ]);

        if ($zoneFilter)  $query->where('zone_id',  $zoneFilter);
        if ($tourFilter)  $query->where('tour',      $tourFilter);

        $pointages = $query->orderByDesc('created_at')->get();

        // Build poste markers: group by poste
        $markers = [];
        foreach ($pointages as $p) {
            // Collect all postes referenced in responses (multi-poste support)
            $responsesGroupedByPoste = $p->responses->groupBy('poste_id');

            foreach ($responsesGroupedByPoste as $posteId => $responses) {
                if ($posteFilter && $posteId != $posteFilter) continue;

                $rPoste = $responses->first()->poste;
                if (!$rPoste || !$rPoste->latitude || !$rPoste->longitude) continue;

                $key = $posteId . '_' . ($p->tour ?? 'local');
                if (!isset($markers[$key])) {
                    $markers[$key] = [
                        'poste_id'   => $rPoste->id,
                        'poste_name' => $rPoste->name,
                        'zone_id'    => $rPoste->zone_id,
                        'zone_name'  => $rPoste->zone?->name ?? ($p->zone?->name ?? '—'),
                        'lat'        => $rPoste->latitude,
                        'lng'        => $rPoste->longitude,
                        'tour'       => $p->tour,
                        'tour_label' => match($p->tour) { 'matin'=>'Matin','soir'=>'Soir','nuit'=>'Nuit',default=>'Local' },
                        'agents'     => [],
                    ];
                }

                foreach ($responses as $r) {
                    $markers[$key]['agents'][] = [
                        'name'     => $r->agent?->name ?? '—',
                        'status'   => $r->status,
                        'time'     => $r->responded_at?->format('H:i'),
                    ];
                }
            }
        }

        // Compute stats per marker
        $mapData = array_values(array_map(function ($m) {
            $present = count(array_filter($m['agents'], fn($a) => $a['status'] === 'present'));
            $absent  = count(array_filter($m['agents'], fn($a) => $a['status'] === 'absent'));
            $pending = count(array_filter($m['agents'], fn($a) => $a['status'] === 'pending'));
            $m['stats'] = ['present' => $present, 'absent' => $absent, 'pending' => $pending, 'total' => count($m['agents'])];
            return $m;
        }, $markers));

        $zones  = SecZone::where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);
        $postes = SecPoste::where('company_id', $company->id)->orderBy('name')->get(['id', 'zone_id', 'name']);

        return view('client.securite.carte', compact('company', 'mapData', 'zones', 'postes', 'date', 'zoneFilter', 'posteFilter', 'tourFilter'));
    }

    public function destroyPointage(SecPointage $pointage)
    {
        $company = $this->company();
        abort_if($pointage->company_id !== $company->id, 403);
        $date = $pointage->date;

        // Supprimer les notifications liées à ce pointage (avant cascade des réponses)
        SecNotification::whereJsonContains('data->pointage_id', $pointage->id)->delete();

        // La suppression cascade les sec_pointage_responses automatiquement (FK cascadeOnDelete)
        $pointage->delete();

        return redirect()->route('client.securite.pointage', ['date' => $date])
            ->with('success', 'Pointage supprimé.');
    }

    /** JSON live-status endpoint — polled by JS every 3 s */
    public function pointageLiveStatus(SecPointage $pointage)
    {
        $company = $this->company();
        abort_if($pointage->company_id !== $company->id, 403);

        // Auto-expire pending responses
        if ($pointage->type === 'remote' && $pointage->status === 'pending' && now()->isAfter($pointage->expires_at)) {
            $pointage->responses()->where('status', 'pending')->update(['status' => 'absent']);
            $pointage->update(['status' => 'completed']);
            $pointage->refresh();
        }

        $responses = $pointage->responses()->with(['agent', 'zone', 'poste'])->get();

        return response()->json([
            'status'   => $pointage->status,
            'expires_at' => $pointage->expires_at->toIso8601String(),
            'summary'  => [
                'total'   => $responses->count(),
                'present' => $responses->where('status', 'present')->count(),
                'absent'  => $responses->where('status', 'absent')->count(),
                'pending' => $responses->where('status', 'pending')->count(),
            ],
            'responses' => $responses->map(fn($r) => [
                'id'           => $r->id,
                'agent_name'   => $r->agent->name,
                'zone_name'    => $r->zone?->name ?? '—',
                'poste_name'   => $r->poste?->name ?? '—',
                'status'       => $r->status,
                'responded_at' => $r->responded_at?->format('H:i'),
            ])->values(),
        ]);
    }

    // ── Justifications d'absence ──────────────────────────────────────────────
    public function justifications(Request $request)
    {
        $company = $this->company();

        $query = SecJustification::where('company_id', $company->id)
            ->with(['agent:id,name', 'reviewer:id,name'])
            ->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $justifications = $query->get();
        $pending   = $justifications->where('status', 'pending')->count();
        $validated = $justifications->where('status', 'validated')->count();
        $rejected  = $justifications->where('status', 'rejected')->count();

        return view('client.securite.justifications.index',
            compact('company', 'justifications', 'pending', 'validated', 'rejected'));
    }

    public function validerJustification(SecJustification $justification)
    {
        $company = $this->company();
        abort_if($justification->company_id !== $company->id, 403);

        $justification->update([
            'status'           => 'validated',
            'reviewer_id'      => auth()->id(),
            'reviewer_comment' => request('comment'),
            'reviewed_at'      => now(),
        ]);

        // Notify agent
        $agent = \App\Models\User::find($justification->agent_id);
        $dateStr = $justification->date_absence->format('d/m/Y');
        SecNotification::notifier(
            $justification->agent_id,
            'justification',
            'Justification validée ✅',
            "Votre justification d'absence du {$dateStr} a été validée.",
            ['justification_id' => $justification->id],
        );
        if ($agent?->fcm_token) {
            FcmService::sendToTokens(
                [$agent->fcm_token],
                'Justification validée ✅',
                "Votre justification d'absence du {$dateStr} a été validée.",
                ['type' => 'justification', 'justification_id' => (string) $justification->id],
            );
        }

        return back()->with('success', 'Justification validée.');
    }

    public function rejeterJustification(SecJustification $justification)
    {
        $company = $this->company();
        abort_if($justification->company_id !== $company->id, 403);

        $justification->update([
            'status'           => 'rejected',
            'reviewer_id'      => auth()->id(),
            'reviewer_comment' => request('comment'),
            'reviewed_at'      => now(),
        ]);

        // Notify agent
        $agent = \App\Models\User::find($justification->agent_id);
        $dateStr = $justification->date_absence->format('d/m/Y');
        SecNotification::notifier(
            $justification->agent_id,
            'justification',
            'Justification refusée ❌',
            "Votre justification d'absence du {$dateStr} a été refusée.",
            ['justification_id' => $justification->id],
        );
        if ($agent?->fcm_token) {
            FcmService::sendToTokens(
                [$agent->fcm_token],
                'Justification refusée ❌',
                "Votre justification d'absence du {$dateStr} a été refusée.",
                ['type' => 'justification', 'justification_id' => (string) $justification->id],
            );
        }

        return back()->with('error', 'Justification refusée.');
    }

    // ── Remplacements ─────────────────────────────────────────────────────────
    public function remplacements(Request $request)
    {
        $company = $this->company();

        $query = SecRemplacement::where('company_id', $company->id)
            ->with(['agentSortant', 'agentEntrant', 'poste', 'zone'])
            ->orderByDesc('date')
            ->orderByDesc('heure_entree');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        if ($request->filled('poste_id')) {
            $query->where('poste_id', $request->poste_id);
        }
        if ($request->filled('agent_id')) {
            $id = (int) $request->agent_id;
            $query->where(fn($q) =>
                $q->where('agent_sortant_id', $id)->orWhere('agent_entrant_id', $id)
            );
        }

        $remplacements = $query->paginate(30)->withQueryString();

        // Stats
        $today   = SecRemplacement::where('company_id', $company->id)->whereDate('date', today())->count();
        $week    = SecRemplacement::where('company_id', $company->id)->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $month   = SecRemplacement::where('company_id', $company->id)->whereMonth('date', now()->month)->whereYear('date', now()->year)->count();

        $zones   = SecZone::where('company_id', $company->id)->orderBy('name')->get();
        $postes  = SecPoste::where('company_id', $company->id)->orderBy('name')->get();
        $agents  = User::where('company_id', $company->id)
            ->whereIn('role', ['agent_securite', 'gerant_securite'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('client.securite.remplacements.index', compact(
            'company', 'remplacements', 'zones', 'postes', 'agents',
            'today', 'week', 'month'
        ));
    }

    public function destroyRemplacement(SecRemplacement $remplacement)
    {
        $company = $this->company();
        abort_if($remplacement->company_id !== $company->id, 403);
        $remplacement->delete();
        return back()->with('success', 'Remplacement supprimé.');
    }

    // ── Communications ────────────────────────────────────────────────────────
    public function communications()
    {
        $company = $this->company();
        $communications = SecCommunication::where('company_id', $company->id)
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->get();
        $zones  = SecZone::where('company_id', $company->id)->get();
        $postes = SecPoste::where('company_id', $company->id)->with('zone')->get();
        $tours  = SecTour::where('company_id', $company->id)->orderBy('ordre')->get();
        return view('client.securite.communications.index', compact(
            'communications', 'zones', 'postes', 'tours'
        ));
    }

    public function storeCommunication(Request $request)
    {
        $company = $this->company();
        $v = $request->validate([
            'title'      => 'required|string|max:200',
            'message'    => 'nullable|string|max:1000',
            'audio'      => 'nullable|file|mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/wav,audio/ogg,audio/webm,video/mp4|max:20480',
            'expires_at' => 'nullable|date|after:now',
            'poste_ids'  => 'nullable|array',
            'poste_ids.*'=> 'integer|exists:sec_postes,id',
            'zone_ids'   => 'nullable|array',
            'zone_ids.*' => 'integer|exists:sec_zones,id',
            'tour_ids'   => 'nullable|array',
            'tour_ids.*' => 'string',
        ]);

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('communications', 'public');
        }

        $communication = SecCommunication::create([
            'company_id' => $company->id,
            'title'      => $v['title'],
            'message'    => $v['message'] ?? null,
            'audio_path' => $audioPath,
            'poste_ids'  => !empty($v['poste_ids']) ? array_map('intval', $v['poste_ids']) : null,
            'zone_ids'   => !empty($v['zone_ids'])  ? array_map('intval', $v['zone_ids'])  : null,
            'tour_ids'   => !empty($v['tour_ids'])  ? $v['tour_ids']  : null,
            'created_by' => auth()->id(),
            'expires_at' => $v['expires_at'] ?? null,
        ]);

        // FCM push vers les agents/gérants de l'entreprise
        $tokens = User::where('company_id', $company->id)
            ->whereIn('role', ['agent_securite', 'gerant_securite'])
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (!empty($tokens)) {
            SendFcmNotifications::dispatchSync(
                $tokens,
                '📢 ' . $communication->title,
                $communication->message ?? 'Nouveau message vocal',
                [
                    'type'             => 'communication_new',
                    'communication_id' => (string) $communication->id,
                ]
            );
        }

        return back()->with('success', 'Communication envoyée aux agents ciblés.');
    }

    public function destroyCommunication(SecCommunication $communication)
    {
        $company = $this->company();
        abort_if($communication->company_id !== $company->id, 403);

        if ($communication->audio_path) {
            \Storage::disk('public')->delete($communication->audio_path);
        }

        $commId = $communication->id;
        $communication->delete();

        // Notifier tous les agents via FCM (message silencieux)
        $tokens = User::where('company_id', $company->id)
            ->whereIn('role', ['agent_securite', 'gerant_securite'])
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (!empty($tokens)) {
            SendFcmNotifications::dispatch($tokens, '', '', [
                'type'             => 'communication_deleted',
                'communication_id' => (string) $commId,
            ]);
        }

        return back()->with('success', 'Communication supprimée et retirée des téléphones.');
    }
}
