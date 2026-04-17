<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['company', 'subscription.plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->latest()->paginate(20);

        $totalPaid    = Invoice::where('status', 'paid')->sum('amount');
        $totalPending = Invoice::where('status', 'pending')->sum('amount');
        $countPending = Invoice::where('status', 'pending')->count();

        return response()->json([
            'data' => $invoices->map(fn($i) => [
                'id'                => $i->id,
                'invoice_number'    => $i->invoice_number,
                'company_id'        => $i->company_id,
                'company_name'      => $i->company?->name,
                'plan_name'         => $i->subscription?->plan?->name,
                'amount'            => (float) $i->amount,
                'status'            => $i->status,
                'payment_method'    => $i->payment_method,
                'payment_reference' => $i->payment_reference,
                'subscription_id'   => $i->subscription_id,
                'paid_at'           => $i->paid_at?->format('d/m/Y'),
                'created_at'        => $i->created_at->format('d/m/Y H:i'),
            ]),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
                'total'        => $invoices->total(),
                'total_paid'   => (float) $totalPaid,
                'total_pending'=> (float) $totalPending,
                'count_pending'=> $countPending,
            ],
        ]);
    }
}
