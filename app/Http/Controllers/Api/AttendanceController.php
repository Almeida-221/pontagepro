<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OuvrierPaiement;
use App\Models\Payment;
use App\Models\SecNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    /** POST /attendance/clock-in  {worker_id} */
    public function clockIn(Request $request)
    {
        $request->validate(['worker_id' => 'required|integer']);

        $scanner  = $request->user();
        $worker   = User::where('id', $request->worker_id)
                        ->where('company_id', $scanner->company_id)
                        ->firstOrFail();

        $today = Carbon::today();

        $dailyRate = $worker->taux_journalier > 0
            ? $worker->taux_journalier
            : $worker->category?->daily_rate;

        $attendance = Attendance::firstOrCreate(
            ['worker_id' => $worker->id, 'date' => $today],
            [
                'company_id' => $scanner->company_id,
                'daily_rate' => $dailyRate,
            ]
        );

        if ($attendance->entry_time) {
            return response()->json(['message' => 'Déjà pointé (entrée)'], 409);
        }

        $clockTime = $request->clock_time
            ? Carbon::parse($request->clock_time)
            : Carbon::now();

        $attendance->update(['entry_time' => $clockTime]);

        return response()->json(['attendance' => $attendance->fresh()->toApiArray()]);
    }

    /** POST /attendance/clock-out  {worker_id} */
    public function clockOut(Request $request)
    {
        $request->validate(['worker_id' => 'required|integer']);

        $scanner  = $request->user();
        $worker   = User::where('id', $request->worker_id)
                        ->where('company_id', $scanner->company_id)
                        ->firstOrFail();

        $today = Carbon::today();

        $attendance = Attendance::where('worker_id', $worker->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->entry_time) {
            return response()->json(['message' => 'Entrée non enregistrée'], 422);
        }

        if ($attendance->exit_time) {
            return response()->json(['message' => 'Déjà pointé (sortie)'], 409);
        }

        $clockTime = $request->clock_time
            ? Carbon::parse($request->clock_time)
            : Carbon::now();

        $earned = $attendance->daily_rate;
        $attendance->update([
            'exit_time'     => $clockTime,
            'amount_earned' => $earned,
        ]);

        // Increment worker balance
        if ($earned) {
            $worker->increment('balance', $earned);
        }

        // Notification in-app pour l'ouvrier/gérant
        $dateFr = Carbon::parse($attendance->date)->locale('fr')->isoFormat('dddd D MMMM');
        $montantFmt = number_format((float)$earned, 0, ',', ' ');
        SecNotification::notifier(
            $worker->id,
            'pointage_journee',
            'Journée validée ✅',
            "Votre journée du $dateFr a été validée. Vous avez gagné $montantFmt FCFA. Bonne soirée !",
            ['attendance_id' => $attendance->id, 'amount' => (float)$earned, 'date' => $attendance->date->toDateString()]
        );

        // Synchroniser avec ouvrier_pointages pour l'interface web admin
        \App\Models\OuvrierPointage::updateOrCreate(
            ['user_id' => $worker->id, 'date' => $today],
            ['company_id' => $scanner->company_id, 'statut' => 'present', 'initiated_by' => $scanner->id]
        );

        return response()->json(['attendance' => $attendance->fresh()->toApiArray()]);
    }

    /** GET /attendance/today  — list for manager/admin */
    public function today(Request $request)
    {
        $user   = $request->user();
        $today  = Carbon::today();

        $records = Attendance::with('worker')
            ->where('company_id', $user->company_id)
            ->whereDate('date', $today)
            ->orderByDesc('entry_time')
            ->get()
            ->map(fn($a) => $a->toApiArray());

        return response()->json(['records' => $records]);
    }

    /** DELETE /attendance/{id}  — admin cancels a record */
    public function destroy(Request $request, $id)
    {
        $admin      = $request->user();
        $attendance = \App\Models\Attendance::where('company_id', $admin->company_id)
                        ->findOrFail($id);

        $workerId = $attendance->worker_id;
        $earned   = $attendance->amount_earned ?? 0;

        $attendance->delete();

        // Recalculate worker balance from remaining attendance records
        $totalEarned = \App\Models\Attendance::where('worker_id', $workerId)
                        ->whereNotNull('amount_earned')
                        ->sum('amount_earned');

        User::where('id', $workerId)->update(['balance' => $totalEarned]);

        return response()->json(['message' => 'Pointage annulé', 'new_balance' => $totalEarned]);
    }

    /** GET /attendance/my  — worker's own history */
    public function my(Request $request)
    {
        $user = $request->user();

        $attendance = Attendance::where('worker_id', $user->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->map(fn($a) => $a->toApiArray());

        $payments = OuvrierPaiement::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'type'       => 'payment',
                'worker_id'  => $p->user_id,
                'amount'     => (float) $p->montant,
                'note'       => $p->note,
                'paid_by'    => null,
                'created_at' => $p->created_at?->toIso8601String(),
            ]);

        $transfersSent = \DB::table('transfers')
            ->where('sender_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'type'           => 'transfer_sent',
                'amount'         => (float) $t->amount,
                'note'           => $t->note,
                'other_party_id' => $t->recipient_id,
                'other_name'     => \App\Models\User::find($t->recipient_id)?->name ?? '',
                'created_at'     => $t->created_at,
            ]);

        $transfersReceived = \DB::table('transfers')
            ->where('recipient_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn($t) => [
                'id'             => $t->id,
                'type'           => 'transfer_received',
                'amount'         => (float) $t->amount,
                'note'           => $t->note,
                'other_party_id' => $t->sender_id,
                'other_name'     => \App\Models\User::find($t->sender_id)?->name ?? '',
                'created_at'     => $t->created_at,
            ]);

        return response()->json([
            'records'            => $attendance,
            'payments'           => $payments,
            'transfers_sent'     => $transfersSent,
            'transfers_received' => $transfersReceived,
            'balance'            => (float) ($user->fresh()->balance ?? 0),
        ]);
    }
}
