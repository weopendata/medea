<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA - Nieuw bericht</title>
</head>
<body>
    <p>
        Uw heeft een nieuw bericht van {{ $user->firstName }} {{ $user->lastName}}.
    </p>
    <p>
        {!! nl2br($message) !!}
    </p>
    <p>
        U kan enkel antwoorden door de persoon via zijn email, {{ $user->email }} te contacteren.
    </p>
</body>
</html>