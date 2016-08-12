<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset uw paswoord</title>
</head>
<body>
    <h1>Wachtwoord vergeten?</h1>

    <p>
        Geen zorgen, klik op de volgende link om ze opnieuw in te stellen: <a href="{{ $link = url('password/reset', $user->getPasswordResetToken()).'?email='.urlencode($user->email) }}"> {{ $link }} </a>

    </p>
</body>
</html>