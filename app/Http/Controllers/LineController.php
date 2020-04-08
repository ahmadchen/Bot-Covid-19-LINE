<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Service\FollowService;
use App\Http\Service\GroupService;
use App\Http\Service\HelpService;
use App\Http\Service\MessageService;
use Illuminate\Support\Facades\Log;

class LineController extends Controller
{
    public function handler(
        Request $request,
        HelpService $helpService,
        MessageService $messageService,
        GroupService $groupService)
    {
        $bot = $request->bot;

        foreach ($request->events as $event)
        {
            switch ($event['type'])
            {
                case "follow":
                    $helpService->sendGreetings($bot, $event);
                break;
                case "leave":
                    $groupService->leftGroup($bot, $event);
                break;
                case "join":
                    $groupService->joinGroup($bot, $event);
                break;
                case "message":
                    $messageService->handleMessages($bot, $event);
                break;
            }
        }

        return response("UwU", 200);
    }
}
