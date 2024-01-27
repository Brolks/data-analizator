<!DOCTYPE html>
<html>
<head>
   <title>Загрузка файла</title>
   <link rel="stylesheet" href="{{ mix('/css/app.css') }}?{{ time() }}" />
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
</head>
<body>
   <div id="date-analyzer-root" data-csrf-token="{{ csrf_token() }}"></div>
   <script src="{{ mix('/js/DateAnalyzer.js') }}?{{time()}}"></script>
</body>
</html>
