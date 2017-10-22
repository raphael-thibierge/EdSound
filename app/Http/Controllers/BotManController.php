<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Middleware\ApiAi;
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

        // Dialogflow API
        $dialogflow = ApiAi::create(env('DIALOGFLOW_API_KEY'))->listenForAction();

        // Apply global "received" middleware
        $botman->middleware->received($dialogflow);

        $botman->hears('input.welcome', function (BotMan $bot) {
            // The incoming message matched the "input.welcome" on Dialoglfow.com
            // Retrieve API.ai information:
            $extras = $bot->getMessage()->getExtras();

            // response content
            $apiReply = $extras['apiReply'];
            $apiAction = $extras['apiAction'];
            $apiIntent = $extras['apiIntent'];
            $apiParameters = $extras['apiParameters'];

            $bot->reply($apiReply);
        })->middleware($dialogflow);

        $botman->hears('test', function (BotMan $bot) {
            $bot->reply('Test validé !');
        });

        // default response
        $botman->fallback(function (BotMan $bot){
            $bot->reply("I didn't get that..");
        });

        // start listening
        $botman->listen();
    }
}