<?php
error_reporting(E_ALL);

/* Получаем порт сервиса WWW. */
$service_port = '80';

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
echo $out;

$body = 'key1=value1&key2=value2';
$body_length = strlen($body);
$http_message =
"POST /print.php HTTP/1.1
Host: bowl.loc
Content-Type: application/x-www-form-urlencoded
Connection: close
Content-Length: $body_length

$body
";
echo "Отправляем сообщение:\n\n";
socket_write($socket, $http_message);

echo "Читаем ответ:\n\n";
while (true) {
    $bytes = socket_recv($socket, $out, 2048, MSG_DONTWAIT);
    echo $out;
}

?>