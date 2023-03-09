<?php

namespace MapSkool;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Shuchkin\SimpleXLSX;

class Socket implements MessageComponentInterface {
    public function __construct() {
        echo "Update\n";
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        $clientCnt = count($this->clients);

        echo "New Connection! ({$conn->resourceId})\n";
        echo "Client count: {$clientCnt}\n";

        $data = $this->getExcelData("./db/iskolak.xlsx");
        $conn->send(json_encode($data));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ( $this->clients as $client ) {

            if ( $from->resourceId == $client->resourceId ) {
                continue;
            }

            $client->send("Client $from->resourceId said $msg");
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        $clientCnt = count($this->clients);
        echo "Client count: {$clientCnt}\n";
        echo "Client disconnected! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error with ClientID: ({$conn->resourceId})\n";
        echo "Disconnecting...";
        $this->onClose($conn);
        echo $e->getMessage();
    }

    public function getExcelData(string $file) {
        if ($xlsx = SimpleXLSX::parse($file)) {
            $header = [];
            $data = [];
            foreach ($xlsx->rows() as $k => $r) {
                if ($k === 0) {
                    $header = $r;
                    continue;
                }

                $data[] = array_combine($header, $r);
            }

            return $data;
        } else {
            echo SimpleXLSX::parseError();
        }
    }
}