<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class Digidip {
    
    private $username;
    private $password;
    private $api_password;
    
    public function __construct()
    {  

    }      
    
    public function login($username, $password) {    
        
       $this->username = $username;
       $this->password = $password;
       $this->api_password = base64_encode($username.':'.$password);
       
    }    
    
    public function getMerchants() {
               
        $merchants = array();
        $url = "https://api.digidip.net/merchants";
        $request = new Request();
        $request->setHeader(array('Authorization' => 'Basic '.$this->api_password));
        $request->setHeader(array('Accept' => 'application/json'));
        $response = $request->getContent($url);
        $content = json_decode($response['content']);
        
       // print_r($content);
       // die();
        
        
        $totalpages = ceil($content->_constraints->total_amount / $content->_constraints->max_elements);
        
        for ($i = 1; $i <= $totalpages; $i++) {
            
            $url = "https://api.digidip.net/merchants?page=".$i;
            $request = new Request();
            $request->setHeader(array('Authorization' => 'Basic '.$this->api_password));
            $request->setHeader(array('Accept' => 'application/json'));
            $response = $request->getContent($url);
            $content = json_decode($response['content']);            
            
            foreach($content->data as $item)
            {            
                    if (empty($item->blocked_projects)) {
                        $merchant = Array();                
                        $merchant['id'] = $item->merchant_id;
                        $merchant['name'] = $item->merchant_name;
                        $merchant['domain'] = $this->getHostname($item->hosts[0]);
                        $merchant['domains'] = $item->hosts; 
                        
                        $merchants[] = $merchant;  
                    }          
            }
            
        }       
                
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
        $compose = "https://couponarea.digidip.net/visit?url=".($url); 
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