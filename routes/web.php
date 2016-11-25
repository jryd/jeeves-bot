<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::post('/slack', 'SlackController@slack');

Route::get('/login/slack', function(){
    return Socialite::with('slack')
        ->scopes(['bot'])
        ->redirect();
});
Route::get('/connect/slack', function(\GuzzleHttp\Client $httpClient){
    $response = $httpClient->post('https://slack.com/api/oauth.access', [
        'headers' => ['Accept' => 'application/json'],
        'form_params' => [
            'client_id' => env('SLACK_KEY'),
            'client_secret' => env('SLACK_SECRET'),
            'code' => $_GET['code'],
            'redirect_uri' => env('SLACK_REDIRECT_URI'),
        ]
    ]);
    $bot_token = json_decode($response->getBody())->bot->bot_access_token;
    echo "Your Bot Token is: ". $bot_token. " place it inside your .env as SLACK_TOKEN";
});

Route::get('/test', function() {
        $client = new GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.imgur.com/3/g/memes', ["headers" => ["Authorization" => 'Client-ID 0f060ab4011d657']]);
        $response = json_decode($response->getBody());
        $randomMeme = $response->data[array_rand($response->data)];
        if ($randomMeme->is_album)
        {
            $gallery = $client->request('GET', 'https://api.imgur.com/3/gallery/album/' . $randomMeme->id, ["headers" => ["Authorization" => 'Client-ID 0f060ab4011d657']]);
            $gallery = json_decode($gallery->getBody());
            $randomImage = $gallery->data->images[array_rand($gallery->data->images)]->link;
            dd('Album: ' . $randomImage);
        }
        else 
        {
            dd('Image: ' . $randomMeme->link);
        }
        dd('Image: ' . $randomMeme);
});