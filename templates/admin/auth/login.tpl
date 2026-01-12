<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Blog Admin</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div style="width: 100%; max-width: 400px; padding: 2rem;">
        <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin: 0 0 0.5rem;">Blog Admin</h1>
                <p style="color: #6b7280; margin: 0;">Connectez-vous pour continuer</p>
            </div>

            <form method="POST" action="/admin/login">
                <div style="margin-bottom: 1.25rem;">
                    <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Adresse email</label>
                    <input type="email" id="email" name="email" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;" required autofocus>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">Mot de passe</label>
                    <input type="password" id="password" name="password" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 1rem;" required>
                </div>

                <button type="submit" style="width: 100%; padding: 0.75rem 1rem; background: #4f46e5; color: white; font-size: 1rem; font-weight: 500; border: none; border-radius: 0.5rem; cursor: pointer;">Se connecter</button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; color: #6b7280; font-size: 0.875rem;">
                <a href="/" style="color: #4f46e5;">Retour au site</a>
            </div>
        </div>
    </div>
</body>
</html>
