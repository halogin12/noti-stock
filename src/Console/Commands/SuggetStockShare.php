<?php

namespace Ducnm\NotiStock\Console\Commands;

use Ducnm\NotiStock\Models\StockShare;
use Ducnm\NotiStock\Helper\LogApp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SuggetStockShare extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sugget-stock-share';

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
        $dataNoti = [];
        StockShare::with('histories')->chunkById(10, function ($items) use (&$dataNoti) {
            foreach ($items as $item) {
                $histories = $item->histories()
                    ->orderBy('date', 'desc')
                    ->limit(100)
                    ->get()
                    ->reverse()
                    ->values();

                if ($histories->count() < 30) {
                    continue;
                }

                $closes = $histories->pluck('price_close')->toArray();

                $stoch = $this->stochasticRSI($closes);

                $lastK = end($stoch['smoothK']);
                $lastD = end($stoch['smoothD']);

                $this->info("Code: {$item->code} - K: {$lastK} - D: {$lastD}");
                Log::info("stoch: $item->code", ['K' => $lastK, 'D' => $lastD]);

                $dataNoti[] = [
                    "Code" => $item->code,
                    "K" =>  $lastK,
                    "D" => $lastD,
                ];
            }
        });

        $jsonPretty = json_encode(
            $dataNoti,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        LogApp::sendTele("<pre>" . htmlspecialchars($jsonPretty) . "</pre>");
    }

    /**
     * Tính RSI (Relative Strength Index) theo công thức Wilder
     *
     * @param array $closes  Mảng giá đóng cửa (theo thứ tự thời gian)
     * @param int   $period  Chu kỳ RSI (mặc định 14)
     * @return array         Mảng RSI, index tương ứng với closes (null nếu chưa đủ dữ liệu)
     */
    public function calculateRSI(array $closes, int $period = 14)
    {
        $count = count($closes);
        $rsi   = array_fill(0, $count, null);

        if ($count <= $period) {
            return $rsi;
        }

        $gains = [];
        $losses = [];

        // 1️⃣ Tính gain / loss
        for ($i = 1; $i < $count; $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[$i]  = $change > 0 ? $change : 0;
            $losses[$i] = $change < 0 ? abs($change) : 0;
        }

        // 2️⃣ Avg gain / loss ban đầu
        $avgGain = array_sum(array_slice($gains, 1, $period)) / $period;
        $avgLoss = array_sum(array_slice($losses, 1, $period)) / $period;

        // RSI tại vị trí period
        if ($avgLoss == 0) {
            $rsi[$period] = 100;
        } else {
            $rs = $avgGain / $avgLoss;
            $rsi[$period] = 100 - (100 / (1 + $rs));
        }

        // 3️⃣ Wilder smoothing cho các phiên tiếp theo
        for ($i = $period + 1; $i < $count; $i++) {
            $avgGain = (($avgGain * ($period - 1)) + $gains[$i]) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $losses[$i]) / $period;

            if ($avgLoss == 0) {
                $rsi[$i] = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsi[$i] = 100 - (100 / (1 + $rs));
            }
        }

        return $rsi;
    }

    public function stochasticRSI(
        array $closes,
        int $lengthRSI = 14,
        int $lengthStoch = 14,
        int $smoothK = 5,
        int $smoothD = 5
    ) {
        $rsi = $this->calculateRSI($closes, $lengthRSI);
        $count = count($closes);

        $stochRSI = $k = $d = array_fill(0, $count, null);

        for ($i = 0; $i < $count; $i++) {

            // ---- RAW STOCH RSI (%K) ----
            if ($i >= $lengthRSI + $lengthStoch) {
                $rsiSlice = array_slice($rsi, $i - $lengthStoch + 1, $lengthStoch);

                if (in_array(null, $rsiSlice, true)) continue;

                $min = min($rsiSlice);
                $max = max($rsiSlice);

                $stochRSI[$i] = ($max == $min)
                    ? 0
                    : 100 * (($rsi[$i] - $min) / ($max - $min));
            }

            // ---- SMOOTH K ----
            if ($i >= $lengthRSI + $lengthStoch + $smoothK - 1) {
                $kSlice = array_slice($stochRSI, $i - $smoothK + 1, $smoothK);
                if (!in_array(null, $kSlice, true)) {
                    $k[$i] = array_sum($kSlice) / $smoothK;
                }
            }

            // ---- SMOOTH D ----
            if ($i >= $lengthRSI + $lengthStoch + $smoothK + $smoothD - 2) {
                $dSlice = array_slice($k, $i - $smoothD + 1, $smoothD);
                if (!in_array(null, $dSlice, true)) {
                    $d[$i] = array_sum($dSlice) / $smoothD;
                }
            }
        }

        return [
            'rawK'    => $stochRSI, // %K gốc
            'smoothK' => $k,         // %K
            'smoothD' => $d,         // %D (TradingView)
        ];
    }
}
