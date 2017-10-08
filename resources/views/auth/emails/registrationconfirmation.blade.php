<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA registratie</title>
</head>
<body>
    <p>
        Beste {{ $user->firstName . ' ' . $user->lastName }},
    </p>
    <p>
        Hartelijk dank voor je aanmelding als MEDEA-gebruiker. Je registratie als {{ implode(', ', $user->getRoles()) }} werd goedgekeurd.
    </p>
    <p>
        Je kunt je nu inloggen op het <a href="{{ url("/") }}">MEDEA platform</a>. Gebruik hiervoor je emailadres en het wachtwoord dat je koos bij registratie.
    </p>
    <p>
        Aarzel niet om ons te contacteren met vragen of opmerkingen via de contactgegevens onderaan deze mail.
    </p>

    <p>
        Uw registratie werd goedgekeurd! U kan nu inloggen op het <a href='{{ url("login") }}'>MEDEA platform</a>.
    </p>
    <p>
        Met vriendelijke groeten,<br/>
        Het MEDEA team
    </p>
    <p>
        <a href="vondsten.be" target="_blank">vondsten.be</a> || info: <a href="blog.vondsten.be" target="_blank">blog.vondsten.be</a> || Facebook: MEDEAvondsten
    </p>
</body>
</html>