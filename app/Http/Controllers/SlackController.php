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
            
            $slackBot->hears('what are people in {country} watching?', function (Slackbot $bot, $country) use ($request) {
                $geocode = app('geocoder')->geocode($country)->get();
                $countryCode = $geocode->first()->getCountryCode();
                $videoList = \Youtube::getPopularVideos($countryCode);
                $bot->reply('https://www.youtube.com/watch?v=' . $videoList[array_rand($videoList)]->id);
            });
            
            $slackBot->hears('hit me with a meme', function (Slackbot $bot) {
                $client = new Client();
                $response = $client->request('GET', 'https://api.imgur.com/3/g/memes', ["headers" => ["Authorization" => 'Client-ID ' . env('IMGUR_API_KEY')]]);
                $response = json_decode($response->getBody());
                $randomMeme = $response->data[array_rand($response->data)];
                if ($randomMeme->is_album)
                {
                    $gallery = $client->request('GET', 'https://api.imgur.com/3/gallery/album/' . $randomMeme->id, ["headers" => ["Authorization" => 'Client-ID ' . env('IMGUR_API_KEY')]]);
                    $gallery = json_decode($gallery->getBody());
                    $randomImage = $gallery->data->images[array_rand($gallery->data->images)]->link;
                    $bot->reply($randomImage);
                }
                else 
                {
                    $bot->reply($randomMeme->link);
                }
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
