<?php

namespace Chat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{

    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        
        $conn->resourceId = rand(1, 100);

        echo "Nova conexão ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // A mensagem chega com o ID do usuário que deve receber a mesma separada por :
        $msg = explode(':', $msg);
        
        // o ID de quem deve receber
        $to = $msg[0];
        
        // a mensagem enviada
        $message = $msg[1];
        
        foreach ($this->clients as $client) {
            
            // Obviamente que neste momento deve ser realizada a verificação se a mensagem faz realmente parte
            // de uma conversa válida
            if ($from !== $client && $client->resourceId == $to) {
                echo "Conexão #" . $from->resourceId . " enviando uma mensagem para a conexão #" . $to . "\n";
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        
        echo "Conexão {$conn->resourceId} se desconectou\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Ocorreu um erro: {$e->getMessage()}\n";

        $conn->close();
    }

}
