<?php

namespace App\Listener;

use Phalcon\Events\Event;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// define('CLIENT_ID', 'c47671c294d24e39a150a194bebec477');
// define('CLIENT_SECRET', 'dabfe39a47864267bca1e37b15ce3723');
// define('SCOPE', 'playlist-modify-public playlist-modify-private');
// define('REDIRECTION_URI', 'http://localhost:8080/index/callback');
class NotificationsListener
{
    public function beforeHandleRequest(Event $event, $application)
    {
       
    }


    public function beforeSend(Event $event, $component, $value)
    {
        $value=json_decode($value);
   
        // Make request to  Get New TOKEN 

        $url = "https://accounts.spotify.com/api/token";
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(CLIENT_ID . ':' . CLIENT_SECRET)
        ];
        $body = ["form_params" => ["grant_type" => "refresh_token","refresh_token"=>$value->refresh_token]];
        $client = new Client(
            ['headers' => $headers]
        );
        $response = $client->request('POST', $url,$body);
        $response = json_decode($response->getBody());
  
        return $response;
       
    }
}
