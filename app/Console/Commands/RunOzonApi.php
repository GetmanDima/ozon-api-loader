<?php

namespace App\Console\Commands;

use App\Services\Ozon\ApiService;
use Illuminate\Console\Command;

class RunOzonApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozon:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run ozon api loading';

    /**
     * Execute the console command.
     *
     */
    public function handle(ApiService $service)
    {
        $clientId1 = env('OZON_CLIENT_ID_1');
        $clientApiKey1 = env('OZON_API_KEY_1');
        $clientId2 = env('OZON_CLIENT_ID_2');
        $clientApiKey2 = env('OZON_API_KEY_2');

        if ($clientId1 && $clientApiKey1) {
            $service->setCredentials($clientId1, $clientApiKey1);
            $service->handle();
        }

        if ($clientId2 && $clientApiKey2) {
            $service->setCredentials($clientId2, $clientApiKey2);
            $service->handle();
        }
    }
}
