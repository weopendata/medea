<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nieuwe vondst</title>
</head>
<body>
    <p>
    Beste {{$user->firstName}} {{$user->lastName}},
    </p>
    <p>
      Hartelijk dank voor je vondstmelding op MEDEA:
    </p>
    <p>
    {{$title}}
    </p>
    <p>
      Deze melding zal worden behandeld door een validator. Klik <a href="{{url('/finds/' . $findId)}}">hier</a> om de vondst te bekijken.
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