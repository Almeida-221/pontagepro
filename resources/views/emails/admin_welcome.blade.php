<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès SB Pointage Mobile</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #4338CA; color: #fff; padding: 30px 40px; }
        .header h1 { margin: 0; font-size: 24px; }
        .body { padding: 30px 40px; color: #333; }
        .credentials { background: #f0f4ff; border: 1px solid #4338CA; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .pin { font-size: 36px; font-weight: bold; color: #4338CA; letter-spacing: 12px; text-align: center; padding: 10px 0; }
        .footer { background: #f4f4f4; padding: 20px 40px; text-align: center; font-size: 12px; color: #888; }
        .badge { display: inline-block; background: #4338CA; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SB Pointage</h1>
            <p style="margin:5px 0 0;">Application de gestion de pointage</p>
        </div>
        <div class="body">
            <h2>Bonjour {{ $admin->name }},</h2>
            <p>Vous avez été ajouté(e) en tant qu'<strong>Administrateur</strong> de l'entreprise <strong>{{ $company->name }}</strong> sur SB Pointage.</p>
            <p>Voici vos identifiants pour vous connecter sur l'application mobile :</p>

            <div class="credentials">
                <p><strong>Entreprise :</strong> {{ $company->name }}</p>
                <p><strong>Téléphone :</strong> {{ $admin->phone }}</p>
                <p><strong>Votre code PIN :</strong></p>
                <div class="pin">{{ $plainPin }}</div>
            </div>

            <p style="color:#e53e3e; font-size:13px;">
                Ne partagez jamais votre code PIN. Vous pouvez le changer depuis l'application mobile.
            </p>

            <p>Téléchargez l'application SB Pointage et connectez-vous avec votre numéro de téléphone et ce code PIN.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SB Pointage. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
