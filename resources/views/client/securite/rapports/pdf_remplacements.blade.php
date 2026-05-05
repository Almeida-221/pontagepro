<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Remplacements — {{ $periodeLabel }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a1a; padding: 24px; }
        h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { font-size: 12px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        thead tr { background: #1e40af; color: #fff; }
        th { padding: 8px 10px; text-align: left; font-size: 11px; }
        th.center, td.center { text-align: center; }
        tbody tr:nth-child(even) { background: #f0f4ff; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; }
        .badge { display: inline-block; min-width: 30px; text-align: center;
                 padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .green  { background: #d1fae5; color: #065f46; }
        .red    { background: #fee2e2; color: #991b1b; }
        .blue   { background: #dbeafe; color: #1e40af; }
        .totals { margin-top: 16px; border-top: 2px solid #1e40af; padding-top: 10px;
                  display: flex; gap: 24px; font-size: 11px; }
        .totals strong { font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #888; text-align: right; }

        @media print {
            body { padding: 12px; }
            @page { margin: 15mm; }
        }
    </style>
</head>
<body>

    <h1>Rapport Remplacements</h1>
    <div class="subtitle">
        Entreprise : <strong>{{ $company->name }}</strong> &nbsp;|&nbsp;
        Période : <strong>{{ $periodeLabel }}</strong> &nbsp;|&nbsp;
        Généré le : <strong>{{ now()->translatedFormat('d F Y à H:i') }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom de l'agent</th>
                <th>Zone</th>
                <th>Poste</th>
                <th class="center">Jours travaillés</th>
                <th class="center">Jours absents</th>
                <th class="center">Jours de repos</th>
                <th class="center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $row)
            <tr>
                <td>{{ $row['agent'] }}</td>
                <td>{{ $row['zone'] }}</td>
                <td>{{ $row['poste'] }}</td>
                <td class="center"><span class="badge green">{{ $row['jours_travailles'] }}</span></td>
                <td class="center"><span class="badge red">{{ $row['jours_absents'] }}</span></td>
                <td class="center"><span class="badge blue">{{ $row['jours_repos'] }}</span></td>
                <td class="center">{{ $row['statut'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <span>Agents : <strong>{{ count($stats) }}</strong></span>
        <span style="color:#065f46">Jours travaillés : <strong>{{ array_sum(array_column($stats,'jours_travailles')) }}</strong></span>
        <span style="color:#991b1b">Jours absents : <strong>{{ array_sum(array_column($stats,'jours_absents')) }}</strong></span>
        <span style="color:#1e40af">Jours repos : <strong>{{ array_sum(array_column($stats,'jours_repos')) }}</strong></span>
    </div>

    <div class="footer">Rapport généré par SB Sécurité — {{ now()->translatedFormat('d/m/Y') }}</div>

    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
