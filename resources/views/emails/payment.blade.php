<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Token de confirmación</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" media="screen" href="main.css" />
    <script src="main.js"></script>
</head>
<body>
    <p>Hola sr(a): {{ $data->name }}</p>
    <p>A continuación su token para confimar su pago por un monto de: {{ $data->amount}}</p>
    <p>token: {{ $data->token }}</p>
    <p>Saludos cordiales.</p>
</body>
</html>