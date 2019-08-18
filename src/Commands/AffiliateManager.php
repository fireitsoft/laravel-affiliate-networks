<?php
namespace FireItSoft\AffiliateNetworks\Commands;

use Illuminate\Console\Command;
use FireItSoft\AffiliateNetworks\AffiliateNetworksManager;

use App\Models\Networks;

class AffiliateManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Getting merchants and offers from affiliate networks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$class = $this->argument('class');

        $networks = Networks::all()->toArray();
        
        $networks_id = array();
        foreach($networks as $net){
            $networks_id[$net['id']] = $net['code'];  
        }        

        $selected_network = $this->choice('Select the affiliate network', $networks_id, '1');
        $selected_method = $this->choice('Select the desired action', ['getMerchants', 'getCoupons', 'getDeals'], '0');
        
        $network = Networks::where('code', '=', $selected_network)->first();
        
        $manager = new AffiliateNetworksManager();
        $manager->login($network->code, $network->api_username, $network->api_password, $network->api_user_id);
        if($selected_method == "getMerchants")
        $merchants = $manager->getMerchants($network->code);
        elseif($selected_method == "getCoupons")
        $merchants = $manager->getCoupons($network->code);
        elseif($selected_method == "getDeals")
        $merchants = $manager->getDeals($network->code); 
        
        
               
       // $manager->login($selected_network, $network->api_username, $network->api_password, $network->api_user_id);
       // $merchants = $manager->getMerchants($selected_network);
        //$answer = $this->ask('Select the affiliate network');
        //$this->info("Thanks for do the quiz in the console, your answers : ");
        //return "test";
        //
    }
}
