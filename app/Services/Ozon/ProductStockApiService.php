<?php

namespace App\Services\Ozon;

use App\Models\Ozon\Product;
use App\Models\Ozon\ProductLastId;
use App\Models\Ozon\Stock;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;

class ProductStockApiService extends BaseApiService
{
    private const FETCH_LIMIT = 1000;

    public function handle()
    {
        try {
            $this->log->info('Start products with stocks. Client Id: ' . $this->clientId);

            $lastId = $this->getLatestLastId();
            $lastIdValue = $lastId ? $lastId['last_id'] : '';

            ['products' => $products, 'lastId' => $newLastIdValue] = $this->fetchData($lastIdValue);

            if (count($products) === 0) {
                $this->log->info('New products with stocks not found. ProductLastId: ' . $lastIdValue);
            }

            DB::transaction(function () use ($products, $lastIdValue, $newLastIdValue) {
                if ($lastIdValue !== $newLastIdValue) {
                    $this->insertLastId($newLastIdValue);
                }

                $this->insertData($products);
            });

            $this->log->info('Finish products with stocks');
        } catch (QueryException $e) {
            $this->logQueryError($e);
        } catch (ConnectionException $e) {
            $this->logConnectionError($e);
        } catch (\ErrorException $e) {
            $this->logError($e);
        }
    }

    private function getLatestLastId(): ?ProductLastId
    {
        return ProductLastId::latest('created_at')->where('client_id', '=', $this->clientId)->first();
    }

    /**
     * @throws ConnectionException
     */
    private function fetchData(string $lastId): array
    {
        $newLastId = $lastId;
        $currentCount = 0;
        $products = [];

        do {
            $result = $this->makeRequest()->post('/v3/product/info/stocks', [
                'filter' => [
                    'visibility' => 'ALL',
                ],
                'limit' => self::FETCH_LIMIT,
                'last_id' => $newLastId
            ])->json()['result'];

            $products = array_merge($products, $result['items']);

            if ($result['last_id'] !== '') {
                $newLastId = $result['last_id'];
            }

            $totalCount = $result['total'];
            $currentCount += self::FETCH_LIMIT;
        } while ($currentCount < $totalCount);

        return [
            'products' => $products,
            'lastId' => $newLastId
        ];
    }

    private function insertLastId(string $lastId)
    {
        ProductLastId::create(['last_id' => $lastId, 'client_id' => $this->clientId]);
    }

    /**
     * @throws QueryException
     */
    private function insertData(array $products)
    {
        foreach ($products as $product) {
            $productModel = Product::create([
                'product_id' => $product['product_id'],
                'offer_id' => $product['offer_id'],
                'client_id' => $this->clientId
            ]);

            $stocks = array_map(function ($stock)  {
                return new Stock([
                    'type' => $stock['type'],
                    'present' => $stock['present'],
                    'reserved' => $stock['reserved']
                ]);
            }, $product['stocks']);

            if (count($stocks) > 0) {
                $productModel->stocks()->saveMany($stocks);
            }
        }
    }
}
