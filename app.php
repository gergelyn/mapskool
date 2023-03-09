<?php

require dirname(__FILE__) . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MapSkool\Socket;

$socket = new Socket();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $socket
        )
    ),
    12345
);


$lastUpdate = 0;

$file = "./db/iskolak.xlsx";

$server->loop->addPeriodicTimer(5, function() use ($socket, &$file, &$lastUpdate) {
    echo "lastUpdate: {$lastUpdate}\n";

    $fileModifyDate = filemtime($file);
    echo "fileModifyDate: {$fileModifyDate}\n";

    if ($fileModifyDate === false) {
        throw new Exception("Could not read last modificatiion time");
    }

    if ($fileModifyDate > $lastUpdate) {

        $lastUpdate = $fileModifyDate;

        $data = $socket->getExcelData($file);

        foreach ($socket->clients as $client) {
            $client->send(json_encode($data));
        }
    }

    // Clear file status cache to get the actual modification date
    clearstatcache();
});

$server->run();