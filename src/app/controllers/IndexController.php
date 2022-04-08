<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;


class IndexController extends Controller
{

    
    public function getJsonResponseUsingGuzzle($url)
    {
        $client = new Client();

        $response = $client->request('GET', $url);

        $response = json_decode($response->getBody()->getContents());
        return $response;
    }
    public function indexAction()
    {

        
    }
  
}
