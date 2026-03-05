<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EquityService
{
    public function searchEquityNames(string $query, string $exchange = 'NSE'): array
    {
        if (strlen($query) < 2) return [];

        // Debug: Log what we are searching for
        // Check storage/logs/laravel.log to see this
        Log::info("Searching Equity: $query in $exchange");

        $q = DB::table('angel_scrips')
            ->where('name', 'LIKE', $query . '%')
            ->where('exch_seg', $exchange)
            // Fix: Handle BOTH NULL and Empty String for expiry
            ->where(function($q) {
                $q->whereNull('expiry')->orWhere('expiry', '=', '');
            })
            // Optimization: Equity instruments usually have instrumenttype as '' or 'EQ'
            ->where(function($q) {
                $q->where('instrumenttype', '')->orWhereNull('instrumenttype');
            });

        $results = $q->select('name')
            ->distinct()
            ->limit(15)
            ->pluck('name')
            ->toArray();

        return $results;
    }

    public function findEquityToken(string $name, string $exchange = 'NSE'): ?object
    {
        $scrip = DB::table('angel_scrips')
            ->where('name', $name)
            ->where('exch_seg', $exchange)
            ->where(function($q) {
                $q->whereNull('expiry')->orWhere('expiry', '=', '');
            })
            ->first();

        // Fallback: Try looser search if exact match fails
        if (!$scrip) {
            $scrip = DB::table('angel_scrips')
                ->where('symbol', 'LIKE', $name . '%') // Try matching symbol
                ->where('exch_seg', $exchange)
                ->where(function($q) {
                    $q->whereNull('expiry')->orWhere('expiry', '=', '');
                })
                ->first();
        }

        return $scrip;
    }
}