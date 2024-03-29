<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Telegram\Bot\Keyboard\Keyboard;
use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotController extends Controller
{
    // Todo start command should be mandatory! it's not now. 

    function index()
    {
        $response = Telegram::getWebhookInfo();
        return $response;
    }
    function setWebhook()
    {
        $stat = Telegram::setWebhook(['url' => env('WEBHOOK_URL') . "/bot"]);
        return $stat;
    }
    function webhookUpdate()
    {
        $update = Telegram::commandsHandler(true);
        // leave the command handling to handlers. return if it is a command
        if (isset($update->message->entities)) {
            return;
        }
        // handle if it is callback querry ie. response to a post
        if (isset($update->callback_query)) {
            $cid = $update->callback_query->message->chat->id;
            try {
                $postId = explode("-", $update->callback_query->message->reply_markup->inline_keyboard[0][0]['callback_data'])[1];
                $value = ['expected' => 'tip_evidence', 'isReplay' => true, 'post_id' => $postId];
                Cache::forget($cid);
                Cache::put($cid, $value);
                $reply_markup = Keyboard::make([
                    'keyboard' => [["Abort"]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => false
                ]);
                Telegram::sendMessage([
                    'chat_id' => $cid, 'text' => "Ok! describe what you got as clear as possible!", 'reply_markup' => $reply_markup, 'is_persistent' => 'true'
                ]);
            } catch (\Throwable $th) {
                error_log($th);
            }
            // error_log($update->callback_query->message." - ".$update->callback_query->data);
            return;
        }

        $chat_id = $update->message->from->id;
        try {
            // abort unconditionaly
            if ($update->message->text == "Abort") {
                $this->handleKey($update);
                return;
            }
            // handle reserved keys 
            $reserved_keys = ['Drop Tip', 'Profile', 'Back To Home', 'Start Broadcasts', 'Stop Broadcasts', 'Abort'];
            if (!Cache::has($chat_id) && in_array($update->message->text, $reserved_keys, $update->getMessge())) {
                $this->handleKey($update);
                return;
            }
            // accept only the expected values
            if (Cache::has($chat_id)) {
                $this->acceptOnlyExpected($update);
                return;
            } else {
                // error_log($update);

                $this->replay($update, "please only use the provided buttons!", [['Drop Tip'], ['Profile']]);
            }
        } catch (\Throwable $th) {
            error_log($th);
        }
        return;
    }





    // =============================================================================================================

    function handleKey($update)
    {
        $key = $update->message->text;
        $chat_id = $update->message->from->id;
        switch ($key) {
            case 'Drop Tip':
                // accept the tip
                $value = ['expected' => 'tip_evidence'];
                Cache::put($chat_id, $value);
                $this->replay($update, "Ok! What's the tip?", [["Abort"]]);
                return;
            case 'Profile':
                $subscriber = Subscriber::where('chat_id', $chat_id)->first();
                if ($subscriber->gets_broadcast) {
                    $this->replay($update, " 🛠 Profile Settings 🔧 \n 📢 Gets broadcasts - ✅ Yes  \n ", [['Stop Broadcasts'], ['Back To Home']]);
                } else {
                    $this->replay($update, " 🛠 Profile Settings 🔧 \n 📢 Gets broadcasts - ❌ No  \n ", [['Start Broadcasts'], ['Back To Home']]);
                }

                return;
            case 'Start Broadcasts':
                $subscriber = Subscriber::where('chat_id', $chat_id)->first();
                $subscriber->gets_broadcast = 1;
                $subscriber->save();
                $this->replay($update, "Okay! You wil get future broadcasts ▶️", [['Stop Broadcasts'], ['Back To Home']]);
                return;
            case 'Stop Broadcasts':
                $subscriber = Subscriber::where('chat_id', $chat_id)->first();
                $subscriber->gets_broadcast = 0;
                $subscriber->save();
                $this->replay($update, "Okay! You won't get broadcasts anymore 🛑", [['Start Broadcasts'], ['Back To Home']]);
                return;
            case 'Back To Home':
                $this->replay($update, "🏠 Returned Home!", [['Drop Tip'], ['Profile']]);
                return;
            case 'Abort':
                Cache::forget($chat_id);
                $this->replay($update, "Canceled!", [['Drop Tip'], ['Profile']]);
                return;
            default:
                Telegram::sendMessage([
                    'chat_id' => $chat_id, 'text' => 'No Session Found or it Expired! Please Only Choose from The Menu'
                ]);
                return;
        }
    }

    function acceptOnlyExpected($update): void
    {
        $chat_id = $update->message->from->id;
        $data = Cache::get($chat_id);
        if ($data['expected'] == 'tip_evidence') {
            if (isset($update->message->text)) {
                // handle it for it's text
                if (isset($data['isReplay'])) {
                    // it's a replay to already posted broadcast
                    $value = ['expected' => 'tip_evicence_approval', 'text' => $update->message->text, 'isReplay' => true, 'post_id' => $data['post_id']];
                    Cache::forget($chat_id);
                    Cache::put($chat_id, $value, 600);
                    $this->replay($update, "do you have any photo evidence you can send me?!", [['Yes'], ['No'], ['Abort']]);
                    return;
                }
                $value = ['expected' => 'tip_evicence_approval', 'text' => $update->message->text];
                Cache::forget($chat_id);
                Cache::put($chat_id, $value, 600);
                $this->replay($update, "do you have any photo evidence you can send me?!", [['Yes'], ['No'], ['Abort']]);
                return;
            }
            $this->replay($update, "please finish your conversation or Cancel!", [['Abort']]);
            return;
        } elseif ($data['expected'] == 'tip_evicence_approval') {
            if (isset($update->message->text)) {
                // handle the question of evidence
                if ($update->message->text == "Yes") {
                    // wait for user to upload photo
                    if (isset($data['isReplay'])) {
                        $value = ['expected' => 'evidence_photo', 'text' => $data['text'], 'isReplay' => true, 'post_id' => $data['post_id']];
                        Cache::forget($chat_id);
                        Cache::put($chat_id, $value, 600);
                        $this->replay($update, "Please send me a picture!", [['Abort']]);
                        return;
                    }
                    $value = ['expected' => 'evidence_photo', 'text' => $data['text']];
                    Cache::forget($chat_id);
                    Cache::put($chat_id, $value, 600);
                    $this->replay($update, "Please send me a picture!", [['Abort']]);
                    return;
                } elseif ($update->message->text == "No") {
                    // get the tip and finish
                    if (isset($data['isReplay'])) {
                        $postId = $data["post_id"];
                        $this->replay($update, "Thank you for reporting to the commision!", [['Drop Tip'], ['Profile']]);
                        $tip = new Tip();
                        $p = str_replace("'", "&prime;", Cache::get($chat_id)['text']);
                        $tip->text = $p;
                        $tip->for = $postId;
                        $tip->save();
                        Cache::forget($chat_id);
                        return;
                    }
                    $this->replay($update, "Thank you for reporting to the commision!", [['Drop Tip'], ['Profile']]);
                    $tip = new Tip();
                    $p = str_replace("'", "&prime;", Cache::get($chat_id)['text']);
                    $tip->text = $p;
                    $tip->save();
                    Cache::forget($chat_id);
                    return;
                }
                $this->replay($update, "please finish your conversation or Cancel!", [['Abort']]);
                return;
            }
            $this->replay($update, "please finish your conversation or Cancel!", [['Abort']]);
            return;
        } elseif ($data['expected'] == 'evidence_photo') {
            if (isset($update->message->photo)) {
                // save the file , record into database and finish
                if (isset($data['isReplay'])) {
                    $postId = $data['post_id'];
                    $this->replay($update, "We got Your Tip! Thank you for reporting to the commision!", [['Drop Tip'], ['Profile']]);
                    $file = Telegram::getFile(['file_id' => $update->message->photo->last()->file_id]);
                    $fileName = Telegram::downloadFile($file, "Tip Photos");
                    $tip = new Tip();
                    $p1 = str_replace("'", " &prime;", $data['text']);
                    $p2 = str_replace(["\r", "\n"], " <br /> ", $p1);
                    $tip->text = $p2;
                    $tip->for = $postId;
                    $tip->attachment = $fileName;
                    $tip->save();
                    Cache::forget($chat_id);
                    return;
                }
                $this->replay($update, "We got Your Tip! Thank you for reporting to the commision!", [['Drop Tip'], ['Profile']]);
                $file = Telegram::getFile(['file_id' => $update->message->photo->last()->file_id]);
                $fileName = Telegram::downloadFile($file, "Tip Photos");
                $tip = new Tip();
                $p1 = str_replace("'", "&prime;", $data['text']);
                $p2 = str_replace(["\r", "\n"], " <br /> ", $p1);
                $tip->text = $p2;
                $tip->attachment = $fileName;
                $tip->save();
                Cache::forget($chat_id);
                return;
            }
            $this->replay($update, "please finish uploading or Cancel!", [['Abort']]);
            return;
        }
        $this->replay($update, "please finish your conversation or Cancel!", [['Abort']]);
        return;
    }




    function replay($update, $text, array $buttons): void
    {
        $chat_id = $update->message->from->id;
        $keyboard = $buttons;
        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
        Telegram::sendMessage([
            'chat_id' => $chat_id, 'text' => $text, 'reply_markup' => $reply_markup, 'is_persistent' => 'true'
        ]);
        return;
    }
}
