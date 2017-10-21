<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Facebook\FacebookDriver;
use Illuminate\Http\Request;

class BotManController extends Controller
{
    public function handle(Request $request){

        // Use Facebook messenger driver
        DriverManager::loadDriver(FacebookDriver::class);

        // Define botman config
        $config = [
            'facebook' => [
                'token' => env('FACEBOOK_TOKEN'),
                'app_secret' => env('FACEBOOK_APP_SECRET'),
                'verification' => env('FACEBOOK_VERIFICATION'),
            ]
        ];

        // Create BotMan instance
        $botman = BotManFactory::create($config);

        // Default message
        $botman->hears('Hi', function (BotMan $bot){
            $bot->types();
            $bot->reply('Hello !');
        });

        // start listening
        $botman->listen();
    }
}
