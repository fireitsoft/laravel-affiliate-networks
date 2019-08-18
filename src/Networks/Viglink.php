<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class Viglink {
    
    private $secret;
    private $uid;
    
    public function __construct()
    {  

    }      
    
    public function login($uid, $secret) {    
        
       $this->uid = $uid;
       $this->secret = $secret;
       
    }    
    
    public function getMerchants() {
               
        $merchants = array();
        //$url = "https://publishers.viglink.com/api/merchant/search?domain=saksfifthavenue.com&page=1";
        $url = "https://publishers.viglink.com/api/merchant/search?page=1";

        $request = new Request();
        $request->setHeader(array('Authorization' =>'secret '.$this->secret));
        $response = $request->getContent($url);
        $content = json_decode($response['content']);    

        $totalpages = $content->totalPages;

        
        for ($i = 1; $i <= $totalpages; $i++) {
            
            $url = "https://publishers.viglink.com/api/merchant/search?page=".$i;   
            $request = new Request();
            $request->setHeader(array('Authorization' =>'secret '.$this->secret));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
            
            foreach($content->merchants as $item)
            {            
                    if(isset($item->id)) { 
                        if($item->approved == "1") {  
                            $merchant = Array();                
                            $merchant['id'] = $item->id;
                            $merchant['name'] = $item->name;
                            $merchant['domain'] = $this->getHostname($item->domains[0]);
                            $merchant['domains'] = $item->domains;

                            $merchants[] = $merchant;       
                        }         
                    }
            }
            //break; 
        }
        //$executionTime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        //echo $executionTime;
        return $merchants;
    }
    
    public function getCoupons() {  
    
       return;
    } 
    
    public function getDeals() {  
    
       return;
    }    
    
    public function affiliateUrl($url) {
        $url = urlencode($url);
        $compose = "http://redirect.viglink.com?key=".$this->uid."&u=".($url); 
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