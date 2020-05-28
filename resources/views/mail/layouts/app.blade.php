<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="text/html">

  <title>{{config('app.name')}} - @yield('title')</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" />

  <!-- Styles -->
  <style>
    html,
    body {
      background-color: #fff;
      color: #383a3b;
      font-family: 'Nunito', sans-serif;
      font-weight: 500;
      font-size:20px;
      height: 100vh;
      margin: 0;
    }

    table{
      width: 1000px; 
      border: 1px solid #e6dcdc;
      background: #ffffff;
      margin-left:auto;
      margin-right:auto;
    }

    table td {
      padding: 5px;
    }

    ul {
      line-height: 1.6;
    }

    .title {
      font-size: 34px;
    }
    
  </style>
</head>

<body>
  <div class="container">
    @yield('content')
  </div>
</body>

</html>