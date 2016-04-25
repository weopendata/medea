<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA registratie</title>
</head>
<body>
    <h3>De volgende gebruiker heeft zich geregistreerd op het platform en moet geverifieerd worden</h3>

    <ul>
        <li>
        email: {{ $user->email }}
        </li>
        <li>
        naam: {{ $user->firstName . ' ' . $user->lastName }}
        </li>
        <li>
        rollen: {{ $roles }}
        </li>
    </ul>
    <p>
        Om de registratie goed te keuren <a href='{{ url("register/confirm/{$user->token}") }}'>klik hier</a>.
    </p>
    <p>
        Om de registratie af te keuren <a href='{{ url("register/deny/{$user->token}") }}'>klik hier</a>.
    </p>
</body>
</html>