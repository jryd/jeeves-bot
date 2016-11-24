<?php

namespace App\Http\Controllers;

use Mpociot\SlackBot\Conversation;
use Mpociot\SlackBot\Answer;

use GuzzleHttp\Client;
use Geocoder;

class WeatherConversation extends Conversation
{
    protected $location;
    protected $latitude;
    protected $longitude;
    
    public function weatherLocation()
    {
        $this->ask('Which city do you want the forecast for?', function(Answer $answer) {
            $this->say('Let me get that for you now, one moment...');
            $this->location = $answer->getText();
            $this->setCoordinates();
            $this->getWeather();
        });
    }
    
    public function getWeather()
    {
        $client = new Client();
        $weather_response = $client->request('GET', 'https://api.darksky.net/forecast/' . env('DARKSKY_KEY') . '/' . $this->latitude . ',' . $this->longitude . '?units=si');
        $weather = json_decode($weather_response->getBody());
        $this->say('Your current forecast is:', [
            'attachments' => json_encode([
                [
                    'title' =>  $weather->currently->summary,
                    'image_url' => 'https://laravel-alertr-jryd.c9users.io/images/skycons/'. $weather->currently->icon.'.gif',
                    'text' => $weather->hourly->summary,
                    'fields' => [
                        [
                            "title" => "Temperature",
                            "value" => round($weather->currently->temperature) . '°',
                            "short" => true
                        ],
                        [
                            "title" => "Humidity",
                            "value" => $weather->currently->humidity * 100 . '%',
                            "short" => true
                        ],
                        [
                            "title" => "High",
                            "value" => round($weather->daily->data[0]->temperatureMax) . '°',
                            "short" => true
                        ],
                        [
                            "title" => "Low",
                            "value" => round($weather->daily->data[0]->temperatureMin) . ' °',
                            "short" => true
                        ]
                    ]
                ]
            ])
        ]);
    }
    
    public function setCoordinates()
    {
        $geocode = app('geocoder')->geocode($this->location)->get();
        $this->latitude = $geocode->first()->getLatitude();
        $this->longitude = $geocode->first()->getLongitude();
    }
    
    public function run()
    {
        // This will be called immediately
        $this->weatherLocation();
    }
}