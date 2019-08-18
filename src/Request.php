<?php

namespace FireItSoft\AffiliateNetworks;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class Request
{
    
    private $webClient; 
    private $timeout;
    private $headers;   
    private $cookie;   
    private $debug;   
    
    public function __construct($timeout=null)
    {
        if($timeout)
        $this->timeout = $timeout;
           
        $this->webClient = new Client([  
            'timeout'         => $this->timeout,
            'allow_redirects' => ['track_redirects' => true, 'max' => 10, 'strict' => false, 'protocols' => ['http', 'https'], 'referer' => false]
        ]); 
        $this->setHeaders();        
    }

    public function setHeaders(){
        
        $agents = array(
          'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
          'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0',
          'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
          'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
          'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)',
          'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Trident/6.0)',
          'Mozilla/5.0 (Windows NT 5.1; rv:7.0.1) Gecko/20100101 Firefox/7.0.1',
          'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
          'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:15.0) Gecko/20100101 Firefox/15.0'
        );
        $agent = $agents[array_rand($agents, 1)];

        $headers = array(
        'verify' => false,
        'timeout' => $this->timeout,
        'cookies'         => $this->cookie,
        'connect_timeout' => $this->timeout,
        'http_errors' => false,
        'headers' => [
            'User-Agent' => $agent,
            'Accept' => 'text/html,application/json,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive' 
        ],
        'debug' => $this->debug
        );  
        
        
        $this->headers = $headers;  
        
    }
    
    public function setHeader($array){ 
       
        foreach($array as $key => $item){
            $this->headers['headers'][$key] = $item;
        } 
        
    }
    
    
    public function setPostParams($array, $type){ 
       
         $this->headers[$type] = $array;   
    }  
    
    public function getContent($url) {
        
        
        $client = new \GuzzleHttp\Client();
    
        try {
            $response = $client->get($url, $this->headers);
        } catch(ConnectException $e) {
            throw new \RuntimeException(
                    $e->getHandlerContext()['error']
                );
        } catch(\GuzzleHttp\Exception\RequestException $e) {
           // \Log::alert('Error on proxy :  Messsage:'.$e->getHandlerContext()['error'].' on url '. $url);
            Log::channel('guzzle')->info('Error:  Messsage:'.$e->getMessage().' on url '. $url);
           // print_r($e->getHandlerContext());
            return;
        }        
        
        $html = $response->getBody();
        $item['code'] = $response->getStatusCode();
        $item['content'] = $html->getContents();
        
        return $item;        
    }
    
    public function postContent($url) {
        
        
        $client = new \GuzzleHttp\Client();
          // print_r($this->headers);
        try {
            $response = $client->post($url, $this->headers);
        } catch(ConnectException $e) {
            throw new \RuntimeException(
                    $e->getHandlerContext()['error']
                );
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            \Log::alert('Error on proxy :  Messsage:'.$e->getHandlerContext()['error'].' on url '. $url);
            return;
        }        
        
        $html = $response->getBody();
        $item['code'] = $response->getStatusCode();
        $item['content'] = $html->getContents();
        
        
        return $item;        
    }    
    

    public function postAjax($url, $data) {
    
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post($url, [
                'json' => $data,
            ]);
            
        } catch(ConnectException $e) {
            throw new \RuntimeException(
                    $e->getHandlerContext()['error']
                );
        } catch(\GuzzleHttp\Exception\RequestException $e) {
            \Log::alert('Error on proxy :  Messsage:'.$e->getHandlerContext()['error'].' on url '. $url);
            return;
        }
        
        $html = $response->getBody();
        $item['code'] = $response->getStatusCode();
        $item['content'] = $html->getContents();
        
        
        return $item;                
       
    } 
    
    
}