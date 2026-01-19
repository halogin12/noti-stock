<?php

namespace Halogin\NotiStock\Console\Commands;

use Illuminate\Http\Client\HttpClientException;
use Halogin\NotiStock\Models\StockShare;
use Halogin\NotiStock\Models\StockSharePriceHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Carbon\Carbon;


class CloneStockShare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clone-stock-shase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StockShare::chunkById(10, function ($items) {
            foreach ($items as $item) {
                $url = 'https://api2.simplize.vn/api/historical/quote/prices/' . $item->code;
                $param = [
                    'page' => 0,
                    'size' => 200
                ];

                // $url = 'https://api2.simplize.vn/api/historical/quote/' . $item->code;
                // $param = [];
                
                $datas = $this->curlApi($url, $param);

                $dataHistories = [];

                foreach ($datas as $data) {
                    $dataHistories = [
                        'stock_share_id' => $item->id,
                        'date' => Carbon::createFromTimestamp($data['date']),
                        'price_close' => $data['priceClose'],
                        'price_open' => $data['priceOpen'],
                        'price_high' => $data['priceHigh'],
                        'price_low' => $data['priceLow'],
                    ];

                    StockSharePriceHistory::query()->updateOrCreate([
                        'stock_share_id' => $item->id,
                        'date' => Carbon::createFromTimestamp($data['date'])
                    ], $dataHistories);
                }

                // StockSharePriceHistory::query()->insert($dataHistories);
            }
        });
        
    }

    public function curlApi($url, $param = null): array
    {
        try {
            $response = $param ? Http::get($url, $param) : Http::get($url);

            $response->throw();

            $data = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);

        } catch (HttpClientException $httpClientException) {
            throw new RuntimeException('API Error: ' . $httpClientException->getMessage(), $httpClientException->getCode(), $httpClientException);
        }

        return $data['data'];
    }
}
