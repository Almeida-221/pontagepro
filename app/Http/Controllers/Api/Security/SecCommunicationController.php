<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecCommunication;
use Illuminate\Http\Request;

class SecCommunicationController extends Controller
{
    // GET /securite/communications
    public function index(Request $request)
    {
        $user = $request->user();

        $communications = SecCommunication::where('company_id', $user->company_id)
            ->active()
            ->with('creator:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'title'      => $c->title,
                'message'    => $c->message,
                'audio_url'  => $c->audio_path
                    ? url('storage/' . $c->audio_path)
                    : null,
                'created_by' => $c->creator?->name,
                'created_at' => $c->created_at->toIso8601String(),
                'expires_at' => $c->expires_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $communications]);
    }

    // DELETE /securite/communications/{id}
    public function destroy(Request $request, SecCommunication $communication)
    {
        $user = $request->user();

        if ($communication->company_id !== $user->company_id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        if ($user->role === 'agent_securite') {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $communication->delete();

        return response()->json(['message' => 'Communication supprimée.']);
    }
}
