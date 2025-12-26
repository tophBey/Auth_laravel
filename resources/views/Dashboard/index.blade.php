<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<h1>halo {{ auth()->user()->name }}</h1>
      <form method="post" action="{{ route('logout') }}" >
                   @csrf
                   <button type="submit">
                   Logout</button>
              </form>
</body>
</html>