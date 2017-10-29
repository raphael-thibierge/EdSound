<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 22/10/2017
 * Time: 12:08
 */

namespace App\Http\Services;


use App\User;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Facebook\FacebookDriver;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

class BotmanService
{

    public static function instance(){
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
        return BotManFactory::create($config);
    }


}