<?php

namespace App\Queue\Jobs;

use Illuminate\Queue\Jobs\Job;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Contracts\Queue\Job as JobContract;


class RabbitJob extends Job implements JobContract
{
    protected $message;
    protected $queue;
    protected $queueConnection;
    protected $connectionName;

    public function __construct($container, AMQPMessage $message, $queue, $connection = 'rabbitmq')
    {
        $this->container = $container;
        $this->message = $message;
        $this->queue = $queue;
        $this->queueConnection = $queue;
        $this->connectionName = $connection;
    }

    public function payload(): array
    {
        return json_decode($this->message->getBody(), true) ?: [];
    }

    public function fire(): void
    {
        // Wrap in try-catch to let Laravel handle failures
        try {
            //throw new \Exception("You must be at least 18 years old.");
            $payload = $this->payload();
            $job = unserialize($payload['data']['command']);
            $job->handle();
            $this->delete(); // remove from queue if successful
        } catch (\Exception $e) {
            $this->fail($e); // <— this triggers entry in failed_jobs table
            throw $e;        // optionally rethrow
        }
    }

    public function release($delay = 0): void
    {
        // Normally requeue logic here
        $this->delete();
    }

    public function delete(): void
    {
        $this->message->ack();
    }



    public function attempts(): int
    {
        return 1; // Or use a header property
    }

    public function getRawBody(): string
    {
        return $this->message->body;
    }

    public function getQueue(): string
    {
        return $this->queue ?? 'default';
    }

    // public function getConnectionName(): string
    // {
    //     return $this->connectionName ?? 'rabbitmq';
    // }

    public function getQueueConnectionName()
    {
        return $this->queueConnection; // rabbitmq for pushing/popping
    }

    public function getConnectionName()
    {
        return config('queue.failed.connection', config('database.default')); // for failed_jobs
    }

    public function getJobId(): string
    {
        return (string) $this->message->get('delivery_tag');
    }

    public function getName(): string
    {
        return $this->payload()['displayName'] ?? 'UnknownJob';
    }
}
