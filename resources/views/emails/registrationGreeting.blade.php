<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pershendetje</title>
</head>

<body>
    <h1>Miresevini ne Sermo!</h1>
    <p>E/I dashur {{ $name }},</p>
    <p>Faleminderit qe jeni bere pjese e jona! Per te vazhduar me tej duhet te konfirmoni email tuaj!</p>
    <div>
        <a href="http://127.0.0.1:8000/api/confirmEmail?token={{ $token }}" class="btn btn-primary"
            data-style="zoom-in"><span class="ladda-label">Konfirmo</span></a>
    </div>
    <span>nese nuk ishit ju, thjesht injoroni kete email

    </span>
    <p>Best regards,</p>
    <p>The team</p>
</body>

</html>
