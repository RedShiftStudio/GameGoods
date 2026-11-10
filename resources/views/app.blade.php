<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Game Goods — игровые товары и услуги</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app" data-page="{{ $page }}" data-payload="{{ json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}" data-user="{{ json_encode(auth()->user(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}" data-errors="{{ json_encode($errors->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}" data-flash="{{ session('success') }}"></div>
</body>
</html>
