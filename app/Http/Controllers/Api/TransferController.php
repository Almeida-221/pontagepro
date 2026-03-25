<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /** POST /transfers  {recipient_phone, amount, note?} */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_phone' => 'required|string',
            'amount'          => 'required|numeric|min:1',
        ]);

        $sender = $request->user();
        $amount = (float) $request->amount;

        if ($amount > (float) $sender->balance) {
            return response()->json(['message' => 'Solde insuffisant'], 422);
        }

        $recipient = User::where('phone', $request->recipient_phone)
            ->where('company_id', $sender->company_id)
            ->where('id', '!=', $sender->id)
            ->first();

        if (!$recipient) {
            return response()->json(['message' => 'Ouvrier introuvable dans votre entreprise'], 404);
        }

        DB::transaction(function () use ($sender, $recipient, $amount, $request) {
            // Débit/crédit
            $sender->decrement('balance', $amount);
            $recipient->increment('balance', $amount);

            // Historique
            DB::table('transfers')->insert([
                'company_id'   => $sender->company_id,
                'sender_id'    => $sender->id,
                'recipient_id' => $recipient->id,
                'amount'       => $amount,
                'note'         => $request->note,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Notification pour le destinataire
            $montantFmt = number_format($amount, 0, ',', ' ');
            SecNotification::notifier(
                $recipient->id,
                'transfert_recu',
                'Transfert reçu 💸',
                "Vous avez reçu {$montantFmt} FCFA de {$sender->name}.",
                ['sender_id' => $sender->id, 'sender_name' => $sender->name, 'amount' => $amount]
            );
        });

        return response()->json([
            'message'       => 'Transfert effectué avec succès.',
            'new_balance'   => (float) $sender->fresh()->balance,
            'recipient_name'=> $recipient->name,
        ]);
    }

    /** GET /transfers/lookup?phone=xxx — chercher un ouvrier par téléphone */
    public function lookup(Request $request)
    {
        $sender = $request->user();
        $phone  = $request->get('phone');

        $worker = User::where('phone', $phone)
            ->where('company_id', $sender->company_id)
            ->where('role', 'worker')
            ->where('id', '!=', $sender->id)
            ->first(['id', 'name', 'phone']);

        if (!$worker) {
            return response()->json(['message' => 'Aucun ouvrier trouvé'], 404);
        }

        return response()->json(['worker' => $worker]);
    }
}
