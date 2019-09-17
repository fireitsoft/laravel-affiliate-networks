<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class Skimlinks {
    
    private $publisher_id;
    private $url = "https://merchants.skimapis.com/v4/publisher/";
    private $access_token;
    private $client_id;
    private $client_secret;
    private $sid = "92348X1546290";
    
    
    public function __construct()
    {  

    }      

    public function login($client_id, $client_secret, $publisher_id) {
        
       $request = new Request(); 
       $response = $request->postAjax('https://authentication.skimapis.com/access_token', array('client_id' => $client_id, 'client_secret' => $client_secret, 'grant_type' => 'client_credentials'));
       $content = json_decode($response['content']);

       $this->access_token = $content->access_token;
       $this->client_id = $client_id;
       $this->client_secret = $client_secret;
       $this->publisher_id = $publisher_id;
       
    }


    public function getMerchants() {
        
        $merchants = array();
        $limit = 200; 
        $total = 16000;

        $numbers = range(0, $total, $limit);

        foreach ($numbers as $number) {
            
            $url = $this->url.$this->publisher_id."/merchants?access_token=".$this->access_token."&offset=".$number;

            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);

            if(isset($content->merchants)) {
                    foreach($content->merchants as $item) {
                        if($item->domain) { 
                                $merchant = Array();
                                $merchant['id'] = $item->id;
                                $merchant['name'] = $item->name;
                                $merchant['domain'] = $item->domain;
                                $merchant['domains'] = $item->domains;
                                $merchant['logo'] = $item->metadata->logo;
                                $merchant['description'] = $item->metadata->description;
                                $merchants[] = $merchant;
                        }
                    }
            }
        }


        return $merchants; 
                
    } 
    

    public function getCoupons() {
    
        $coupons = array();
        $limit = 2000;
        $total = 10000;        
        $numbers = range(0, $total, $limit);

        foreach ($numbers as $number) {
            $url = $this->url.$this->publisher_id."/offers?access_token=".$this->access_token."&offset=".$number."&limit=".$limit;
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);       
        
            if(isset($content->offers)) {
        
                foreach($content->offers as $item) {
                    if($item->coupon_code != '') {
                            $coupon = Array();
                            $coupon['id'] = $item->id;
                            $coupon['title'] = $item->title;
                            $coupon['description'] = str_replace($item->coupon_code, '', $item->description);
                            $coupon['code'] = trim($item->coupon_code);
                            $coupon['code'] = str_replace('no code needed', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('no code required', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('no coupon necessary', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('no coupon required', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('no code', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('none needed', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('none required', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('n/a', '', strtolower($coupon['code']));
                            $coupon['code'] = str_replace('none', '', strtolower($coupon['code']));
                            if(empty($coupon['code']))
                            unset($coupon['code']);                            
                            
                            if($item->terms)
                            $coupon['terms'] = $item->terms;
                            $coupon['expire'] = $item->offer_ends;
                            $coupon['final_url'] = $item->url;
                            $coupon['url'] = $this->affiliateUrl($item->url);
                            $coupon['merchant_id'] = $item->merchant_details->merchant_id;
                            $coupon['merchant_domains'] = $item->merchant_details->domains;

                            $coupons[] = $coupon;
                    }
                }                
                
            }
        
        }
        
        
        return $coupons;
    }   
    
    public function getDeals() {
    
        $deals = array();
        $limit = 2000;
        $total = 10000;        
        $numbers = range(0, $total, $limit);
    
        foreach ($numbers as $number) {
            $url = $this->url.$this->publisher_id."/offers?period=ongoing&access_token=".$this->access_token."&offset=".$number."&limit=".$limit;
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);       
        
            if(isset($content->offers)) {
        
                foreach($content->offers as $item) {
                    if($item->coupon_code == '') {
                            $deal = Array();
                            $deal['id'] = $item->id;
                            $deal['title'] = $item->title;
                            $deal['description'] = str_replace($item->coupon_code, '', $item->description);
                            $deal['terms'] = $item->terms;
                            $deal['expire'] = $item->offer_ends;
                            $deal['final_url'] = $item->url;
                            $deal['url'] = $this->affiliateUrl($item->url);
                            $deal['merchant_id'] = $item->merchant_details->merchant_id;
                            $deal['merchant_domains'] = $item->merchant_details->domains;

                            $deals[] = $deal;
                    }
                }                
                
            }
        
        }
        
        
        return $deals;
    }      
    
    
    public function affiliateUrl($url) {
        
        $url = urlencode($url);
        $compose = "http://go.redirectingat.com?id=".$this->sid."&url=".($url); 
        return $compose;        
    }
    
    
    
}