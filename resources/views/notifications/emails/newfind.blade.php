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
      Hartelijk dank voor je vondstmelding '{{$title}}'. Deze melding werd nu behandeld door een validator. Klik <a href="{{url('/finds/' . $findId)}}">hier</a> om de vondst te bekijken.
    </p>
</body>
</html>