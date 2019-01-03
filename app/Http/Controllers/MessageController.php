<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\PrivateMessageSent;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!empty($request->get('user')) && !empty($request->get('frend'))) {
            $user = $request->get('user');
            $frend = $request->get('frend');
            $messages = DB::table('messages')
                ->select('messages.*', 'users.name')->where(function ($query) use ($user, $frend) {
                    $query->where([['user_id', $user], ['to_user_id', $frend]])
                        ->orWhere([['user_id', $frend], ['to_user_id', $user]]);
                })->join('users', 'messages.user_id', '=', 'users.id')
                ->orderBy('created_at')
                ->get();
        } else {
            $messages = DB::table('messages')
                ->select('messages.*', 'users.name')
                ->whereNull('to_user_id')
                ->join('users', 'messages.user_id', '=', 'users.id')
                ->get();
        }
        return $messages;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'body' => 'required|string|max:191'
        ]);

        $newMessage = Message::create([
            'to_user_id' => $request['frend'],
            'user_id' => $request['user_id'],
            'body' => $request['body'],
        ]);

        if ($newMessage->to_user_id != null) {
            broadcast(new PrivateMessageSent($newMessage, auth()->user(), User::find($newMessage->to_user_id)));
        } else {
            broadcast(new MessageSent($newMessage, auth()->user()));
        }
        return $newMessage;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Message $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Message $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(Message $message)
    {
        //
    }
}