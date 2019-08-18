<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;
use Illuminate\Support\Carbon;

class Flexoffers {
    
    private $apikey;
    
    public function __construct()
    {  

    }  
    
    
    public function login($apikey) {    
        
       $this->apikey = $apikey;
       
    }     
    
    public function getMerchants() {    
    
        $merchants = array();
        $url = "http://api.flexoffers.com/advertisers.json";
        
        $request = new Request();
        $request->setHeader(array('ApiKey' => $this->apikey));
        $response = $request->getContent($url);
        $content = json_decode($response['content']);        
      
            foreach($content as $item)
            {
                
                $merchant = Array();                
                $merchant['id'] = $item->id;
                $merchant['name'] = $item->name; 
                $merchant['logo'] = $item->imageUrl; 
                $merchant['homepage'] = $item->domainUrl; 
                $merchant['description'] = $item->description; 
                $merchant['domains'] = array($this->getHostname($item->domainUrl));
                $merchant['domain'] = $this->getHostname($item->domainUrl);

                $merchants[] = $merchant; 
                
            }          
        
        
        return $merchants;
            
    }
    
    public function getCoupons() {    
    
        $coupons = array();
        $limit = 500;
        
        $url = "http://api.flexoffers.com/links.json?page=1&pageSize=".$limit."&promotionTypeIds=2";
        
        $request = new Request();
        $request->setHeader(array('ApiKey' => $this->apikey));
        $response = $request->getContent($url);
        $content = json_decode($response['content']);        
              
        $totalpages = ceil($content->totalCount/$limit); 
        
        for ($i = 1; $i <= $totalpages; $i++) {
                          
            $url = "http://api.flexoffers.com/links.json?page=".$i."&pageSize=".$limit."&promotionTypeIds=2";
            
            $request = new Request();
            $request->setHeader(array('ApiKey' => $this->apikey));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
                       
            foreach($content->results as $item)
            {
                
                $coupon = Array();                
                $coupon['id'] = $item->productId;
                $coupon['title'] = str_replace($item->couponCode, '', $item->linkText); 
                $coupon['code'] = $item->couponCode;
                //$coupon['expire'] = $item->expireDate;
                $coupon['expire'] = Carbon::parse($item->expireDate)->toDateTimeString();
                $coupon['url'] = $item->linkUrl;
                $coupon['merchant_id'] = $item->advertiserId;                

                $coupons[] = $coupon; 
                
            }          
           
        }   
           
        return $coupons;
            
    }    
    
    
    public function getDeals() {  
    
       return;
    }  
    
    public function affiliateUrl($url) {
        
        return;     
    }      

    
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }     
    
    
}