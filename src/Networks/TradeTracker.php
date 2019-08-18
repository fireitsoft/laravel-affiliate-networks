<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;
use Illuminate\Support\Carbon;

class TradeTracker {
    
    private $customer_id;
    private $passphrase;
    private $site_id;
    
    public function __construct()
    {  

    }      
    
    public function login($customer_id, $passphrase, $site_id) {    
        
        $this->customerId = $customer_id;
        $this->passphrase = $passphrase;
        $this->site_id = $site_id;
        
        $client = new \SoapClient('http://ws.tradetracker.com/soap/affiliate?wsdl', array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
        $client->authenticate($this->customerId, $this->passphrase, false, 'en_GB', false);        
        $this->client = $client;       
    }      
    
    public function getMerchants() {   
     
        $client = $this->client;
        $merchants = array();
        $options = array (
            'assignmentStatus' => 'accepted'
        );                   
                
        foreach ($client->getCampaigns($this->site_id, $options) as $campaign) {   
            $merchant = Array();
            $merchant['id'] = $campaign->ID;
            $merchant['name'] = $campaign->name;
            $merchant['domain'] = $this->getHostname($campaign->URL);
            $merchant['domains'] = array($this->getHostname($campaign->URL));
            $merchant['homepage'] = $campaign->URL;
            $merchant['logo'] = $campaign->info->imageURL;            
            $merchant['affiliate_link'] = $campaign->info->trackingURL;            
            $merchants[] = $merchant;
        }  
        
        return $merchants; 
    }
    
    public function getCoupons() {    
    
        $client = $this->client;
        $coupons = array();
        $materialOutputType = 'rss';
        $options = array(
            'limit' => 10000,
        );
        
        foreach ($client->getMaterialIncentiveVoucherItems($this->site_id, $materialOutputType, $options) as $item) {      
          
                        preg_match('/<link>(.*)<\/link>/', $item->code, $code);
                        $coupon = Array();                
                        $coupon['id'] = $item->ID;
                        $coupon['title'] = str_replace($item->voucherCode, '', $item->name);
                        //$coupon['description'] = $item->description;
                        $coupon['description'] = str_replace($item->voucherCode, '', $item->description);
                        $coupon['code'] = $item->voucherCode;
                        $coupon['expire'] = Carbon::parse($item->validToDate)->toDateTimeString();
                        if($item->conditions)
                        $coupon['terms'] = $item->conditions;
                        //$coupon['url'] = "https://tc.tradetracker.net/?c=".$item->campaign->ID."&amp;m=".$item->ID."&amp;a=".$this->site_id."&amp;r=&amp;u=";
                        $coupon['url'] = $code[1];
                        $coupon['merchant_id'] = $item->campaign->ID;

                        $coupons[] = $coupon;          
            
        }
        
        return $coupons;   
    }    
       
    public function getDeals() {   
         
        $client = $this->client;
        $deals = array();
        $materialOutputType = 'rss';
        $options = array(
            'limit' => 10000,
        );
        
        foreach ($client->getMaterialIncentiveOfferItems($this->site_id, $materialOutputType, $options) as $item) { 
            
                        preg_match('/<link>(.*)<\/link>/', $item->code, $code);

                        $deal = Array();                
                        $deal['id'] = $item->ID;
                        $deal['title'] = $item->name;
                        $deal['description'] = $item->description;
                        $deal['expire'] = $item->validToDate;
                        $deal['terms'] = $item->conditions;
                        $deal['url'] = $code[1];
                        $deal['merchant_id'] = $item->campaign->ID;

                        $deals[] = $deal;          
            
        }
        
        return $deals;       
       
    } 
    
    public function affiliateUrl($url, $affiliate_link) {
        
        $path = urlencode(parse_url($url, PHP_URL_PATH)); 
        return $affiliate_link.$path;      
    }    
       

    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }  
 
        
}