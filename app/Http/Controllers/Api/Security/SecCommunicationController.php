<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Jobs\SendFcmNotifications;
use App\Models\SecAffectation;
use App\Models\SecCommunication;
use App\Models\User;
use Illuminate\Http\Request;

class SecCommunicationController extends Controller
{
    // ── Format commun ────────────────────────────────────────────────────────
    private function format(SecCommunication $c): array
    {
        return [
            'id'         => $c->id,
            'title'      => $c->title,
            'message'    => $c->message,
            'audio_url'  => $c->audio_path
                ? url('storage/' . $c->audio_path)
                : null,
            'created_by' => $c->creator?->name,
            'created_at' => $c->created_at->toIso8601String(),
            'expires_at' => $c->expires_at?->toIso8601String(),
            'poste_ids'  => $c->poste_ids,
            'zone_ids'   => $c->zone_ids,
            'tour_ids'   => $c->tour_ids,
        ];
    }

    // ── GET /securite/communications ─────────────────────────────────────────
    // Admin/gérant : toutes les communications de l'entreprise
    // Agent        : uniquement celles ciblant son poste/zone/tour
    public function index(Request $request)
    {
        $user = $request->user();

        if (in_array($user->role, ['company_admin', 'gerant_securite'])) {
            $communications = SecCommunication::where('company_id', $user->company_id)
                ->with('creator:id,name')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($c) => $this->format($c));

            return response()->json(['data' => $communications]);
        }

        // Affectation active de l'agent
        $affectation = SecAffectation::where('agent_id', $user->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        $agentPosteId = $affectation?->poste_id;
        $agentZoneId  = $user->zone_id;
        $agentTours   = collect($affectation?->tours ?? [])->pluck('type')->toArray();

        $communications = SecCommunication::where('company_id', $user->company_id)
            ->active()
            ->with('creator:id,name')
            ->where(function ($q) use ($agentPosteId) {
                $q->whereNull('poste_ids');
                if ($agentPosteId) {
                    $q->orWhereJsonContains('poste_ids', $agentPosteId);
                }
            })
            ->where(function ($q) use ($agentZoneId) {
                $q->whereNull('zone_ids');
                if ($agentZoneId) {
                    $q->orWhereJsonContains('zone_ids', $agentZoneId);
                }
            })
            ->where(function ($q) use ($agentTours) {
                $q->whereNull('tour_ids');
                foreach ($agentTours as $tour) {
                    $q->orWhereJsonContains('tour_ids', $tour);
                }
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => $this->format($c));

        return response()->json(['data' => $communications]);
    }

    // ── POST /securite/communications ────────────────────────────────────────
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['company_admin', 'gerant_securite'])) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'nullable|string',
            'audio'      => 'nullable|file|mimetypes:audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/wav,audio/ogg,audio/webm,video/mp4|max:20480',
            'expires_at' => 'nullable|date|after:now',
            'poste_ids'  => 'nullable|array',
            'zone_ids'   => 'nullable|array',
            'tour_ids'   => 'nullable|array',
        ]);

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('communications', 'public');
        }

        $communication = SecCommunication::create([
            'company_id' => $user->company_id,
            'title'      => $request->title,
            'message'    => $request->message,
            'audio_path' => $audioPath,
            'created_by' => $user->id,
            'expires_at' => $request->expires_at,
            'poste_ids'  => !empty($request->poste_ids) ? $request->poste_ids : null,
            'zone_ids'   => !empty($request->zone_ids)  ? $request->zone_ids  : null,
            'tour_ids'   => !empty($request->tour_ids)  ? $request->tour_ids  : null,
        ]);

        // FCM push vers les agents/gérants de l'entreprise
        $tokens = User::where('company_id', $user->company_id)
            ->whereIn('role', ['agent_securite', 'gerant_securite'])
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (!empty($tokens)) {
            SendFcmNotifications::dispatch(
                $tokens,
                '📢 ' . $communication->title,
                $communication->message ?? 'Nouveau message vocal',
                [
                    'type'             => 'communication_new',
                    'communication_id' => (string) $communication->id,
                ]
            );
        }

        $communication->load('creator:id,name');

        return response()->json([
            'message' => 'Communication envoyée.',
            'data'    => $this->format($communication),
        ], 201);
    }

    // ── DELETE /securite/communications/{id} ─────────────────────────────────
    public function destroy(Request $request, SecCommunication $communication)
    {
        $user = $request->user();

        if ($communication->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        // Supprimer le fichier audio
        if ($communication->audio_path) {
            \Storage::disk('public')->delete($communication->audio_path);
        }

        $commId = $communication->id;
        $communication->delete();

        // FCM silent push vers tous les appareils de l'entreprise
        $tokens = User::where('company_id', $user->company_id)
            ->whereIn('role', ['agent_securite', 'gerant_securite', 'company_admin'])
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (!empty($tokens)) {
            SendFcmNotifications::dispatch($tokens, '', '', [
                'type'             => 'communication_deleted',
                'communication_id' => (string) $commId,
            ]);
        }

        return response()->json(['message' => 'Communication supprimée.']);
    }
}
