<?php

namespace App\Http\Controllers\AngelData;

use App\Http\Controllers\Controller;
use App\Services\AngelOneService;
use App\Services\EquityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AngelController extends Controller
{
    public function login(AngelOneService $angel): JsonResponse
    {
        try {
            $data = $angel->login();
            return response()->json([
                'status' => true,
                'message' => 'Logged in',
                'data' => [
                    'jwt' => \Cache::get('angel_jwt'),
                    'feed' => \Cache::get('angel_feed'),
                    'raw' => $data,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function history(Request $request, AngelOneService $angel): JsonResponse
    {
        $symbol = $request->query('symbol', config('services.angel.default_symbol', '99926000'));
        $interval = strtoupper($request->query('interval', 'FIFTEEN_MINUTE'));
        $from = $request->query('from');
        $to = $request->query('to');

        $res = $angel->historical($symbol, $interval, $from, $to);

        if (empty($res['status'])) {
            return response()->json($res, 400);
        }

        return response()->json([
            'status' => true,
            'message' => $res['message'] ?? 'OK',
            'data' => $res['data'] ?? [],
        ]);
    }



    /**
     * Get Top Gainers and Losers (Derivatives Segment)
     */
    public function gainersLosers(Request $request, AngelOneService $angel): JsonResponse
    {
        $input = strtoupper($request->query('datatype', 'GAINERS'));
        
        $map = [
            'GAINERS'    => 'PercPriceGainers',
            'LOSERS'     => 'PercPriceLosers',
            'OI_GAINERS' => 'PercOIGainers',
            'OI_LOSERS'  => 'PercOILosers',
        ];

        $datatype = $map[$input] ?? $input;

        $exchange = strtoupper($request->query('exchange', 'NSE'));

        $expirytype = strtoupper($request->query('expirytype', 'NEAR'));

        $res = $angel->gainersLosers($datatype, $exchange, $expirytype);

        $statusCode = empty($res['status']) ? 400 : 200;
        return response()->json($res, $statusCode);
    }
    
    
    public function getIndices(AngelOneService $angel): JsonResponse
    {
        // NSE Indices Tokens
        $nseTokens = [
            // Broad Market
            '99926000', // Nifty 50
            '99926004', // Nifty Midcap 50
            '99926009', // Nifty Bank
            '99926037', // Nifty Fin Service

            // Sectoral Indices
            '99926002', // Nifty Auto
            '99926005', // Nifty FMCG
            '99926006', // Nifty IT
            '99926007', // Nifty Media
            '99926008', // Nifty Metal
            '99926010', // Nifty Pharma
            '99926011', // Nifty Private Bank
            '99926012', // Nifty PSU Bank
            '99926013', // Nifty Realty
            '99926016', // Nifty Consumer Durables
            '99926017', // Nifty Oil & Gas
            '99926018', // Nifty Healthcare
            
            // Others often available
            '99926019', // Nifty India Consumption
            '99926020', // Nifty CPSE
            '99926021', // Nifty Infrastructure
            '99926022', // Nifty Energy
            '99926025', // Nifty Commodities
        ];
        
        // BSE Indices
        $bseTokens = [
            '99919000'  // Sensex
        ];

        try {
            $nseData = $angel->quote($nseTokens, 'FULL', 'NSE');
            $bseData = $angel->quote($bseTokens, 'FULL', 'BSE');

            $mergedFetched = [];

            if (!empty($nseData['status']) && !empty($nseData['data'])) {
                $raw = $nseData['data']['fetched'] ?? ($nseData['data'] ?? []);
                if (isset($raw['symbolToken'])) $raw = [$raw];
                $mergedFetched = array_merge($mergedFetched, $raw);
            }
            

            if (!empty($bseData['status']) && !empty($bseData['data'])) {
                $raw = $bseData['data']['fetched'] ?? ($bseData['data'] ?? []);
                if (isset($raw['symbolToken'])) $raw = [$raw];
                $mergedFetched = array_merge($mergedFetched, $raw);
            }

            return response()->json([
                'status' => true,
                'message' => 'SUCCESS',
                'data' => [
                    'fetched' => $mergedFetched,
                    'unfetched' => []
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get NIFTY 50 Top Stocks Quote Data (for Marquee)
     */
    public function nifty50Marquee(Request $request, AngelOneService $angel): JsonResponse
    {
        $nifty50Stocks = [
            '2885',  // RELIANCE
            '11536', // RELIANCE
            '1594',  // INFY
            '3045',  // SBIN
            '1660',  // HDFCBANK
            '1333',  // HINDUNILVR
            '10999', // TCS
            '317',   // AXISBANK
            '3456',  // ICICIBANK
            '11483', // LT
            '2475',  // ITC
            '3506',  // KOTAKBANK
            '3351',  // BAJFINANCE
            '4963',  // MARUTI
            '881',   // BHARTIARTL
            '2031',  // HCLTECH
        ];

        try {
            $res = $angel->quote($nifty50Stocks, 'FULL', 'NSE');

            if (empty($res['status']) || empty($res['data'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to fetch NIFTY 50 marquee data'
                ], 400);
            }

            $fetched = $res['data']['fetched'] ?? ($res['data'] ?? []);
            
            if (isset($fetched['symbolToken'])) {
                $fetched = [$fetched];
            }

            $formatted = [];

            foreach ($fetched as $item) {
                $ltp = (float) ($item['ltp'] ?? 0);
                
                $prev = (float) ($item['close'] ?? $ltp);

                if ($prev > 0) {
                    $changePercent = round((($ltp - $prev) / $prev) * 100, 2);
                } else {
                    $changePercent = 0.00;
                }

                $formatted[] = [
                    'symbol' => $item['tradingSymbol'] ?? '',
                    'ltp'    => $ltp,
                    'change' => $changePercent,
                    'trend'  => $changePercent >= 0 ? 'UP' : 'DOWN',
                ];
            }

            return response()->json([
                'status' => true,
                'message' => 'SUCCESS',
                'data' => $formatted
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch 52 Week High/Low Data for specific symbols
     */
    public function fetch52WeekHighLowData(Request $request, AngelOneService $angel): JsonResponse
    {
        $single = $request->query('symbol');
        $multi = $request->query('symbols');
        $exchange = $request->query('exchange', 'NSE');

        $symbols = [];
        if (!empty($multi)) {
            $symbols = is_array($multi) ? $multi : array_filter(array_map('trim', explode(',', (string)$multi)));
        } elseif (!empty($single)) {
            $symbols = [trim((string)$single)];
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please provide a symbol or comma-separated symbols.'
            ], 400);
        }

        $res = $angel->quote($symbols, 'FULL', $exchange);

        if (empty($res['status']) || empty($res['data'])) {
            return response()->json([
                'status' => false,
                'message' => $res['message'] ?? 'Failed to fetch data from broker',
            ], 400);
        }

        // Robust data extraction
        $rawFetched = $res['data']['fetched'] ?? ($res['data'] ?? []);
        if (isset($rawFetched['symbolToken'])) {
            $rawFetched = [$rawFetched];
        }
        
        $formattedData = [];

        foreach ($rawFetched as $item) {
            $formattedData[] = [
                'symbolToken'   => $item['symbolToken'] ?? null,
                'tradingSymbol' => $item['tradingSymbol'] ?? null,
                'ltp'           => $item['ltp'] ?? 0,
                '52_week_high'  => $item['high52'] ?? ($item['52WeekHigh'] ?? null),
                '52_week_low'   => $item['low52'] ?? ($item['52WeekLow'] ?? null),
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => $formattedData,
            'unfetched' => $res['data']['unfetched'] ?? []
        ]);
    }
    
    public function wsToken(AngelOneService $angel): JsonResponse
    {
        $res = $angel->wsToken();
        $statusCode = empty($res['status']) ? 400 : 200;
        return response()->json($res, $statusCode);
    }

    public function dashboard()
    {
        return view('dashboard');
    }


public function searchNames(Request $request, AngelOneService $angel): JsonResponse
{
    try {
        $query = $request->query('query', '');
        $exchange = $request->query('exchange', 'NSE');

        if (strlen($query) < 2) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $names = $angel->searchSymbolNames($query, $exchange);
        return response()->json(['status' => true, 'data' => $names]);
    } catch (\Exception $e) {
        \Log::error('searchNames error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Server error', 'data' => []], 500);
    }
}

public function getExpiries(Request $request, AngelOneService $angel): JsonResponse
{
    try {
        $name = $request->query('name', '');
        $exchange = $request->query('exchange', 'NSE');

        if (!$name) {
            return response()->json(['status' => true, 'data' => []]);
        }

        $expiries = $angel->getExpiriesForSymbol($name, $exchange);
        return response()->json(['status' => true, 'data' => $expiries]);
    } catch (\Exception $e) {
        \Log::error('getExpiries error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Server error', 'data' => []], 500);
    }
}

public function getStrikes(Request $request, AngelOneService $angel): JsonResponse
{
    try {
        $name = $request->query('name', '');
        $expiry = $request->query('expiry', '');
        $exchange = $request->query('exchange', 'NSE');

        if (!$name || !$expiry) {
            return response()->json(['status' => false, 'message' => 'Missing parameters', 'data' => []], 400);
        }

        $strikes = $angel->getStrikesForSymbol($name, $expiry, $exchange);
        return response()->json(['status' => true, 'data' => $strikes]);
    } catch (\Exception $e) {
        \Log::error('getStrikes error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Server error', 'data' => []], 500);
    }
}

public function findToken(Request $request, AngelOneService $angel): JsonResponse
{
    try {
        $name = $request->query('name');
        $exchange = $request->query('exchange', 'NSE');
        $expiry = $request->query('expiry');
        $type = $request->query('type', 'future');
        $strike = $request->query('strike');
        $right = $request->query('right');

        $scrip = $angel->findScripToken($name, $exchange, $expiry, $type, $strike, $right);

        if (!$scrip) {
            return response()->json(['status' => false, 'message' => 'Contract not found', 'data' => null], 404);
        }

        $token = $scrip->symboltoken ?? $scrip->symbolToken ?? $scrip->token ?? $scrip->symbol_token ?? null;
        $symbol = $scrip->symbol ?? $scrip->tradingSymbol ?? $scrip->tradingsymbol ?? $scrip->tradingsymbol ?? null;

        $out = [
            'token' => (string)$token,
            'symbol' => (string)$symbol,
            'name' => $scrip->name ?? null,
            'expiry' => $scrip->expiry ?? null,
            'strike' => $scrip->strike ?? null,
            'instrumenttype' => $scrip->instrumenttype ?? null,
        ];

        return response()->json(['status' => true, 'data' => $out]);
    } catch (\Exception $e) {
        \Log::error('findToken error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
    }
}

public function quote(Request $request, AngelOneService $angel): JsonResponse
{
    try {
        $symbol = $request->query('symbol');
        $exchange = $request->query('exchange', 'NSE');

        if (!$symbol) {
            return response()->json(['status' => false, 'message' => 'Symbol required', 'data' => null], 400);
        }

        $res = $angel->quote([$symbol], 'FULL', $exchange);

        if (empty($res) || empty($res['status'])) {
            return response()->json(['status' => false, 'message' => $res['message'] ?? 'Failed to fetch quote', 'data' => null], 400);
        }

        return response()->json(['status' => true, 'data' => $res['data'] ?? $res]);
    } catch (\Exception $e) {
        \Log::error('quote error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' =>'Server error: '. $e->getMessage()], 500);
    }
}


public function findEquityToken(Request $request, EquityService $equityService): JsonResponse
    {
        try {
            $name = $request->query('name');
            $exchange = $request->query('exchange', 'NSE');

            if (!$name) {
                return response()->json(['status' => false, 'message' => 'Stock name is required'], 400);
            }

            $scrip = $equityService->findEquityToken($name, $exchange);

            if (!$scrip) {
                return response()->json(['status' => false, 'message' => 'Equity token not found for ' . $name], 404);
            }

            // Normalize data
            $token = $scrip->symboltoken ?? $scrip->token ?? null;
            $symbol = $scrip->symbol ?? $scrip->tradingSymbol ?? $scrip->name;

            return response()->json([
                'status' => true,
                'data' => [
                    'token' => (string)$token,
                    'symbol' => (string)$symbol,
                    'name' => $scrip->name,
                    'exch_seg' => $scrip->exch_seg
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('findEquityToken Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * Dedicated Endpoint for Equity Name Search (Autocomplete)
     */
    public function searchEquityNames(Request $request, EquityService $equityService): JsonResponse
    {
        try {
            $query = $request->query('query', '');
            $exchange = $request->query('exchange', 'NSE');

            $names = $equityService->searchEquityNames($query, $exchange);
            
            return response()->json(['status' => true, 'data' => $names]);
        } catch (\Exception $e) {
            Log::error('searchEquityNames Error: ' . $e->getMessage());
            return response()->json(['status' => false, 'data' => []], 500);
        }
    }

}