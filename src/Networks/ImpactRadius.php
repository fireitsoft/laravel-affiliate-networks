<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;


class ImpactRadius {


    private $sid;
    private $token;
    
    public function __construct()
    {  

    }      
    
    public function login($sid, $token) {    
        
       $this->sid = $sid;
       $this->token = $token;
       
    }
    
    public function getMerchants() {

        $merchants = array();
        $url = 'https://'.$this->sid.':'.$this->token.'@api.impactradius.com/Mediapartners/'.$this->sid.'/Campaigns.json';

        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']);    
        $totalpages = $content->{'@numpages'}; 
 
        for ($i = 1; $i <= $totalpages; $i++) {         
         
            $url = 'https://'.$this->sid.':'.$this->token.'@api.impactradius.com/Mediapartners/'.$this->sid.'/Campaigns.json?Page='.$i;

            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']); 

               
            foreach($content->Campaigns as $item)
            {
                
                if($item->InsertionOrderStatus == "Active") {
                    
                    $merchant = Array();                
                    $merchant['id'] = $item->CampaignId;
                    $merchant['name'] = $item->CampaignName; 
                    $merchant['description'] = $item->CampaignDescription; 
                    $merchant['affiliate_link'] = $item->TrackingLink;
                    $merchant['logo'] = 'https://cdn2.impact.com:'.$item->CampaignLogoUri; 
                    $merchant['domain'] = $this->getHostname($item->CampaignUrl);
                    $merchant['domains'] = array($this->getHostname($item->CampaignUrl));
                    //$merchant['domains'] = $item->DeeplinkDomains;
                    $merchant['homepage'] = $item->CampaignUrl;
                    //$merchant['domains'] = $item->DeeplinkDomains;
                    
                    $merchants[] = $merchant; 
                }
            }          
         
        } 
         
            
        return $merchants;
    }
    
    
    public function getCoupons() {
    
        return;
        
        $merchants = $this->getMerchants();
        $coupons = array();
        
        foreach($merchants as $merchant) {
            
            
            $url = 'https://'.$this->sid.':'.$this->token.'@api.impactradius.com/Mediapartners/'.$this->sid.'/Campaigns/'.$merchant['id'].'/Deals.json';       
              
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);
            
            foreach($content->Deals as $item) {
                if($item->State == "ACTIVE" && $item->DefaultPromoCode != '') {
                    $coupon = array();
                    $coupon['id'] = $item->Id;
                    $coupon['title'] = $item->Name;
                    $coupon['description'] = $item->Description;
                    $coupon['code'] = $item->DefaultPromoCode;
                    if($item->EndDate)
                    $coupon['expire'] = $item->EndDate;
                    $coupon['merchant_id'] = $item->CampaignId;
                    $coupons[] = $coupon;
                }
                
            }            
            
        }

       return $coupons;
    }  
    
    
    public function getDeals() {
    
        $merchants = $this->getMerchants();
        $deals = array();
        
        foreach($merchants as $merchant) {
            
            
            $url = 'https://'.$this->sid.':'.$this->token.'@api.impactradius.com/Mediapartners/'.$this->sid.'/Campaigns/'.$merchant['id'].'/Deals.json';       
              
            $request = new Request();
            $response = $request->getContent($url);
            $content = json_decode($response['content']);
            
            foreach($content->Deals as $item) {
                if($item->State == "ACTIVE" && $item->DefaultPromoCode == '') {
                    $deal = array();
                    $deal['id'] = $item->Id;
                    $deal['title'] = $item->Name;
                    $deal['description'] = $item->Description;
                    $deal['code'] = $item->DefaultPromoCode;
                    $deal['expire'] = $item->EndDate;
                    $deal['merchant_id'] = $item->CampaignId;
                    $deals[] = $deal;
                }
                
            }            
            
        }

       return $deals;
    }       
    
    
    public function affiliateUrl($url, $affiliate_link) {
        
        return $affiliate_link."?u=".urlencode($url);        
    }      
    
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }     
             
    
    
}

