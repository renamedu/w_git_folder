<?php
// Путь к файлу логов
$logFile = '/var/log/crond.log';

// Функция для получения последних 100 строк из файла логов
function tail($file, $lines = 100) {
    // Открываем файл для чтения
    $f = fopen($file, 'r');
    $buffer = 4096; // Размер буфера для чтения
    fseek($f, -1, SEEK_END); // Перемещаем указатель в конец файла

    // Проверяем, заканчивается ли файл на новую строку, если нет, вычитаем одну строку
    if (fread($f, 1) != "\n") $lines -= 1;
    
    $output = ''; // Переменная для хранения выходных данных
    $chunk = ''; // Переменная для хранения текущего блока данных

    // Пока есть строки и не достигли начала файла
    while (ftell($f) > 0 && $lines >= 0) {
        $seek = min(ftell($f), $buffer); // Устанавливаем смещение
        fseek($f, -$seek, SEEK_CUR); // Перемещаем указатель назад на размер буфера
        $chunk = fread($f, $seek) . $chunk; // Читаем данные и добавляем к текущему блоку
        fseek($f, -$seek, SEEK_CUR); // Перемещаем указатель обратно
        $lines -= substr_count($chunk, "\n"); // Вычитаем количество строк, найденных в блоке
    }

    fclose($f); // Закрываем файл
    $output = explode("\n", trim($chunk)); // Разбиваем блок на строки
    return array_slice($output, -$lines); // Возвращаем последние строки
}

// Получаем последние 100 строк из файла логов
$logLines = tail($logFile);

// Подготавливаем данные для отображения в таблице
$tableData = [];
foreach ($logLines as $line) {
    // Разбиваем строку на дату и сообщение с помощью регулярного выражения
    if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.+)$/', $line, $matches)) {
        $tableData[] = [
            'date' => $matches[1], // Дата
            'message' => $matches[2] // Сообщение
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Последние 100 записей логов</h1>
    <table>
        <thead>
            <tr>
                <th>Дата</th>
                <th>Сообщение</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tableData as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['message']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
