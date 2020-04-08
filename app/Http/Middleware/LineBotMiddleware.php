<?php

namespace App\Http\Middleware;

use Closure;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use Illuminate\Support\Facades\Log;

class LineBotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $channelSecret = env("CHANNEL_SECRET");
        $channelToken  = env("CHANNEL_TOKEN");
        $bot = new LINEBot(new CurlHTTPClient($channelToken), [
            'channelSecret' => $channelSecret
        ]);

        $signature = getallheaders()["X-Line-Signature"];

        try
        {
            $bot->parseEventRequest($request->getContent(), $signature);
            $request->merge([
                "bot" => $bot
            ]);

            return $next($request);
        }
        catch (InvalidSignatureException $e)
        {
            abort(400, "Invalid signature");
        }
        catch (InvalidEventRequestException $e)
        {
           abort(400, "Invalid event request");
        }
    }
}
