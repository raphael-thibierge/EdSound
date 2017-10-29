<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 29/10/2017
 * Time: 15:49
 */

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Http\Services\BotmanService;
use BotMan\Drivers\Facebook\FacebookDriver;

class MessengerChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // get notification content
        $message = $notification->toMessenger($notifiable);

        // get user's sender id
        $senderId = $notifiable->messenger_sender_id;

        // send notification with messenger
        BotmanService::instance()->say($message, $senderId, FacebookDriver::class);

    }
}