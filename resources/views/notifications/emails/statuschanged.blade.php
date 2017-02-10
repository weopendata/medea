<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nieuwe status voor uw vondst</title>
</head>
<body>
    <p>
        De vondst, {{ $title }} werd behandeld door een validator. <a href='{{ url("/finds/$findId") }}'>Klik hier</a> om de vondst te bekijken.
    </p>
</body>
</html>