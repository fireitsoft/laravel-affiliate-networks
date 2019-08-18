<?php
namespace FireItSoft\AffiliateNetworks\Networks;

use FireItSoft\AffiliateNetworks\Request;
use Illuminate\Support\Carbon;

class AffiliateWindow {
    
    private $account_id;
    private $api_password;
    private $api_key;
    
    public function __construct()
    {  

    } 
    
    
    public function login($account_id, $api_password, $api_key) {    
        
       $this->account_id = $account_id;
       $this->api_password = $api_password;
       $this->api_key = $api_key;
       
    }  
    
    public function getMerchants() {
    
        $merchants = array();
        $url = "https://api.awin.com/publishers/".$this->account_id."/programmes/?relationship=joined&accessToken=".$this->api_password;
        $request = new Request();
        $response = $request->getContent($url);
        $content = json_decode($response['content']); 
        
            foreach($content as $item)
            {
                $merchant = array();
                $merchant['id'] = $item->id;
                $merchant['name'] = $item->name;  
                $merchant['affiliate_link'] = $item->clickThroughUrl;
                $merchant['logo'] = $item->logoUrl;            
                $merchant['homepage'] = $item->displayUrl;
                $merchant['domain'] = $this->getHostname($item->displayUrl);
                $merchant['domains'] = array($this->getHostname($item->displayUrl));
                $merchants[] = $merchant;
            }               
    
        return $merchants;
    }
    
    
    public function getCoupons() {
        
        $coupons = array();
        $url = "https://ui.awin.com/export-promotions/".$this->account_id."/".$this->api_key."?downloadType=XML&promotionType=voucher&categoryIds=&amp;regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=active";
        
        $request = new Request();
        $response = $request->getContent($url);     
        $csv = array_map('str_getcsv', str_getcsv($response['content'],"\n"));
        $array = array();
        foreach($csv as $key => $item) {
            
            
            if($key != 0) {
                    $coupon = array();
                    $coupon['id'] = $item[0];
                    if(isset($item[17]))
                    $coupon['title'] = $item[17];
                    if(isset($item[5]))
                    $coupon['description'] = $item[5];
                    if(isset($item[4]))
                    $coupon['code'] = $item[4]; 
                    if(isset($item[7]))
                   // $coupon['expire'] = $item[7];
                    $coupon['expire'] = Carbon::parse($item[7])->toDateTimeString();
                    if(isset($item[10]))
                    $coupon['terms'] = $item[10];
                    if(isset($item[2]))
                    $coupon['merchant_id'] = (int)$item[2];
                    if(isset($item[11]))
                    $coupon['url'] = $item[11];
                    if(isset($item[12]))
                    $coupon['final_url'] = $item[12];
                    $coupons[] = $coupon;                
            }
            
            
        }
        
        return $coupons;
    }     
    
    
    public function getDeals() {
        
        $deals = array();
        $url = "https://ui.awin.com/export-promotions/".$this->account_id."/".$this->api_key."?downloadType=XML&promotionType=promotion&categoryIds=&amp;regionIds=&advertiserIds=&membershipStatus=joined&promotionStatus=active";
        
        $request = new Request();
        $response = $request->getContent($url);     
        $csv = array_map('str_getcsv', str_getcsv($response['content'],"\n"));
        $array = array();
        foreach($csv as $key => $item) {
            
            
            if($key != 0 && isset($item[0])) {
                    $coupon = array();
                    if(isset($item[0]))
                    $coupon['id'] = $item[0];
                    if(isset($item[17]))
                    $coupon['title'] = $item[17];
                    if(isset($item[5]))
                    $coupon['description'] = $item[5]; 
                    if(isset($item[7]))
                    $coupon['expire'] = $item[7];
                    if(isset($item[10]))
                    $coupon['terms'] = $item[10];
                    if(isset($item[2]))
                    $coupon['merchant_id'] = (int)$item[2];
                    if(isset($item[11]))
                    $coupon['url'] = $item[11];
                    if(isset($item[12]))
                    $coupon['final_url'] = $item[12];
                    $deals[] = $coupon;                
            }
            
            
        }
        
        return $deals;
    }       
 
    
    public function getHostname($url)
    {
      $extract = new \LayerShifter\TLDExtract\Extract();
      $result = $extract->parse($url);
      $domain = $result->getRegistrableDomain();
      return $domain;
    }  
    
    public function affiliateUrl($url, $mid) {
        
        return "https://www.awin1.com/cread.php?awinmid=".$mid."&awinaffid=".$this->account_id."&clickref=&p=%5B%5B".urlencode($url)."%5D%5D";        
    }     
       
    
}        