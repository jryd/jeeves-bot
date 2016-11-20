<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mpociot\SlackBot\Button;
use Mpociot\SlackBot\Question;
use Mpociot\SlackBot\SlackBot;

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
            
            $slackBot->hears('buttons', function (SlackBot $bot) use ($request) {
                $bot->reply(Question::create('Here are some buttons!')->addButton(Button::create('Hello World')->value('hello world'))->callbackId("helloWorldButton"));
            });
            
            $slackBot->hears('msg', function (SlackBot $bot) use ($request) {

              $bot->startConversation(new MessageConversation());
            
            });
            
            $slackBot->fallback(function(SlackBot $bot) {
                $bot->respond("Lol wut? I don\'t understand.");
            });
            
            $slackBot->listen();
            
        } else {
            return null;
        }
    }
}
