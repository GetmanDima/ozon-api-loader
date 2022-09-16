<?php

namespace App\Services\Ozon;

use App\Models\Ozon\AnalyticsData;
use App\Models\Ozon\Fbo;
use App\Models\Ozon\FboProduct;
use App\Models\Ozon\FinancialDataProduct;
use App\Models\Ozon\FinancialDataProductAction;
use App\Models\Ozon\FinancialDataProductPicking;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FboApiService extends BaseApiService
{
    private const RFC_MICRO_TIME_FORMAT = "Y-m-d\TH:i:s.u\Z";
    private const MICRO_TIME_FORMAT = "Y-m-d\TH:i:s.u";
    private const FETCH_LIMIT = 1000;
    private const MAX_FETCH_COUNT = 10000;

    public function handle()
    {
        $defaultDateFrom = (new Carbon())->subDay();

        try {
            $this->log->info('Start fbo. Client Id: ' . $this->clientId);

            $latestInProcessAt = $this->getLatestInProcessAt();
            $latestInProcessAt = $latestInProcessAt ? $latestInProcessAt->addMillisecond() : $defaultDateFrom;
            $fbos = $this->fetchData($latestInProcessAt);

            if (count($fbos) === 0) {
                $this->log->info('New fbos not found. Since: ' . $this->dateTimeToRfc($latestInProcessAt));
            }

            DB::transaction(function () use ($fbos)  {
                $this->insertData($fbos);
            });

            $this->log->info('Finish fbo');
        } catch (QueryException $e) {
            $this->logQueryError($e);
        } catch (ConnectionException $e) {
            $this->logConnectionError($e);
        } catch (\ErrorException $e) {
            $this->logError($e);
        }
    }

    /**
     * @throws QueryException
     */
    private function getLatestInProcessAt(): ?Carbon
    {
        $lastInProcessAt = Fbo::where('client_id', '=', $this->clientId)->max('in_process_at');
        return $lastInProcessAt ? new Carbon($lastInProcessAt) : null;
    }

    /**
     * @throws ConnectionException
     */
    private function fetchData(Carbon $sinceDateTime): array
    {
        $offset = 0;
        $fbos = [];
        $toDateTime = Carbon::now();

        do {
            $result = $this->makeRequest()->post('/v2/posting/fbo/list', [
                'dir' => 'ASC',
                'filter' => [
                    'since' => $this->dateTimeToRfc($sinceDateTime),
                    'to' => $this->dateTimeToRfc($toDateTime)
                ],
                'limit' => self::FETCH_LIMIT,
                'offset' => $offset,
                'with' => [
                    'analytics_data' => true,
                    'financial_data' => true
                ]
            ])->json()['result'];

            $this->log->info('Fetch data. Since: ' . $this->dateTimeToRfc($sinceDateTime) . ' Offset: ' . $offset);

            $offset += self::FETCH_LIMIT;
            $fbos = array_merge($fbos, $result);
        } while ($result && count($result) !== 0 && count($fbos) < self::MAX_FETCH_COUNT);

        return $fbos;
    }

    /**
     * @throws QueryException
     */
    private function insertData(array $fbos)
    {
        foreach ($fbos as $fbo) {
            $fboModel = Fbo::create([
                'cancel_reason_id' => $fbo['cancel_reason_id'],
                'created_at' => $this->dateTimeToString($this->rfcToDateTime($fbo['created_at'])),
                'in_process_at' => $this->dateTimeToString($this->rfcToDateTime($fbo['in_process_at'])),
                'order_id' => $fbo['order_id'],
                'order_number' => $fbo['order_number'],
                'posting_number' => $fbo['posting_number'],
                'status' => $fbo['status'],
                'client_id' => $this->clientId
            ]);

            $analyticsData = $fbo['analytics_data'];
            $financialData = $fbo['financial_data'];

            $this->insertProducts($fboModel, $fbo['products']);
            $this->insertAnalyticsData($fboModel, $analyticsData);
            $this->insertFinancialDataProducts($fboModel, $financialData['products']);
        }
    }

    private function insertProducts(Fbo $fbo, array $products)
    {
        $productModels = array_map(function ($product)  {
            return new FboProduct([
                'sku' => $product['sku'],
                'name' => $product['name'],
                'quantity' => $product['quantity'],
                'offer_id' => $product['offer_id'],
                'price' => $product['price'],
            ]);
        }, $products);

        if (count($productModels) > 0) {
            $fbo->products()->saveMany($productModels);
        }
    }

    /**
     * @throws QueryException
     */
    private function insertAnalyticsData(Fbo $fbo, array $analyticsData)
    {
        $analyticsDataModel = new AnalyticsData([
            'city' => $analyticsData['city'],
            'delivery_type' => $analyticsData['delivery_type'],
            'is_premium' => $analyticsData['is_premium'],
            'payment_type_group_name' => $analyticsData['payment_type_group_name'],
            'region' => $analyticsData['region'],
            'warehouse_id' => $analyticsData['warehouse_id'],
            'warehouse_name' => $analyticsData['warehouse_name'],
        ]);

        $fbo->analyticsData()->save($analyticsDataModel);
    }

    /**
     * @throws QueryException
     */
    private function insertFinancialDataProducts(Fbo $fbo, array $products)
    {
        $productModels = array_map(function ($product)  {
            return new FinancialDataProduct([
                'client_price' => $product['client_price'],
                'commission_amount' => $product['commission_amount'],
                'commission_percent' => $product['commission_percent'],
                'old_price' => $product['old_price'],
                'payout' => $product['payout'],
                'price' => $product['price'],
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'total_discount_percent' => $product['total_discount_percent'],
                'total_discount_value' => $product['total_discount_value'],
            ]);
        }, $products);

        if (count($productModels) > 0) {
            $fbo->financialDataProducts()->saveMany($productModels);
        }

        for ($i = 0; $i < count($products); $i++) {
            $this->insertFinancialDataProductActions($productModels[$i], $products[$i]['actions']);

            $picking = $products[$i]['picking'];

            if (!is_null($picking)) {
                $this->insertFinancialDataProductPicking($productModels[$i], $picking);
            }
        }
    }

    /**
     * @throws QueryException
     */
    private function insertFinancialDataProductActions(FinancialDataProduct $product, array $actions)
    {
        $actionModels = array_map(function ($action)  {
            return new FinancialDataProductAction([
                'name' => $action
            ]);
        }, $actions);

        if (count($actionModels) > 0) {
            $product->actions()->saveMany($actionModels);
        }
    }

    /**
     * @throws QueryException
     */
    private function insertFinancialDataProductPicking(FinancialDataProduct $product, array $picking)
    {
        $pickingModel = new FinancialDataProductPicking([
            'amount' => $picking['amount'],
            'moment' => $this->dateTimeToString($this->rfcToDateTime($picking['moment'])),
        ]);

        $product->picking()->save($pickingModel);
    }

    private function dateTimeToRfc(Carbon $dateTime): string
    {
        try {
            return $dateTime->format(self::RFC_MICRO_TIME_FORMAT);
        } catch (InvalidFormatException) {
            return $dateTime->format(\DateTimeInterface::RFC3339);
        }
    }

    private function rfcToDateTime(string $rfcDateTime): Carbon|false
    {
        try {
            return Carbon::createFromFormat(self::RFC_MICRO_TIME_FORMAT, $rfcDateTime);
        } catch (InvalidFormatException) {
            return Carbon::createFromFormat(\DateTimeInterface::RFC3339, $rfcDateTime);
        }
    }

    private  function dateTimeToString(Carbon $dateTime): string
    {
        try {
            return $dateTime->format(self::MICRO_TIME_FORMAT);
        } catch (InvalidFormatException) {
            return $dateTime->format(\DateTimeInterface::W3C);
        }
    }
}
