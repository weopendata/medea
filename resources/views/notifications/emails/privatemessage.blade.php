<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEDEA - Nieuw bericht</title>
</head>
<body>
    <p>
        Beste {{$recipient->firstName}} {{$recipient->lastName}},
    </p>
    <p>
        {{ $sender->firstName }} {{ $sender->lastName}} heeft je een bericht gestuurd via MEDEA:
    </p>
    <p>
        <i>{!! nl2br(e($message)) !!}</i>
    </p>
    <p>
        Je kunt dit bericht beantwoorden door deze persoon te contacteren via zijn emailadres {{ $sender->email }}
    </p>
    <p>
        Met vriendelijke groeten,<br/>
        Het MEDEA team
    </p>
    <p>
        <a href="vondsten.be" target="_blank">vondsten.be</a> || info: <a href="blog.vondsten.be" target="_blank">blog.vondsten.be</a> || Facebook: <a href="https://www.facebook.com/pg/MEDEAvondsten" target="_blank">MEDEAvondsten</a>
    </p>
</body>
</html>