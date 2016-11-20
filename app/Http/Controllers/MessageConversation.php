<?php

namespace App\Http\Controllers;

use Mpociot\SlackBot\Conversation;
use Mpociot\SlackBot\Answer;
use Mpociot\SlackBot\Question;
use Mpociot\SlackBot\Button;

use Mail;
use Twilio;
use Validator;
use App\Mail\JeevesEmail;

class MessageConversation extends Conversation
{
    protected $emailRecipient;
    protected $emailMessage;
    protected $smsRecipient;
    protected $smsMessage;
    
    public function messageType()
    {
        $question = Question::create('What kind of message do you want to send?')
            ->fallback('Unable to determine message type')
            ->callbackId('create_database')
            ->addButtons([
                Button::create('Email')->value('email'),
                Button::create('SMS')->value('sms'),
            ]);
            
        $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply())
            {
                $decision = $answer->getValue();
                if ($decision === 'email')
                {
                    $this->askRecipient();
                }
                elseif ($decision === 'sms')
                {
                    $this->askPhoneNumber();
                }
            }
        });
    }
    
    public function askRecipient()
    {
        $this->ask('Who would you like to send the message to?', function(Answer $answer) {
            if ($this->isEmail($answer->getText()))
            {
                $this->emailRecipient = $this->getEmail($answer->getText());
                $this->say('That seems valid. I\'ll pop your message to: ' . $this->emailRecipient);
                $this->askEmailMessage();
            }
            else
            {
                $this->say('Are you sure that\'s a valid email address?');
            }
        });
    }
    
    public function askEmailMessage()
    {
        $this->ask('But before I do, what would you like to say?', function(Answer $answer) {
            $this->emailMessage = $answer->getText();
            $this->say('Rightio, I\'ll send ' . $this->emailRecipient . ' a message to say: ' . $this->emailMessage);
            $this->sendEmailMessage();
        });
    }
    
    public function sendEmailMessage()
    {
        Mail::to($this->emailRecipient)->send(new JeevesEmail($this->emailMessage));
    }
    
    public function isEmail($string)
    {
        $string = str_replace(['<mailto:', '>'], '', $string);
        $matches = explode('|', $string);
        if(filter_var($matches[0], FILTER_VALIDATE_EMAIL))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function getEmail($string)
    {
        $string = str_replace(['<mailto:', '>'], '', $string);
        $matches = explode('|', $string);
        return $matches[0];
    }
    
    public function askPhoneNumber()
    {
        $this->ask('What phone number would you like to send the message to?', function(Answer $answer) {
            if ($this->isPhoneNumber($answer->getText()))
            {
                $this->smsRecipient = phone($answer->getText(), 'AU');
                $this->say('Great. I\'ll pop your message to: ' . $this->smsRecipient);
                $this->askSmsMessage();
            }
            else
            {
                $this->say('You need to enter an Australian mobile number.');
            }
            
        });
    }
    
    public function isPhoneNumber($number)
    {
        $validator = Validator::make(['number' => $number], [
            'number' => 'phone:AU,mobile',
        ]);

        if ($validator->fails()) {
            return false;
        }
        else{
            return true;
        }
    }
    
    public function askSmsMessage()
    {
        $this->ask('But before I do, what would you like to say?', function(Answer $answer) {
            $this->smsMessage = $answer->getText();
            $this->say('Rightio, I\'ll send ' . $this->smsRecipient . ' a message to say: ' . $this->smsMessage);
            $this->sendSmsMessage();
        });   
    }
    
    public function sendSmsMessage()
    {
        Twilio::message($this->smsRecipient, $this->smsMessage);
    }
    
    public function run()
    {
        // This will be called immediately
        $this->messageType();
    }
}