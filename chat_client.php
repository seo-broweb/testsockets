<?php
error_reporting(E_ALL);

echo "<h2>Соединение TCP/IP</h2>\n";

/* Получаем порт сервиса WWW. */
$service_port = '10000';

/* Получаем IP-адрес целевого хоста. */
$address = '127.0.0.1';

/* Создаём сокет TCP/IP. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
} else {
    echo "OK.\n";
}

echo "Пытаемся соединиться с '$address' на порту '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "Не удалось выполнить socket_connect().\nПричина: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
} else {
    echo "OK.\n";
}

echo "Читаем ответ:\n\n";
$bytes = socket_recv($socket, $out, 2048, MSG_DONTWAIT);
stream_set_blocking(STDIN, false);

while (true) {
    $bytes = socket_recv($socket, $out, 2048, MSG_DONTWAIT);
    if ($out) {
        echo $out;
    }

    $line = trim(fgets(STDIN)); // читает одну строку из STDIN
    if ($line === '') {
        continue;
    }

    $result = socket_write($socket, $line, strlen($line));
    if ($result === false) {
        die();
    }

    if ($line == 'stop' || $line == 'die') {
        socket_close($socket);
        die();
    }
}
?>