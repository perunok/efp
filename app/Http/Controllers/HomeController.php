<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\Broadcast;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\Laravel\Facades\Telegram;
use Throwable;

class HomeController extends Controller
{
    function index(Request $request)
    {
        $data = Tip::orderByDesc('id', 'desc')->get();
        return view('index', ['tips' => $data]);
    }
    function makeBroadcast(Request $request)
    {
        //validation is must
        // save the broadcast

        $broadcast = new Broadcast();
        $uniqid = uniqid();
        $broadcast->pid = $uniqid;
        $broadcast->title = $request->title;
        $broadcast->description = $request->description;
        $broadcast->tags = $request->tags;
        $path = request()->file('attachment')->store('Broadcast Files', 'public');
        if ($request->has("reportable")) {
            $broadcast->reportable = true;
        } else {
            $broadcast->reportable = false;
        }
        $broadcast->attachment = $path;
        // broadcast if possible
        $subscribers = Subscriber::all();
        $text = "*" . $request->title . "* \n" . $request->tags . "\n\n" . $request->description . "\n";
        try {
            $keyboard = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Report This', 'callback_data' => "report-" . $uniqid]
                    ]
                ],
                'one_time_keyboard' => true,
                'resize' => true
            ]);

            $messageData = "";
            if ($request->has("reportable")) {
                foreach ($subscribers as $subscriber) {
                    $f = Telegram::sendPhoto([
                        'chat_id' => $subscriber->chat_id, 'photo' => InputFile::create(storage_path('app/public/' . $path)), 'caption' => $text, 'parse_mode' => "Markdown", 'reply_markup' => $keyboard
                    ]);
                    $messageData = $messageData . $f->message_id . "," . $f->chat->id . "-";
                }
            } else {
                foreach ($subscribers as $subscriber) {
                    $f = Telegram::sendPhoto([
                        'chat_id' => $subscriber->chat_id, 'photo' => InputFile::create(storage_path('app/public/' . $path)), 'caption' => $text, 'parse_mode' => "Markdown"
                    ]);
                    $messageData = $messageData . $f->chat->id . "," . $f->message_id . "-";
                }
            }

            $broadcast->message_data = rtrim($messageData, "-");
            $broadcast->save();
        } catch (\Throwable $th) {
            error_log($th);
        }


        return redirect("broadcasted_list");
    }

    function editBroadcast(Request $request)
    {
        //validation is must
        // save the broadcast

        $broadcast = [];
        $broadcast['title'] = $request->title;
        $broadcast['description'] =  $request->description;
        $broadcast['tags'] = $request->tags;
        if ($request->has("attachment")) {
            $path = request()->file('attachment')->store('Broadcast Files', 'public');
            $broadcast['attachment'] = $path;
        }
        if ($request->has("reportable")) {
            $broadcast['reportable'] = true;
        } else {
            $broadcast['reportable'] = false;
        }

        if (!$request->has("edit_on_telegram")) {
            Broadcast::where('pid', $request->id)->update($broadcast);
            return redirect("broadcasted_list");
        }

        $recieved = explode("-", Broadcast::where('pid', $request->id)->first()->message_data);
        foreach ($recieved as $value) {
            $message = explode(",", $value);
            // Telegram::editMessageMedia([
            //     'chat_id' => $message[0],
            //     'message_id' => $message[1],
            //     'media' => [
            //         'type' => 'photo',
            //         'media' => 'YOUR_PHOTO_FILE_ID'
            //     ]
            // ]);
            try {
                if ($request->has("reportable")) {
                    $keyboard = json_encode([
                        'inline_keyboard' => [
                            [
                                ['text' => 'Report This', 'callback_data' => "report-$request->id"]
                            ]
                        ],
                        'one_time_keyboard' => true,
                        'resize' => true
                    ]);
                    Telegram::editMessageCaption([
                        'chat_id' => $message[1],
                        'message_id' => $message[0],
                        'caption' => "*" . $request->title . "* \n" . $request->tags . "\n\n" . $request->description . "\n",
                        'parse_mode' => 'Markdown',
                        'reply_markup' => $keyboard
                    ]);
                } else {
                    Telegram::editMessageCaption([
                        'chat_id' => $message[1],
                        'message_id' => $message[0],
                        'caption' => "*" . $request->title . "* \n" . $request->tags . "\n\n" . $request->description . "\n",
                        'parse_mode' => 'Markdown'
                    ]);
                }
            } catch (Throwable $th) {
                error_log($th);
            }
        }
        Broadcast::where('pid', $request->id)->update($broadcast);
        return redirect("broadcasted_list");
    }
    function showBroadcasted()
    {
        $broadcasted = Broadcast::orderByDesc('id', 'desc')->get();
        return view('broadcasted_list', ['broadcasted' => $broadcasted]);
    }
    function rebroadcast(Request $request)
    {
        try {
            $id = $request->id;
            $broadcast = Broadcast::where('pid', $id)->first();
            $subscribers = Subscriber::all();
            $path = $broadcast->attachment;
            $text = "*" . $broadcast->title . "* \n" . $broadcast->tags . "\n\n" . $broadcast->description . "\n";
            $keyboard = json_encode([
                'inline_keyboard' => [
                    [
                        ['text' => 'Report This', 'callback_data' => "report-$id"]
                    ]
                ],
                'one_time_keyboard' => true,
                'resize' => true
            ]);

            $messageData = "";
            if ($broadcast->reportable) {
                foreach ($subscribers as $subscriber) {
                    $f = Telegram::sendPhoto([
                        'chat_id' => $subscriber->chat_id, 'photo' => InputFile::create(storage_path('app/public/' . $path)), 'caption' => $text, 'parse_mode' => "Markdown", 'reply_markup' => $keyboard
                    ]);
                    $messageData = $messageData . $f->chat->id . "," . $f->message_id . "-";
                }
            } else {
                foreach ($subscribers as $subscriber) {
                    $f = Telegram::sendPhoto([
                        'chat_id' => $subscriber->chat_id, 'photo' => InputFile::create(storage_path('app/public/' . $path)), 'caption' => $text, 'parse_mode' => "Markdown"
                    ]);
                    $messageData = $messageData . $f->chat->id . "," . $f->message_id . "-";
                }
            }
            Broadcast::where('pid', $id)->update(['message_data' => rtrim($messageData, "-")]);
            return json_encode(['status' => "ok"]);
        } catch (Throwable $th) {
            error_log($th);
            return json_encode(['status' => "error"]);
        }
    }
    function bookmark(Request $request)
    {
        try {
            $id = $request->id;
            $tip = Tip::find($id);
            $tip->marked ? $tip->marked = false : $tip->marked = true;
            $tip->save();
            return json_encode(['status' => "ok"]);
        } catch (Throwable $th) {
            error_log($th);
            return json_encode(['status' => "error"]);
        }
    }
    function getBroadcast(Request $request)
    {
        $broadcast = Broadcast::where('pid',$request->id)->first();
        return view('broadcast_detail', ['broadcast' => $broadcast]);
    }
}
