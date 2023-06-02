<?php

namespace App\Console\Commands\Cron;

use Illuminate\Console\Command;
use App\Models\Customer;
use Carbon\Carbon;
use App\Notifications\CustomerCreated;
use Auth;


class StartNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature = 'cron:start-notify';
 
    protected $description = '开始通知计时';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	Customer::query()
	//下一次待联系时间早于当前时间
         ->where('last_used_at', '<=', Carbon::now())
	 ->get()
	 ->each(function (Customer $customer) {
	     //Notify 通知	
	     $customer->user->notify(new CustomerCreated($customer));
	});
	
    }
}
