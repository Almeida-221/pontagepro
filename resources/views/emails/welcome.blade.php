<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur SB Pointage</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #2563EB; color: #fff; padding: 30px 40px; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 30px 40px; color: #333; }
        .credentials { background: #f0f7ff; border: 1px solid #2563EB; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .footer { background: #f4f4f4; padding: 20px 40px; text-align: center; font-size: 12px; color: #888; }
        .btn { display: inline-block; background: #2563EB; color: #fff; padding: 12px 28px; border-radius: 6px; text-decoration: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SB Pointage</h1>
            <p style="margin:5px 0 0;">Gestion de pointage des employés</p>
        </div>
        <div class="body">
            <h2>Bienvenue, {{ $company->full_owner_name }} !</h2>
            <p>Votre compte a été créé avec succès sur <strong>SB Pointage</strong>.</p>
            <p>Voici vos identifiants de connexion :</p>
            <div class="credentials">
                <p><strong>Email :</strong> {{ $user->email }}</p>
                <p><strong>Mot de passe :</strong> {{ $plainPassword }}</p>
            </div>
            <p>Nous vous recommandons de changer votre mot de passe après votre première connexion.</p>
            <a href="{{ config('app.url') }}/connexion" class="btn">Se connecter</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SB Pointage. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
