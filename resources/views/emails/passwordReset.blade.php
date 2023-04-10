<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pershendetje</title>
</head>

<body>
    <h1>Kerkese per rikthim fjalekalimi!</h1>
    <p>E/I dashur {{ $name }},</p>
    <p>Ju keni bere nje kerkese per rikthim fjalekalimi</p>
    <div>
        <a href="http://localhost:3000/user/reset?token={{ $token }}" class="btn btn-primary"
            data-style="zoom-in"><span class="ladda-label">Rikthe</span></a>
    </div>
    <span>nese nuk ishit ju, thjesht injoroni kete email

    </span>
    <p>Best regards,</p>
    <p>The team</p>
</body>

</html>
