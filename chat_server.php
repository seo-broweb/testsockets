#!/usr/local/bin/php -q
<?php
error_reporting(E_ALL);

/* Позволяет скрипту ожидать соединения бесконечно. */
set_time_limit(0);

/* Включает скрытое очищение вывода так, что мы видим данные
 * как только они появляются. */
ob_implicit_flush();

$address = '127.0.0.1';
$port = 10000;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "Не удалось выполнить socket_create(): причина: " . socket_strerror(socket_last_error()) . "\n";
    die();
}

if (socket_bind($sock, $address, $port) === false) {
    echo "Не удалось выполнить socket_bind(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
    die();
}

if (socket_listen($sock, 5) === false) {
    echo "Не удалось выполнить socket_listen(): причина: " . socket_strerror(socket_last_error($sock)) . "\n";
    die();
}

if (!socket_set_nonblock($sock)) {
    echo "Не удалось выполнить socket_set_nonblock() \n";
    die();
}

echo "Слушаем порт 10000\n";
$msgsocks = [];

do {

    if ($clientMsgsock = socket_accept($sock)) {
        if (!socket_set_nonblock($clientMsgsock)) {
            echo "Не удалось выполнить socket_set_nonblock() \n";
            die();
        }
        /* Отправляем инструкции. */
        $msg = "\nДобро пожаловать на тестовый сервер PHP. \n" .
            "Чтобы отключиться, наберите 'выход'. Чтобы выключить сервер, наберите 'выключение'.\n";
        socket_write($clientMsgsock, $msg, strlen($msg));
        $msgsocks[] = $clientMsgsock;
    }

    foreach ($msgsocks as $key => $msgsock) {
//        if (false === ($buf = socket_read($msgsock, 2048, PHP_NORMAL_READ))) {
//            echo "Не удалось выполнить socket_read(): причина: " . socket_strerror(socket_last_error($msgsock)) . "\n";
//            unset($msgsocks[$key]);
//            continue;
//        }
//        $buf = socket_read($msgsock, 2048, PHP_NORMAL_READ);
        $bytes = socket_recv($msgsock, $buf, 2048, MSG_DONTWAIT);

        if ($buf === false) {
            continue;
        }

        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'stop') {
            unset($msgsocks[$key]);
            socket_close($msgsock);
            continue;
        }

        if ($buf == 'die') {
            socket_close($msgsock);
            socket_close($sock);
            exit();
        }
        $talkback = "PHP: Вы сказали '$buf'.\n";
        echo "$buf\n";
        foreach ($msgsocks as $key2 => $msgsock2) {
            if ($key2 == $key) {
                continue;
            }
            socket_write($msgsock2, $talkback, strlen($talkback));
        }
    }
} while (true);

socket_close($sock);
?>