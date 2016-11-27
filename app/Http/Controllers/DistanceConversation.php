<?php

namespace App\Http\Controllers;

use Mpociot\SlackBot\Conversation;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Question;
use Mpociot\SlackBot\Button;

use GuzzleHttp\Client;

class DistanceConversation extends Conversation
{
    protected $departing;
    protected $arriving;
    protected $travel;
    protected $travelTime;
    protected $travelDistance;
    
    public function askDeparting()
    {
        $this->ask('Where are you leaving from :round_pushpin: :arrow_right:?', function(Answer $answer) {
            $this->departing = $this->prepareLocation($answer->getText());
            $this->askArriving();
        });
    }
    
    public function askArriving()
    {
        $this->ask('Where are you going to :arrow_right: :round_pushpin:?', function(Answer $answer) {
            $this->arriving = $this->prepareLocation($answer->getText());
            $this->askTravelMode();
        });
    }
    
    public function askTravelMode()
    {
        $question = Question::create('How will you be travelling?')
            ->fallback('Unable to determine travel type')
            ->callbackId('assign_travel_mode')
            ->addButtons([
                Button::create('Driving')->value('driving'),
                Button::create('Walking')->value('walking'),
                Button::create('Cycling')->value('bicycling'),
                Button::create('Public transport')->value('transit'),
        ]);
            
         $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply())
            {
                $decision = $answer->getValue();
                if ($decision === 'driving')
                {
                    $this->travel = 'driving';
                    $this->adviseTravel();
                }
                elseif ($decision === 'walking')
                {
                    $this->travel = 'walking';
                    $this->adviseTravel();
                }
                elseif ($decision === 'bicycling')
                {
                    $this->travel = 'bicycling';
                    $this->adviseTravel();
                }
                elseif ($decision === 'transit')
                {
                    $this->travel = 'transit';
                    $this->adviseTravel();
                }
            }
        });
    }
    
    public function adviseTravel()
    {
        $travelData = $this->computeDistance();
        $this->say('The distance is ' . $this->travelDistance . ' and will take ' . $this->travelTime . '.');
    }
    
    public function computeDistance()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$this->departing.'&destinations='.$this->arriving.'&mode='.$this->travel.'&key=' . env('GOOGLE_API_KEY'));
        $response = json_decode($response->getBody());
        $this->travelDistance = $response->rows[0]->elements[0]->distance->text;
        $this->travelTime = $response->rows[0]->elements[0]->duration->text;
    }
    
    public function prepareLocation($location)
    {
        return str_replace(' ', '+', $location);
    }
    
    public function run()
    {
        // This will be called immediately
        $this->askDeparting();
    }
}