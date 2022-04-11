<?php

use Phalcon\Mvc\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

define('CLIENT_ID', 'c47671c294d24e39a150a194bebec477');
define('CLIENT_SECRET', 'dabfe39a47864267bca1e37b15ce3723');
define('SCOPE', ' user-read-email playlist-read-private playlist-read-collaborative playlist-modify-public playlist-modify-private');
define('REDIRECTION_URI', 'http://localhost:8080/index/callback');
class IndexController extends Controller
{

    public function build_http_query($query)
    {

        $query_array = array();

        foreach ($query as $key => $key_value) {

            $query_array[] = urlencode($key) . '=' . urlencode($key_value);
        }
        return implode('&', $query_array);
    }

    public function getJsonResponseUsingHeadersAndQuery($url, $headers, $query, $method = 'POST')
    {
        try {

            $client = new Client(
                ['headers' => $headers]
            );
       
            $response = $client->request($method, $url, $query);
            $response = json_decode($response->getBody());
            return $response;
        } catch (RequestException $e) {
            echo $e->getMessage();
        }
    }
    public function   getJsonResponseUsingHeaders($url, $headers,$method = 'GET')
    {
        try {

            $client = new Client(
                ['headers' => $headers]
                
            );
       
            $response = $client->request($method, $url);
            $response = json_decode($response->getBody());
            return $response;
        } catch (RequestException $e) {
    
              if($e->getCode()=='401')
              {
                  echo "Token EXpiredd..... Please re Login";
                  
              }
            echo $e->getMessage();
        }

    }
    public function indexAction()
    {
        // Check if cookie avialble
        if ($this->cookies->has("login-action")) {
            $loginCookie = $this->cookies->get("login-action");
            // Get the cookie's value 
            $value = $loginCookie->getValue();
            $value = json_decode($value);
            //Check Post Request 
            if ($this->request->isPost()) {
                // echo $value;
                // echo "<pre>";
                // print_r($this->request->getPost());
           
                $allkeys = array_keys($this->request->getPost());

                $allkeys = array_diff($allkeys, ["search"]);

                // Search params in Api 
                $type = implode(",", $allkeys);
      
                $q = $this->request->getPost('search');
                $params = [
                    "q" =>  $q,
                    "type" => $type
                ];
                $url = 'https://api.spotify.com/v1/search?q='.$params['q'].'&type='.$params['type'];
                $header = [
                    'Authorization' => 'Bearer ' .$value->access_token,
                ];
                           
                $res= $this->getJsonResponseUsingHeaders($url, $header, $method = 'GET');
                $this->view->res= $res;
                // print_r(json_encode($res->tracks));
                // die;
            }
        } else {
            $params = [
                "response_type" => 'code',
                "client_id" => CLIENT_ID,
                "scope" => SCOPE,
                "redirect_uri" => REDIRECTION_URI,
            ];

            $this->view->link = 'https://accounts.spotify.com/authorize?' . $this->build_http_query($params);
        }
    }


    public function callBackAction()
    {
        $clientCode = $_REQUEST['code'];
        $url = "https://accounts.spotify.com/api/token";
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode(CLIENT_ID . ':' . CLIENT_SECRET)
        ];
        $query = ["form_params" => ['code' => $clientCode,  "grant_type" => "authorization_code", "redirect_uri" => REDIRECTION_URI]];

        $token = $this->getJsonResponseUsingHeadersAndQuery($url, $headers, $query);
        // print_r($token);
        // BQD-pk3e8dLc9saBSrJSBLugrKh_ZIeECjMHvU2tG52839gDqfe6z-N7RrjMJRdYz8Butc_jWmG0emzUDdxPpxl6ChCS-kAmO1C9ibno6yjyZWlsSJjdmHiqdefQqbS1syP1DVFoEWBGK6thK209cSwqbpnVGteNRlf_vslyWysvHm5GYp8zlXLiyN6_NoE9JxgjQ3XWXdKiXCgGFnNqqXfs0rpoZ44umUU
        // Save Token in DB With the name And Token  
        // And Redirect to DashBoard Page 
        $access_token = $token->access_token;
        $refresh_token = $token->refresh_token;
        print_r($access_token);
        // print_r($refresh_token);

        // Set Cookie For login 
        $this->cookies->set(
            "login-action",
            json_encode(["access_token" => $access_token]),
            time() + 15 * 86400
        );
        // redirect to the dashboard 
        return $this->response->redirect('/');
    }
    public function logOutAction()
    {
        $this->cookies->get('login-action')->delete();
        return $this->response->redirect('/');
    }
}
