<?php

namespace App\Telegram\Commands;

use App\Models\Subscriber;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command to get you started';

    public function handle()
    {
        try {
            $userId = Telegram::getWebhookUpdate()->message->from->id;
            $chatId = Telegram::getWebhookUpdate()->message->chat->id;
            $subscriber = new Subscriber();
            $subscriber->user_id = $userId;
            $subscriber->chat_id = $chatId;
            $subscriber->save();
            // error_log(Telegram::getWebhookUpdate());
        } catch (\Throwable $th) {
            error_log($th);
        }
        $keyboard = [
            ['Drop Tip'],
            ['Setup Profile'],
            ['Search Announcements'],
        ];
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
        $this->replyWithMessage([
            'text' => 'Hey! Welcome to our bot ! Variety of Announcements will be sent to you!', 'reply_markup' => $reply_markup, 'is_persistent' => 'true'
        ]);
    }
}
