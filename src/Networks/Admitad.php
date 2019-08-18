<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;
use Illuminate\Support\Carbon;

class Admitad {
    
    private $client_id;
    private $client_secret;
    private $access_token;
    
    public function __construct()
    {  

    } 
    
    public function login($client_id, $client_secret, $website_id) {    
        
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
        $this->website_id = $website_id;

        $url = 'https://api.admitad.com/token/';
        $apiKey = base64_encode($this->client_id . ':' . $this->client_secret);
        $params = array('grant_type' => 'client_credentials', 'client_id' => $this->client_id, 'scope' => 'public_data advcampaigns advcampaigns_for_website referrals coupons_for_website statistics deeplink_generator');
        
              
        $request = new Request();
        $request->setHeader(array('Authorization' => 'Basic '.$apiKey));
        $request->setPostParams($params, 'form_params');
        $response = $request->postContent($url);
        $content = json_decode($response['content']);       
        
        $this->access_token = $content->access_token;
       
    }     
    
    
    public function getMerchants() {
           
        $limit = 100;
        $offset = 0;
        $loop = true;
        $merchants = array(); 
                                           
        while ($loop){
            $url = "https://api.admitad.com/advcampaigns/website/".$this->website_id."/?limit".$limit."&offset=".$offset;
            $request = new Request();
            $request->setHeader(array('Authorization' => 'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
            
            foreach($content->results as $item) {
                $merchant = array();
                $merchant['id'] = $item->id;
                $merchant['name'] = $item->name;  
                $merchant['logo'] = $item->image;
                $merchant['homepage'] = $item->site_url;
                $merchant['domain'] = $this->getHostname($item->site_url);
                $merchant['domains'] = array($this->getHostname($item->site_url));
                $merchants[] = $merchant;
            }
            
            if ((int)$content->_meta->count <= $offset) {
                $loop = false;
            }
            $offset = (int)($limit + $offset);
            
        }    
        return $merchants;
    }
    
    
    public function getCoupons() {    
    
        $limit = 100;
        $offset = 0;
        $loop = true;
        $coupons = array(); 
        
        while ($loop){
            $url = "https://api.admitad.com/coupons/website/".$this->website_id."/?limit".$limit."&offset=".$offset;
            $request = new Request();
            $request->setHeader(array('Authorization' => 'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
            
            foreach($content->results as $item) {
                
                if($item->species == 'promocode') { 
                    $coupon = array();
                    $coupon['id'] = $item->id;
                    $coupon['title'] = $item->name;
                    $coupon['description'] = $item->short_name;
                    $coupon['code'] = $item->promocode; 
                    $coupon['expire'] = Carbon::parse($item->date_end)->toDateTimeString();;
                    $coupon['merchant_id'] = $item->campaign->id;
                    $coupon['url'] = $item->goto_link;
                    $coupons[] = $coupon;
                }
            }
            
            if ((int)$content->_meta->count <= $offset) {
                $loop = false;
            }
            $offset = (int)($limit + $offset);
            
        }    
        return $coupons;        
        
    }    
    
    public function getDeals() {    
    
        $limit = 100;
        $offset = 0;
        $loop = true;
        $deals = array(); 
        
        while ($loop) {
            $url = "https://api.admitad.com/coupons/website/".$this->website_id."/?limit".$limit."&offset=".$offset;
            $request = new Request();
            $request->setHeader(array('Authorization' => 'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
            foreach($content->results as $item) {
                
                if($item->species == 'action') { 
                    $deal = array();
                    $deal['id'] = $item->id;
                    $deal['title'] = $item->name;
                    $deal['description'] = $item->short_name;
                    $deal['expire'] = $item->date_end;
                    $deal['merchant_id'] = $item->campaign->id;
                    $deal['url'] = $item->goto_link;
                    $deals[] = $deal;
                }
            }
            
            if ((int)$content->_meta->count <= $offset) {
                $loop = false;
            }
            $offset = (int)($limit + $offset);
            
        }    
        return $deals;        
        
    }    
        
    public function affiliateUrl($url, $mid) {
        
        $url = "https://api.admitad.com/deeplink/".$this->website_id."/advcampaign/".$mid."/?ulp=".$url;
        $request = new Request();
        $request->setHeader(array('Authorization' => 'Bearer '.$this->access_token));
        $response = $request->getContent($url);
        $content = json_decode($response['content']);         
        
        return $content[0];        
    }          

    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }     
    
}