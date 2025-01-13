
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание пользователя</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }
        .content {
            font-size: 16px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            text-align: center;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Завершение регистрации
        </div>
        
        <div class="content">
            <p>Для завершения настройки вашего аккаунта, пожалуйста, создайте пароль.</p>
            
            <p>Нажмите на кнопку ниже, чтобы задать собственный пароль</p>

            <a href="{{ $url }}" class="button">
                Создать пароль
            </a>

            <p>Если вы не регистрировались в нашей системе, просто проигнорируйте это письмо.</p>
        </div>

        <div class="footer">
            <p>С уважением,<br>
               Команда СБЕРФОНД</p>
        </div>
    </div>
</body>
</html>
