<?php

namespace App\Services\Ozon;

class ApiService extends BaseApiService
{
    protected ProductStockApiService $productStockApiService;
    protected FboApiService $fboApiService;

    public function __construct(ProductStockApiService $productStockApiService, FboApiService $fboApiService)
    {
        parent::__construct();
        $this->productStockApiService = $productStockApiService;
        $this->fboApiService = $fboApiService;
    }

    public function handle() {
        $this->log->info('Start integration');

        $this->productStockApiService->setCredentials($this->clientId, $this->apiKey);
        $this->fboApiService->setCredentials($this->clientId, $this->apiKey);

        $this->productStockApiService->handle();
        $this->fboApiService->handle();

        $this->log->info('Finish integration');
    }
}
