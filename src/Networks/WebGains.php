<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;
use Illuminate\Support\Carbon;

class WebGains {
    
    private $campaign_id;
    private $key;
    
    public function __construct()
    {  

    }      
    
    public function login($campaign_id, $key) {    
        
       $this->key = $key;
       $this->campaign_id = $campaign_id;
       
    }  
 
    public function getMerchants() {

        $merchants = array();
        $url = 'http://api.webgains.com/2.0/programs?&key='.$this->key;

        $request = new Request(60);

        $response = $request->getContent($url);
        $content = json_decode($response['content']);    
               
            foreach($content as $item)
            {
                
                if($item->membershipStatusText == "Joined") {
                    $merchant = Array();                
                    //$merchant['id'] = $item->merchantID;
                    $merchant['id'] = $item->id;
                    $merchant['name'] = $item->name; 
                    $merchant['description'] = $item->description; 
                    $merchant['homepage'] = $item->homepageURL; 
                    //$merchant['logo'] = 'https:'.$item->logo; 
                    $merchant['domain'] = $this->getHostname($item->homepageURL);
                    $merchant['domains'] = array($this->getHostname($item->homepageURL));
                    $merchant['affiliate_link'] = "http://track.webgains.com/click.html?wgcampaignid=".$this->campaign_id."&wgprogramid=".$item->id;
                    
                    $merchants[] = $merchant; 
                }
            }          
        
        return $merchants;
        
    }    
    
    public function getCoupons() {    
    
        $limit = 100;
        $coupons = array();    
    
    
        $url = "http://api.webgains.com/2.0/vouchers?key=".$this->key."&joined=1";
  
        $request = new Request(60);
        $response = $request->getContent($url);  
        $content = json_decode($response['content']);  
        
            foreach($content as $item)
            {               
                        $coupon = Array();                
                        $coupon['id'] = $item->id;
                        //$coupon['title'] = $item->description;
                        $coupon['title'] = str_replace($item->code, '', $item->description); 
                        $coupon['code'] = $item->code;
                        //$coupon['expire'] = $item->expiryDate;
                        $coupon['expire'] = Carbon::parse($item->expiryDate)->toDateTimeString();
                        $coupon['final_url'] = $item->destinationUrl;
                        $coupon['url'] = $item->trackingUrl;
                        $coupon['merchant_id'] = $item->programId;

                        $coupons[] = $coupon;                
            
            } 

        return $coupons;
    }   
    
    
    public function getDeals() { 
    
       return;
    }    
     
    public function affiliateUrl($url, $program = null) {
        $compose = "http://track.webgains.com/click.html?wgcampaignid=".$this->campaign_id."&wgprogramid=".$program."&wgtarget=".urlencode($url);
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