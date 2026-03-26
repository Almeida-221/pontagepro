<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecNotification;
use App\Models\User;
use App\Services\FcmService;
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

        $montantFmt = number_format($amount, 0, ',', ' ');

        DB::transaction(function () use ($sender, $recipient, $amount, $request, $montantFmt) {
            $sender->decrement('balance', $amount);
            $recipient->increment('balance', $amount);

            DB::table('transfers')->insert([
                'company_id'   => $sender->company_id,
                'sender_id'    => $sender->id,
                'recipient_id' => $recipient->id,
                'amount'       => $amount,
                'note'         => $request->note,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Notification destinataire
            SecNotification::notifier(
                $recipient->id,
                'transfert_recu',
                'Transfert reçu 💸',
                "Vous avez reçu {$montantFmt} FCFA de {$sender->name}.",
                ['sender_id' => $sender->id, 'sender_name' => $sender->name, 'amount' => $amount]
            );
            // Notification expéditeur
            SecNotification::notifier(
                $sender->id,
                'transfert_envoye',
                'Transfert envoyé ✅',
                "Vous avez envoyé {$montantFmt} FCFA à {$recipient->name}.",
                ['recipient_id' => $recipient->id, 'recipient_name' => $recipient->name, 'amount' => $amount]
            );
        });

        // Push FCM
        if ($recipient->fcm_token) {
            FcmService::sendToTokens([$recipient->fcm_token], 'Transfert reçu 💸',
                "Vous avez reçu {$montantFmt} FCFA de {$sender->name}.",
                ['type' => 'transfert_recu']);
        }
        if ($sender->fcm_token) {
            FcmService::sendToTokens([$sender->fcm_token], 'Transfert envoyé ✅',
                "Vous avez envoyé {$montantFmt} FCFA à {$recipient->name}.",
                ['type' => 'transfert_envoye']);
        }

        return response()->json([
            'message'        => 'Transfert effectué avec succès.',
            'new_balance'    => (float) $sender->fresh()->balance,
            'recipient_name' => $recipient->name,
        ]);
    }

    /** POST /transfers/receive  {payer_phone, amount, note?} — le destinataire scanne le payeur */
    public function receive(Request $request)
    {
        $request->validate([
            'payer_phone' => 'required|string',
            'amount'      => 'required|numeric|min:1',
        ]);

        $recipient = $request->user(); // Sidiki
        $amount    = (float) $request->amount;

        $payer = User::where('phone', $request->payer_phone)
                     ->where('company_id', $recipient->company_id)
                     ->where('id', '!=', $recipient->id)
                     ->first();

        if (!$payer) {
            return response()->json(['message' => 'Ouvrier introuvable dans votre entreprise'], 404);
        }

        if ($amount > (float) $payer->balance) {
            return response()->json([
                'message' => 'Solde insuffisant chez ' . $payer->name . '.',
            ], 422);
        }

        $montantFmt = number_format($amount, 0, ',', ' ');

        DB::transaction(function () use ($payer, $recipient, $amount, $request, $montantFmt) {
            $payer->decrement('balance', $amount);
            $recipient->increment('balance', $amount);

            DB::table('transfers')->insert([
                'company_id'   => $recipient->company_id,
                'sender_id'    => $payer->id,
                'recipient_id' => $recipient->id,
                'amount'       => $amount,
                'note'         => $request->note,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // Notification payeur (Lamine)
            SecNotification::notifier(
                $payer->id,
                'transfert_debite',
                'Paiement effectué 💸',
                "{$montantFmt} FCFA ont été prélevés de votre compte par {$recipient->name}.",
                ['recipient_id' => $recipient->id, 'recipient_name' => $recipient->name, 'amount' => $amount]
            );
            // Notification destinataire (Sidiki)
            SecNotification::notifier(
                $recipient->id,
                'transfert_recu',
                'Paiement reçu ✅',
                "Vous avez reçu {$montantFmt} FCFA de {$payer->name}.",
                ['sender_id' => $payer->id, 'sender_name' => $payer->name, 'amount' => $amount]
            );
        });

        // Push FCM
        if ($payer->fcm_token) {
            FcmService::sendToTokens([$payer->fcm_token], 'Paiement effectué 💸',
                "{$montantFmt} FCFA ont été prélevés par {$recipient->name}.",
                ['type' => 'transfert_debite']);
        }
        if ($recipient->fcm_token) {
            FcmService::sendToTokens([$recipient->fcm_token], 'Paiement reçu ✅',
                "Vous avez reçu {$montantFmt} FCFA de {$payer->name}.",
                ['type' => 'transfert_recu']);
        }

        return response()->json([
            'message'     => 'Paiement reçu avec succès.',
            'new_balance' => (float) $recipient->fresh()->balance,
            'payer_name'  => $payer->name,
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
