<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;

class CommissionJunction {
    
    private $token;
    private $client_id;
    private $website_id;
    
    
    public function __construct()
    {  

    }      
    
    public function login($site_id, $api_password, $uid) {    
        
       $this->access_token = $api_password;
       $this->client_id = $uid;
       $this->website_id = $site_id;
    }
    
    
    public function getMerchants() {
        
        $merchants = array();
        $limit = 100; 

            
        $url = "https://advertiser-lookup.api.cj.com/v2/advertiser-lookup?requestor-cid=".$this->client_id."&advertiser-ids=joined&records-per-page=100&page-number=1";

        $request = new Request();
        $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
        $response = $request->getContent($url);
        $content = new \SimpleXMLElement($response['content']);        
        
        $total = (string)$content->advertisers['total-matched'];
        $totalpages = ceil($total/$limit);  
            
        for ($i = 1; $i <= $totalpages; $i++) {
                
            $url = "https://advertiser-lookup.api.cj.com/v2/advertiser-lookup?requestor-cid=".$this->client_id."&advertiser-ids=joined&records-per-page=100&page-number=".$i;

            $request = new Request();
            $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = new \SimpleXMLElement($response['content']);               
            
            foreach($content->advertisers->advertiser as $item)
            {               
                        $merchant = Array();                
                        $merchant['id'] = (string)$item->{'advertiser-id'};
                        $merchant['name'] = (string)$item->{'advertiser-name'};
                        $merchant['domain'] = $this->getHostname($item->{'program-url'});
                        $merchant['domains'] = array($this->getHostname($item->{'program-url'}));
                        $merchant['homepage'] = (string)$item->{'program-url'};
                        $merchant['affiliate_link'] = "http://www.anrdoezrs.net/links/".$this->website_id."/type/dlg/".(string)$item->{'program-url'}; 
                        
                        $merchants[] = $merchant;                
            
            }
        }


        return $merchants; 
                
    }     
    
    
    public function getCoupons() {
        
        $limit = 100;
        $coupons = array(); 
        
        $url = "https://link-search.api.cj.com/v2/link-search?website-id=".$this->website_id."&link-type=Text Link&advertiser-ids=joined&promotion-type=coupon&records-per-page=".$limit."&page-number=1";
        
        $request = new Request();
        $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
        $response = $request->getContent($url);
        $content = new \SimpleXMLElement($response['content']);  
              
        $total = (string)$content->links['total-matched'];
        $totalpages = ceil($total/$limit);        
        
        for ($i = 1; $i <= $totalpages; $i++) {
            
            $url = "https://link-search.api.cj.com/v2/link-search?website-id=".$this->website_id."&link-type=Text Link&advertiser-ids=joined&promotion-type=coupon&records-per-page=".$limit."&page-number=".$i;   
            $request = new Request();
            $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = new \SimpleXMLElement($response['content']);
            
            foreach($content->links->link as $item)
            {               
                        $coupon = Array();                
                        $coupon['id'] = (string)$item->{'link-id'};
                        $coupon['title'] = (string)$item->{'link-name'};
                        $coupon['description'] = (string)$item->{'link-name'};
                        $coupon['description'] = str_replace((string)$item->{'coupon-code'}, '',$coupon['description']);
                        $coupon['code'] = (string)$item->{'coupon-code'};
                        $coupon['expire'] = (string)$item->{'promotion-end-date'};
                        $coupon['final_url'] = (string)$item->{'destination'};
                        $coupon['url'] = (string)$item->{'clickUrl'};
                        $coupon['merchant_id'] = (string)$item->{'advertiser-id'};
                        
                        
                        $coupons[] = $coupon;                
            
            }             
            
        }    
        
        return $coupons;   
    }   
    
    
    public function getDeals() {
        
        $limit = 100;
        $deals = array(); 
        
        $url = "https://link-search.api.cj.com/v2/link-search?website-id=".$this->website_id."&link-type=Text Link&advertiser-ids=joined&promotion-type=sale/discount&records-per-page=".$limit."&page-number=1";
        
        $request = new Request();
        $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
        $response = $request->getContent($url);
        $content = new \SimpleXMLElement($response['content']);  
              
        $total = (string)$content->links['total-matched'];
        $totalpages = ceil($total/$limit);        
        
        for ($i = 1; $i <= $totalpages; $i++) {
            
            $url = "https://link-search.api.cj.com/v2/link-search?website-id=".$this->website_id."&link-type=Text Link&advertiser-ids=joined&promotion-type=sale/discount&records-per-page=".$limit."&page-number=".$i;   
            $request = new Request();
            $request->setHeader(array('Authorization' =>'Bearer '.$this->access_token));
            $response = $request->getContent($url);
            $content = new \SimpleXMLElement($response['content']);
            
            foreach($content->links->link as $item)
            {               
                        $deal = Array();                
                        $deal['id'] = (string)$item->{'link-id'};
                        $deal['title'] = (string)$item->{'link-name'};
                        $deal['description'] = (string)$item->{'link-name'};
                        $deal['expire'] = (string)$item->{'promotion-end-date'};
                        $deal['final_url'] = (string)$item->{'destination'};
                        $deal['url'] = (string)$item->{'clickUrl'};
                        $deal['merchant_id'] = (string)$item->{'advertiser-id'};
                        
                        
                        $deals[] = $deal;                
            
            }             
            
        }    
        
        return $deals;   
    }  

    
    public function affiliateUrl($url) {
        
        $compose = "http://www.anrdoezrs.net/links/".$this->website_id."/type/dlg/".$this->cleanUrl($url);
        return $compose;        
    }    
    
    private function cleanUrl($link){
        if (strpos($link, '?') !== false){ // clean
            $link = substr($link, 0, strpos($link, '?'));
        }
    
        if (strpos($link, '#') !== false){ // clean
            $link = substr($link, 0, strpos($link, '#'));
        }
    
        return $link;
    } 
    
    
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }     
    
    
    
    
}



