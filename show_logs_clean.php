<?php
$logFile = '/var/log/crond.log';

function tail($file, $lines = 100) {
    $f = fopen($file, 'r');
    $buffer = 4096;
    fseek($f, -1, SEEK_END);
    if (fread($f, 1) != "\n") $lines -= 1;
    $output = '';
    $chunk = '';

    while (ftell($f) > 0 && $lines >= 0) {
        $seek = min(ftell($f), $buffer);
        fseek($f, -$seek, SEEK_CUR);
        $chunk = fread($f, $seek) . $chunk;
        fseek($f, -$seek, SEEK_CUR);
        $lines -= substr_count($chunk, "\n");
    }

    fclose($f);
    $output = explode("\n", trim($chunk));
    return array_slice($output, -$lines);
}

$logLines = tail($logFile);

$tableData = [];
foreach ($logLines as $line) {
    if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) (.+)$/', $line, $matches)) {
        $tableData[] = [
            'date' => $matches[1],
            'message' => $matches[2]
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
    <h1>Last 100 Log Entries</h1>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Message</th>
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
