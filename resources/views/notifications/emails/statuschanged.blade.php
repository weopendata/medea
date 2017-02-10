<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nieuwe status voor uw vondst</title>
</head>
<body>
    <p>
        Hartelijk dank voor je melding van de volgende vondst op MEDEA:
    <p>
    <p>
        {{ $title }}
    </p>
    <p>
        Deze werd ondertussen nagekeken door een validator. <a href='{{ url("/finds/$findId") }}'>Klik hier</a> om de vondst te bekijken.
    </p>
</body>
</html>