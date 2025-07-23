<!DOCTYPE html>
<html lang="en">
<head>
    <title>OPTIK MELATI</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="{{ asset('login/css/style.css') }}" />
</head>
<body class="img js-fullheight" style="background-image: url(login/images/bg.jpg)">
    <section class="ftco-section">
        @yield('login')
    </section>
    <script src="{{ asset('login/js/popper.js') }}"></script>
    <script src="{{ asset('login/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('login/js/jquery.min.js') }}"></script>
    <script src="{{ asset('login/js/main.js') }}"></script>
</body>
</html>
