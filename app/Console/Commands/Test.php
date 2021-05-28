<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderEmail;
use App\Sdk\FrogMiscSapi;
use App\Sdk\MiscSapi;
use Illuminate\Console\Command;
use Crypt;
class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test {param=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $param = $this->argument('param');

//        SendReminderEmail::dispatch($param)->allOnConnection('redis')->delay(now()->addMinutes(1));

        $sdk = new FrogMiscSapi('http://api.1d1d100.net/base/location/provinces');
        $result = $sdk -> test([]);
        dd($result);

//        $client = new \GuzzleHttp\Client();
//        $res = $client->request('GET','http://api.1d1d100.net/base/location/provinces');
//        dd($res->getBody()->getContents());
    }
}
