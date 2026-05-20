<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <title>در حال انتقال به بانک...</title>
</head>

<body>
    <form id="samanForm" name="samanForm" method="POST" action="https://sep.shaparak.ir/OnlinePG/OnlinePG">
        <input type="hidden" name="Token" value="{{ $token }}">
        <input type="hidden" name="GetMethod" value="">
    </form>
    <p>در حال انتقال به بانک، لطفاً منتظر بمانید...</p>
    <script>
        document.getElementById('samanForm').submit();
    </script>
</body>

</html>
