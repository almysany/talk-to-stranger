<?php

use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

/**
 * Created by PhpStorm.
 * User: luqman
 * Date: 3/31/17
 * Time: 6:35 AM
 */
class BotHelper
{
    public static function getMenu(){
        $options[] = new MessageTemplateActionBuilder('Start talking', 'start');
        $options[] = new MessageTemplateActionBuilder('Chat quota', 'chat_quota');
        $options[] = new MessageTemplateActionBuilder('End talk', 'end_talk');

        $button_template = new ButtonTemplateBuilder('Menu', 'What do you want to do?', null, $options);

        return new TemplateMessageBuilder('Open this chat to see the message', $button_template);
    }

    public static function greetMate($user_id){
        $access_token = getenv('CHANNEL_ACCESS_TOKEN');
        $secret = getenv('CHANNEL_SECRET');

        $http_client = new CurlHTTPClient($access_token);

        $bot = new LINEBot($http_client,['channelSecret' => $secret]);

        $bot->pushMessage($user_id, new TextMessageBuilder("Great, we found the other person!"));
        $bot->pushMessage($user_id, new TextMessageBuilder("You can start talking now. Your chat quota left is 30"));
        $bot->pushMessage($user_id, new TextMessageBuilder("Please only say something nice :)"));
        $bot->pushMessage($user_id, new TextMessageBuilder("End of Bot Response\n==========================="));

    }

    public static function notifyEndMate($bot, $user_id, $intentional = false){
        $mate = User::findOne(['user_id' => $user_id]);
        $mate->current_friend_id = '';
        $mate->status = User::STATUS_IDLE;
        $mate->save();
        if(! $intentional){
            $bot->pushMessage($user_id, new TextMessageBuilder("Bot: The person on the other end have run out of chat quota"));
        }else{
            $bot->pushMessage($user_id, new TextMessageBuilder("Bot: Ouch, the person on the other end stopped the session :("));
        }
        $bot->pushMessage($user_id, new TextMessageBuilder("===========================\nThis talking session is ended."));
        $bot->pushMessage($user_id, BotHelper::getMenu());
    }
}