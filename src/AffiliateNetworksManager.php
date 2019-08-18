<?php
namespace FireItSoft\AffiliateNetworks;


class AffiliateNetworksManager
{
    
    protected $networks = [];
    protected $avaliable_networks = [];     


    public function __construct()
    {
        $this->loadAvailableNetworks();
    }    
    
    
    protected function loadAvailableNetworks(){
        $classes=scandir(__DIR__.'/Networks');
        foreach ($classes AS $network_class){
            if ($network_class=='.' || $network_class=='..'){
                continue;
            }
            $class = new \ReflectionClass(__NAMESPACE__.'\\Networks\\'.substr($network_class,0,-4));
            $this->avaliable_networks[$class->getShortName()]=$class->getName();
        }
    }    
    
    public function getAvailableNetworks():array {
        return $this->avaliable_networks;
    }    
       
    public function hasNetwork($network_alias) {
        if (!array_key_exists($network_alias, $this->networks ) && array_key_exists($network_alias, $this->avaliable_networks)){
            $fully_className=$this->avaliable_networks[$network_alias];
            $this->networks[$network_alias]= new $fully_className();
        }
        return array_key_exists($network_alias, $this->networks);
    }
    
    public function login(string $network_alias, string $username, string $password, string $id_site = '') {
             
        if (!$this->hasNetwork($network_alias)) {
            return false;
        }
            
        $response = $this->networks[$network_alias]->login($username, $password, $id_site);
        return $response;    
    }      
    
    
    public function getMerchants(string $network_alias) {

        if (!$this->hasNetwork($network_alias)) {
            return false;
        }
                
        $response = $this->networks[$network_alias]->getMerchants();
        return $response;
    }   
    
    
    public function getCoupons(string $network_alias) {

        if (!$this->hasNetwork($network_alias)) {
            return false;
        }
                
        $response = $this->networks[$network_alias]->getCoupons();
        return $response;
    }   
    
    public function getDeals(string $network_alias) {

        if (!$this->hasNetwork($network_alias)) {
            return false;
        }
                
        $response = $this->networks[$network_alias]->getDeals();
        return $response;
    } 
    
    public function affiliateUrl(string $network_alias, string $url, $id=null) {

        if (!$this->hasNetwork($network_alias)) {
            return false;
        }
                
        $response = $this->networks[$network_alias]->affiliateUrl($url, $id);
        return $response;
    }        
  
     
    
}