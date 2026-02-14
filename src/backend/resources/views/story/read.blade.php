<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Chronicle</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #1a1a1a;
            color: #e0e0e0;
            line-height: 1.8;
        }
        h1 {
            color: #a0a0a0;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        p {
            margin: 1.5em 0;
            text-indent: 2em;
        }
        .nav {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        a {
            color: #888;
            text-decoration: none;
        }
        a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <h1>The Chronicle</h1>

    @foreach ($story->paragraphs as $paragraph)
        <p>{{ $paragraph }}</p>
    @endforeach

    <div class="nav">
        <a href="?cursor={{ $story->nextCursor }}">Tiếp tục →</a>
    </div>
</body>
</html>
