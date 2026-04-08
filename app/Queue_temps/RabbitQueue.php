<?php

namespace App\Queue;

// use App\Queue\Jobs\RabbitJob as JobContract;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Illuminate\Contracts\Queue\Job as JobContract;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class RabbitQueue extends Queue implements QueueContract
{
    protected AMQPStreamConnection $connection;
    protected $channel;
    protected $connectionName;
    protected $queue;

    public function __construct(AMQPStreamConnection $connection, string $queue = 'default', string $connectionName = 'rabbitmq')
    {
        $this->connection = $connection;
        $this->connectionName = $connectionName;
        $this->channel = $connection->channel();
        $this->queue = $queue;
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    public function size($queue = null): int
    {
        list($queue, $messageCount) = $this->channel->queue_declare($this->queue, true);
        return $messageCount;
    }


    // push a job
    public function push($job, $data = '', $queue = null)
    {

        $payload = $this->createPayload($job, $data);
        // 2. Decode and ensure connection and queue exist
        $decoded = json_decode($payload, true);
        $decoded['connection'] = $this->getConnectionName() ?? 'rabbitmq';
        $decoded['queue'] = $queue ?? $this->queue;

        // 3. Re-encode to JSON string
        $payload = json_encode($decoded);
        $this->pushRaw($payload, $queue);
    }


    // Laravel 13 requires pushRaw
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        if (is_array($payload)) {
            $payload = json_encode($payload);
        }
        $msg = new \PhpAmqpLib\Message\AMQPMessage(
            $payload,
            ['delivery_mode' => \PhpAmqpLib\Message\AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($msg, '', $queue ?? $this->queue);
    }

    // Laravel 13 requires later()
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->push($job, $data, $queue);
    }

    public function pop($queue = null): ?\App\Queue\Jobs\RabbitJob
    {
        $message = $this->channel->basic_get($this->queue);

        if (!$message) return null;

        return new \App\Queue\Jobs\RabbitJob($this->container, $message, $this->queue,$this->connectionName);
    }

    // Stub methods for Laravel 13 monitoring
    public function pendingSize($queue = null): int
    {
        return $this->size($queue);
    }

    public function delayedSize($queue = null): int
    {
        return 0;
    }

    public function reservedSize($queue = null): int
    {
        return 0;
    }

    public function creationTimeOfOldestPendingJob($queue = null): int
    {
        return time();
    }
}
