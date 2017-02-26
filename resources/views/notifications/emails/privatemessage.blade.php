<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA - Nieuw bericht</title>
</head>
<body>
    <p>
        Uw heeft een nieuw bericht van {{ $sender->firstName }} {{ $sender->lastName}}.
    </p>
    <p>
        {!! nl2br(e($message)) !!}
    </p>
    <p>
        U kan enkel antwoorden door de persoon via zijn email, {{ $sender->email }} te contacteren.
    </p>
</body>
</html>