<?php

namespace App\Http\Controllers\Api\Security;

use App\Http\Controllers\Controller;
use App\Models\SecJustification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SecJustificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = SecJustification::where('company_id', $user->company_id)
            ->with(['reviewer:id,name']);

        if ($user->role === 'agent_securite') {
            // Agent voit seulement les siennes
            $query->where('agent_id', $user->id);
        } elseif ($user->role === 'gerant_securite') {
            // Gérant voit seulement les agents de sa zone
            $agentsIds = User::where('zone_id', $user->zone_id)
                ->where('role', 'agent_securite')
                ->pluck('id');
            $query->whereIn('agent_id', $agentsIds);
        }
        // admin_securite voit tout (pas de filtre)

        $justifications = $query->orderByDesc('created_at')->get();

        return response()->json($justifications->map(fn($j) => $this->format($j)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'motif'        => 'required|in:maladie,voyage,mariage,bapteme,deces,visite,autre',
            'description'  => 'nullable|string|max:1000',
            'date_absence' => 'required|date',
            'document'     => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $user = $request->user();
        $documentPath = null;

        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store(
                'justifications/' . $user->company_id,
                'public'
            );
        }

        $justification = SecJustification::create([
            'company_id'   => $user->company_id,
            'agent_id'     => $user->id,
            'motif'        => $request->motif,
            'description'  => $request->description,
            'date_absence' => $request->date_absence,
            'document_path' => $documentPath,
            'status'       => 'pending',
        ]);

        return response()->json($this->format($justification->load('reviewer')), 201);
    }

    public function destroy(Request $request, SecJustification $justification)
    {
        $user = $request->user();
        abort_if($justification->company_id !== $user->company_id, 403);
        abort_if($justification->agent_id !== $user->id, 403);
        abort_if($justification->status !== 'pending', 422, 'Impossible de supprimer une justification déjà traitée.');

        if ($justification->document_path) {
            Storage::disk('public')->delete($justification->document_path);
        }
        $justification->delete();

        return response()->json(['message' => 'Justification supprimée.']);
    }

    private function format(SecJustification $j): array
    {
        return [
            'id'               => $j->id,
            'motif'            => $j->motif,
            'motif_label'      => $j->motif_label,
            'description'      => $j->description,
            'date_absence'     => $j->date_absence?->toDateString(),
            'document_url'     => $j->document_path ? asset('storage/' . $j->document_path) : null,
            'status'           => $j->status,
            'reviewer_name'    => $j->reviewer?->name,
            'reviewer_comment' => $j->reviewer_comment,
            'reviewed_at'      => $j->reviewed_at?->toIso8601String(),
            'created_at'       => $j->created_at->toIso8601String(),
        ];
    }
}
