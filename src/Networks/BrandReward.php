<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class BrandReward {
    
    private $api_user;
    private $api_key;
    
    public function __construct()
    {  

    } 
    
    public function login($api_user, $api_key) {    
        
       $this->api_key = $api_key;
       $this->api_user = $api_user;
       
    }   
     
    public function getMerchants() {
               
        $merchants = array();
        $url = "http://api.brandreward.com/?act=advertiser.advertiser_list&key=".$this->api_key."&user=".$this->api_user."&favor=0&domain=&country=&category=&pagesize=100000&outformat=json";
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);
        
            foreach($content->data as $item)
            {
                
                if(isset($item->Domains)) {
                    $merchant = Array();                
                    $merchant['id'] = $item->ID;
                    $merchant['name'] = $item->Name;  
                    //$merchant['affiliate_link'] = $item->TrackingLink;
                    $merchant['logo'] = $item->Image; 
                    
                    $merchant['domain'] = $this->getHostname($item->Domains[0]);
                    $merchant['domains'] = $item->Domains;
                    foreach($merchant['domains'] as $key => $domain){
                        $merchant['domains'][$key] = $this->getHostname($domain);
                    }
                    
                    $merchants[] = $merchant; 
                }
            }
                
        return $merchants;
    }
    
    
    public function getCoupons() {
    
        $coupons = array();
        
        $url = "http://api.brandreward.com?act=links.content_feed&key=".$this->api_key."&user=".$this->api_user."&pagesize=10000&outformat=json&language=en";
        
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);       
           
            foreach($content->data as $item) {
                
                if($item->CouponCode != "" && $item->CouponCode != 'N/A') {
                    $coupon = array();
                    $coupon['id'] = $item->LinkID;
                    $coupon['title'] = stripslashes($item->Title);
                    if($item->Description)
                    $coupon['description'] = stripslashes($item->Description);
                    $coupon['code'] = $item->CouponCode; 
                    $coupon['expire'] = $item->EndDate;
                    $coupon['merchant_id'] = $item->AdvertiserID;
                    $coupon['url'] = $item->LinkUrl;
                    $coupons[] = $coupon;    
                }      
            }
        
       return $coupons;
    }    
    
    public function getDeals() {
    
        $deals = array();
        
        $url = "http://api.brandreward.com?act=links.content_feed&key=".$this->api_key."&user=".$this->api_user."&pagesize=10000&outformat=json&language=en";
        
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);               
            
            foreach($content->data as $item) {
                
                if($item->CouponCode == "" || $item->CouponCode == 'N/A') {
                    $deal = array();
                    $deal['id'] = $item->LinkID;
                    $deal['title'] = stripslashes($item->Title);
                    $deal['description'] = stripslashes($item->Description);
                    $deal['expire'] = $item->EndDate;
                    $deal['merchant_id'] = $item->AdvertiserID;
                    $deal['url'] = $item->LinkUrl;
                    $deals[] = $deal;    
                }      
            }
        
       return $deals;
    }     
 
    public function affiliateUrl($url) {
        
        return "https://r.brandreward.com?key=".$this->api_key."&url=".urlencode($url);        
    }  
 
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    } 
    
    
}