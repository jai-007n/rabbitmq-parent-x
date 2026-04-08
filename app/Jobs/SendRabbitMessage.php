<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendRabbitMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable,SerializesModels;

    public $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function handle()
    {
        Log::info("RabbitMQ message received from project A: " . $this->message['command']['message']);
    }
}
