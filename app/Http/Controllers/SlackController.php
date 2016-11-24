<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpociot\SlackBot\Button;
use Mpociot\SlackBot\Question;
use Mpociot\SlackBot\SlackBot;

use GuzzleHttp\Client;
use Youtube;

class SlackController extends Controller
{
    public function slack(Request $request)
    {
        $payload = $request->json();

        if ($payload->get('type') === 'url_verification') {
            return $payload->get('challenge');
        }

        $slackBot = app('slackbot');

        if (!$slackBot->isBot()) {
            
            $slackBot->hears('ping', function (SlackBot $bot) use ($request) {
                $bot->reply('Pong :table_tennis_paddle_and_ball:');
            });
            
            $slackBot->hears('hello', function (SlackBot $bot) use ($request) {
                $bot->reply('World! :earth_asia:');
            });
            
            $slackBot->hears('msg', function (SlackBot $bot) use ($request) {
                $bot->startConversation(new MessageConversation());
            });
            
            $slackBot->hears('weather', function (SlackBot $bot) use ($request) {
                $bot->startConversation(new WeatherConversation());
            });
            
            $slackBot->hears('youtube {query}', function (SlackBot $bot, $query) use ($request) {
                $videoList = Youtube::searchVideos($query);
                $bot->reply('https://www.youtube.com/watch?v=' . $videoList[0]->id->videoId);
            });
            
            /* Currently not working - spams channel - bug logged
            $slackBot->fallback(function(SlackBot $bot) {
                $bot->reply('Lol wut? I don\'t understand.');
            });*/
            
            $slackBot->listen();
            
        } else {
            return null;
        }
    }
}
