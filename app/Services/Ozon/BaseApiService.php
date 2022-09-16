<?php

namespace App\Services\Ozon;

use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

abstract class BaseApiService
{
    protected string $apiUrl;
    protected string $clientId;
    protected string $apiKey;
    protected LoggerInterface $log;

    abstract public function handle();

    public function __construct()
    {
        $this->apiUrl = env("OZON_API_URL");

        $this->log = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/ozon.log'),
        ]);
    }

    public function setCredentials(string $clientId, string $apiKey)
    {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
    }

    protected function makeRequest(): PendingRequest
    {
        return Http::acceptJson()->baseUrl($this->apiUrl)->withHeaders([
            'Client-Id' => $this->clientId,
            'Api-Key' => $this->apiKey
        ]);
    }

    protected function logQueryError(QueryException $e)
    {
        $this->log->error('Query error: ' . $e->getMessage());
    }

    protected function logConnectionError(ConnectionException $e)
    {
        $this->log->error('Connection error: ' . $e->getMessage());
    }

    protected function logError(\ErrorException $e)
    {
        $this->log->error('Error: ' . $e->getMessage() . ' Line: ' . $e->getLine());
    }
}
