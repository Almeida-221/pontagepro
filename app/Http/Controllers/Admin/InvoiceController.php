<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
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
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        $totalPaid    = Invoice::where('status', 'paid')->sum('amount');
        $totalPending = Invoice::where('status', 'pending')->sum('amount');
        $countPending = Invoice::where('status', 'pending')->count();

        return view('admin.invoices.index', compact('invoices', 'totalPaid', 'totalPending', 'countPending'));
    }
}
