<?php

namespace Ducnm\NotiStock\Helper;

use Illuminate\Support\Facades\Http;

class LogApp
{
    public static function sendTele($message)
    {
        $bot = 'bot' . env('NOTI_STOCK_TELEGRAM_BOT_TOKEN');
        $chatId = env('NOTI_STOCK_TELEGRAM_CHAT_ID');
        
        $url = "https://api.telegram.org/$bot/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];
        $response = Http::post($url, $data);
        return $response->json();
    }
}