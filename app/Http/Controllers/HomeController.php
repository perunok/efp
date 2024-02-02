<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    function index(Request $request)
    {
        $data = Tip::orderByDesc('id', 'desc')->get();
        return view('index', ['tips' => $data]);
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
}
