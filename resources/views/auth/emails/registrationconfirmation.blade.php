<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA registratie</title>
</head>
<body>
    <h3>Beste {{ $user->firstName . ' ' . $user->lastName }}</h3>
    <p>
        Uw registratie werd goedgekeurd! U kan nu inloggen op het <a href='{{ url("login") }}'>MEDEA platform</a>.
    </p>

    <p>
    Vriendelijke groeten,
    </p>
    <p>
    Het MEDEA team
    </p>
</body>
</html>