<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Напоминание о выплате дивидендов</title>
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
            Напоминание о выплате дивидендов
        </div>
        
        <div class="content">
            <p>Ваш договор № <strong>{{ $contract->contract_number }}</strong></p>
            
            <p>Дата следующей выплаты: <strong>{{ $paymentDate->translatedFormat('d F Y') }}</strong></p>

            <p>До выплаты осталось <strong>{{ $daysRemaining }}</strong> дней.</p>
        </div>

        <div class="footer">
            <p>С уважением,<br>
               Команда СБЕРФОНД</p>
        </div>
    </div>
</body>
</html>
