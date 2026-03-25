<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OuvrierPaiement;
use App\Models\Payment;
use App\Models\SecNotification;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** GET /payments — liste des paiements récents de l'entreprise */
    public function index(Request $request)
    {
        $user = $request->user();
        $payments = Payment::where('company_id', $user->company_id)
            ->with(['worker', 'paidBy'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        return response()->json(['payments' => $payments->map->toApiArray()]);
    }

    /** POST /payments  {worker_id, amount, note?} */
    public function store(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|integer',
            'amount'    => 'required|numeric|min:1',
        ]);

        $payer  = $request->user();
        $worker = User::where('id', $request->worker_id)
                      ->where('company_id', $payer->company_id)
                      ->firstOrFail();

        $amount = (float) $request->amount;

        if ($amount > (float) $worker->balance) {
            return response()->json(['message' => 'Solde insuffisant'], 422);
        }

        $payment = Payment::create([
            'company_id' => $payer->company_id,
            'worker_id'  => $worker->id,
            'paid_by_id' => $payer->id,
            'amount'     => $amount,
            'note'       => $request->note,
        ]);

        // Notification push pour l'ouvrier
        $montantFmt = number_format($amount, 0, ',', ' ');
        SecNotification::notifier(
            $worker->id,
            'paiement_recu',
            'Paiement reçu 💰',
            "Vous avez reçu un paiement de {$montantFmt} FCFA." . ($request->note ? " ({$request->note})" : ''),
            ['amount' => $amount]
        );

        // Synchroniser avec ouvrier_paiements (utilisé par la vue Laravel)
        OuvrierPaiement::create([
            'company_id' => $payer->company_id,
            'user_id'    => $worker->id,
            'montant'    => $amount,
            'date'       => now()->toDateString(),
            'note'       => $request->note ?? 'Paiement mobile',
            'created_by' => $payer->id,
        ]);

        $worker->decrement('balance', $amount);

        return response()->json([
            'payment'     => $payment->load(['paidBy', 'worker'])->toApiArray(),
            'new_balance' => (float) $worker->fresh()->balance,
        ]);
    }
}
