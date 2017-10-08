<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nieuwe status voor uw vondst</title>
</head>
<body>
    <p>
        Beste {{$user->firstName}} {{$user->lastName}},
    </p>
    <p>
        Hartelijk dank voor je vondstmelding op MEDEA:
    <p>
    <p>
        {{ $title }}
    </p>
    <p>
        Deze melding werd ondertussen nagekeken door een validator en gepubliceerd. <a href='{{ url("/finds/$findId") }}'>Klik hier</a> om de vondst te bekijken en indien nodig, de vondstinformatie te corrigeren en aan te vullen.
    </p>
    <p>
        Aarzel niet om ons te contacteren met vragen of opmerkingen via de contactgegevens onderaan deze mail.
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