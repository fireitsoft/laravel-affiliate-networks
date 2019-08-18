<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class PepperJam {
    
    private $api_key;
    private $api_version;
    private $pub_id;
    
    public function __construct()
    {  

    } 


    public function login($api_version, $api_key, $pub_id) {    
        
       $this->api_version = $api_version;
       $this->api_key = $api_key;
       $this->pub_id = $pub_id;
       
    }  

    
    public function getMerchants() {    
    
        $merchants = array();
        $url = "https://api.pepperjamnetwork.com/{$this->api_version}/publisher/advertiser?apiKey={$this->api_key}&format=json&status=joined";
        
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);        
                   
            foreach($content->data as $item)
            {
                
                $merchant = Array();                
                $merchant['id'] = $item->id;
                $merchant['name'] = $item->name; 
                $merchant['description'] = $item->description; 
                $merchant['homepage'] = $item->website; 
                $merchant['logo'] = 'https:'.$item->logo; 
                $merchant['domain'] = $this->getHostname($item->website);
                $merchant['domains'] = array($this->getHostname($item->website));

                $merchants[] = $merchant; 
                
            }          
        
        return $merchants;
            
    }
    
    
    public function getCoupons() {
        
        $limit = 100;
        $coupons = array(); 
        $url = "https://api.pepperjamnetwork.com/".$this->api_version."/publisher/creative/coupon?apiKey=".$this->api_key."&format=json&status=active&page=1";
        
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);       
          
        $totalpages = $content->meta->pagination->total_pages;  
          
        for ($i = 1; $i <= $totalpages; $i++) {
        
            $url = "https://api.pepperjamnetwork.com/".$this->api_version."/publisher/creative/coupon?apiKey=".$this->api_key."&format=json&status=active&page=".$i;
        
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);     
            
            
            foreach($content->data as $item)
            {
                
                $coupon = Array();                
                $coupon['id'] = $item->id;
                $coupon['title'] = str_replace($item->coupon, '', $item->name); 
                $coupon['description'] = str_replace($item->coupon, '', $item->description); 
                $coupon['code'] = $item->coupon;
                $coupon['expire'] = $item->end_date;
                $coupon['url'] = $item->code;
                $coupon['merchant_id'] = $item->program_id;                

                $coupons[] = $coupon; 
                
            }               
        
        
        }  
        return $coupons;  
    }
    
    public function getDeals() {
        
        $limit = 100;
        $deals = array(); 
        $url = "https://api.pepperjamnetwork.com/".$this->api_version."/publisher/creative/text?apiKey=".$this->api_key."&format=json&status=active&page=1";
        
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);                   
          
        $totalpages = $content->meta->pagination->total_pages;  
          
        for ($i = 1; $i <= $totalpages; $i++) {
        
            $url = "https://api.pepperjamnetwork.com/".$this->api_version."/publisher/creative/text?apiKey=".$this->api_key."&format=json&status=active&page=".$i;
        
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);     
            
            
            foreach($content->data as $item)
            {
                
                $deal = Array();                
                $deal['id'] = $item->id;
                $deal['title'] = $item->name; 
                $deal['description'] = $item->description; 
                $deal['expire'] = $item->end_date;
                $deal['url'] = $item->tracking_url;
                $deal['merchant_id'] = $item->program_id;                

                $deals[] = $deal; 
                
            }               
        
        
        }  
        return $deals;  
    }    
    
    
    public function affiliateUrl($url) {
        
         $link = "http://www.pepperjamnetwork.com/share/bookmarklet?publisherId=".$this->pub_id."&url=".$url;
                
         $homepage = file_get_contents($link);

         $re = "/pjxTrackingLink =  '(.*)';/isU";
         preg_match($re, $homepage, $matches);
         $compose = $matches[1];
         return $compose;     
    }     
    
    
    
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }    

}
