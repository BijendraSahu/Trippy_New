<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use LaravelFCM\Facades\FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;

class UserNotifications extends Model
{
    protected $table = 'notifications';
    public $timestamps = false;


    public static function getNotification($token, $title, $message, $notify_data)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($message)->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['data' => json_encode($notify_data)]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

//        $token = "denG_Y3xlKw:APA91bFg0PVxYaI-knF2q-X79Lbz5xRP_a0BhPOQyfSmbW7bYmPQuZyfPUnArMpmYnM8K6WbUKt-iKT4Owjlx31XNH4fMC1ioBsqTtcI5_rEfJJc2ImvvWOBEG_ejPZLdfYzdyZ9eDGx";

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);


    }
}
