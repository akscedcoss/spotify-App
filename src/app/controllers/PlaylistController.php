<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

define('CLIENT_ID', 'c47671c294d24e39a150a194bebec477');
define('CLIENT_SECRET', 'dabfe39a47864267bca1e37b15ce3723');
define('SCOPE', 'playlist-modify-public playlist-modify-private');
define('REDIRECTION_URI', 'http://localhost:8080/index/callback');
class PlaylistController extends Controller
{
    public function   getJsonResponseUsingHeaders($url, $headers, $method = 'GET')
    {
        try {

            $client = new Client(
                ['headers' => $headers]

            );

            $response = $client->request($method, $url);
            $response = json_decode($response->getBody());
            return $response;
        } catch (RequestException $e) {

            if ($e->getCode() == '401') {
                echo "Token EXpiredd..... Please re Login";
            }
            echo $e->getMessage();
        }
    }
    //  Display ALl Playlist
    public function indexAction()
    {

        $loginCookie = $this->cookies->get("login-action");
        // Get the cookie's value 
        $value = $loginCookie->getValue();
        $value = json_decode($value);
        // Setting Up url 
        $url = 'https://api.spotify.com/v1/me';
        $header = [
            'Authorization' => 'Bearer ' . $value->access_token,
        ];
        $id = $this->getJsonResponseUsingHeaders($url, $header, $method = 'GET')->id;
        $url = 'https://api.spotify.com/v1/users/' . $id . '/playlists';
        $this->view->res = $this->getJsonResponseUsingHeaders($url, $header, $method = 'GET');
        if ($this->request->isPost()) {
            print_r($this->request->getPost());
            die;
        }
    }
    //  Add To  Playlist
    public function previewPlaylistAction($id)
    {
        $loginCookie = $this->cookies->get("login-action");
        // Get the cookie's value 
        $value = $loginCookie->getValue();
        $value = json_decode($value);
        // echo $id;
        $url = 'https://api.spotify.com/v1/playlists/' . $id . '/tracks';
        $header = [
            'Authorization' => 'Bearer ' . $value->access_token,
        ];
        // print_r(json_encode($this->getJsonResponseUsingHeaders($url, $header, $method = 'GET')));
        $this->view->res=$this->getJsonResponseUsingHeaders($url, $header, $method = 'GET');
     
    }
    //  Add To  Playlist
    public function addToPlaylistAction($id)
    {
        echo $id;
        $this->view->id = $id;
        $loginCookie = $this->cookies->get("login-action");
        // Get the cookie's value 
        $value = $loginCookie->getValue();
        $value = json_decode($value);
        // get List of playlist
        $url = 'https://api.spotify.com/v1/me';
        $header = [
            'Authorization' => 'Bearer ' . $value->access_token,
        ];
        $id = $this->getJsonResponseUsingHeaders($url, $header, $method = 'GET')->id;
        $url = 'https://api.spotify.com/v1/users/' . $id . '/playlists';
        $this->view->res = $this->getJsonResponseUsingHeaders($url, $header, $method = 'GET');
    }
    public function addToPlaylistCompleteAction($playlistid, $trackId)
    {
        $loginCookie = $this->cookies->get("login-action");
        // Get the cookie's value 
        $value = $loginCookie->getValue();
        $value = json_decode($value);
        //  Url 
        $url = "https://api.spotify.com/v1/playlists/$playlistid/tracks";
        $header = [
            'Authorization' => 'Bearer ' . $value->access_token,
        ];
        $body = ["uris" => ["spotify:track:" . $trackId]];

        $client = new Client(
            ['headers' => $header, 'body' => json_encode($body)]

        );
        $response = $client->request('POST', $url);
        $this->response->redirect('/playlist');
    }
}
