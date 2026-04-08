<?php

namespace App\Queue\Connectors;

use Illuminate\Queue\Connectors\ConnectorInterface;
use App\Queue\RabbitQueue;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitConnector implements ConnectorInterface
{
    public function connect(array $config)
    {

    // Ensure defaults if env is missing
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5672;
        $user = $config['user'] ?? 'guest';
        $password = $config['password'] ?? 'guest';
        $queue = $config['queue'] ?? 'default';
        $connectionName = $config['name'] ?? 'rabbitmq';

        
        $connection = new AMQPStreamConnection($host, $port, $user, $password);
        return new RabbitQueue($connection, $queue,$connectionName);

    }
}
