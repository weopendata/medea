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
        Je registratie op MEDEA werd niet goedgekeurd. Op de MEDEA-website vind je een overzicht van de verschillende gebruikersrollen.
        Bij verdere vragen kun je contact opnemen met het MEDEA-team via de contactgegevens onderaan deze mail.
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