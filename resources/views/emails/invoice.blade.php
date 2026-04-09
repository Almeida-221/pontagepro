<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #2563EB; color: #fff; padding: 30px 40px; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 30px 40px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f0f7ff; font-weight: bold; }
        .total { font-size: 18px; font-weight: bold; color: #2563EB; }
        .footer { background: #f4f4f4; padding: 20px 40px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SB Pointage</h1>
            <p style="margin:5px 0 0;">Facture {{ $invoice->invoice_number }}</p>
        </div>
        <div class="body">
            <h2>Facture pour {{ $invoice->company->name }}</h2>
            <table>
                <tr>
                    <th>Description</th>
                    <th>Montant</th>
                </tr>
                @if($invoice->subscription)
                <tr>
                    <td>Abonnement {{ $invoice->subscription->plan->name }}</td>
                    <td>{{ $invoice->formatted_amount }}</td>
                </tr>
                @endif
                <tr>
                    <td class="total">Total</td>
                    <td class="total">{{ $invoice->formatted_amount }}</td>
                </tr>
            </table>
            <p><strong>Statut :</strong> {{ $invoice->status_label }}</p>
            @if($invoice->paid_at)
            <p><strong>Payée le :</strong> {{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
            @endif
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SB Pointage. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
