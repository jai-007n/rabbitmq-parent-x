<?php

use App\Jobs\SendRabbitMessage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/send', function () {
    // dispatch(new \App\Jobs\SendRabbitMessage("Hello from Laravel!"))
    //     ->onConnection('rabbitmq') // sets $connection
    //     ->onQueue('default');      // sets $queue;
    $data = ['message' => 'Hello Project B','handler' => 'processMessage'];
    
    dispatch(new SendRabbitMessage($data));
    // SendRabbitMessage::dispatch($data)
    //     ->onConnection('rabbitmq')
    //     ->onQueue('default');

    return 'Message sent to RabbitMQ! from project A';
});
