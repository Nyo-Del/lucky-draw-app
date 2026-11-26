<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LuckyDrawController extends Controller
{
    /**
     * Dashboard / Hub
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * 1st: Spin Wheel
     */
    public function spinwheel()
    {
        return view('spinwheel');
    }

    /**
     * 2nd: Dice
     */
    public function dice()
    {
        return view('dice');
    }

    /**
     * 3rd: Balancer (Total pool max distribution e.g. 10,000)
     */
    public function balancer()
    {
        return view('balancer');
    }

    /**
     * 4th: Unbalancer (Independent draws 1 to 10,000 based on probability)
     */
    public function unbalancer()
    {
        return view('unbalancer');
    }

    /**
     * API for Balancer Draw calculation
     */
    public function apiBalancerDraw(Request $request)
    {
        $maxPool = (float) $request->input('max_pool', 10000);
        $remainingPool = (float) $request->input('remaining_pool', $maxPool);
        $drawerName = trim($request->input('drawer_name', 'Drawer #' . ($request->input('draw_count', 0) + 1)));
        $expectedDrawers = (int) $request->input('expected_drawers', 10);
        $drawCount = (int) $request->input('draw_count', 0);
        $distribution = $request->input('distribution', 'red_envelope'); // red_envelope, weighted, uniform

        if ($remainingPool <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'The pool of ' . number_format($maxPool) . ' has been completely exhausted!',
                'remaining' => 0,
            ], 400);
        }

        $drawsLeft = max(1, $expectedDrawers - $drawCount);

        if ($drawsLeft === 1) {
            // Last drawer takes all remaining pool
            $amount = round($remainingPool, 2);
        } else {
            if ($distribution === 'red_envelope') {
                // WeChat / Red Packet Algorithm:
                // min is 1, max is (remaining / drawsLeft) * 2
                $avg = $remainingPool / $drawsLeft;
                $maxPossible = min($remainingPool - ($drawsLeft - 1), $avg * 2);
                $minPossible = 1;
                if ($maxPossible < $minPossible) {
                    $amount = round($remainingPool / $drawsLeft, 2);
                } else {
                    $amount = round(mt_rand($minPossible * 100, (int)($maxPossible * 100)) / 100, 2);
                }
            } elseif ($distribution === 'weighted') {
                // Tiered probabilistic distribution:
                // 60% chance: 1% - 5% of remaining
                // 30% chance: 5% - 20% of remaining
                // 10% chance: 20% - 45% of remaining
                $roll = mt_rand(1, 100);
                if ($roll <= 60) {
                    $pct = mt_rand(10, 50) / 1000;
                } elseif ($roll <= 90) {
                    $pct = mt_rand(50, 200) / 1000;
                } else {
                    $pct = mt_rand(200, 450) / 1000;
                }
                $amount = round(max(1, min($remainingPool, $remainingPool * $pct)), 2);
            } else {
                // Uniform random slice
                $amount = round(mt_rand(100, (int) min(1000000, ($remainingPool / $drawsLeft) * 200)) / 100, 2);
            }
        }

        $amount = min($amount, $remainingPool);
        $amount = max(1, $amount);
        if ($drawsLeft === 1) {
            $amount = $remainingPool;
        }

        $newRemaining = round($remainingPool - $amount, 2);

        return response()->json([
            'success' => true,
            'drawer_name' => $drawerName,
            'amount' => $amount,
            'remaining_pool' => $newRemaining,
            'max_pool' => $maxPool,
            'percentage_of_pool' => round(($amount / $maxPool) * 100, 2),
            'draw_number' => $drawCount + 1,
        ]);
    }

    /**
     * API for Unbalancer Draw calculation
     */
    public function apiUnbalancerDraw(Request $request)
    {
        $max = (int) $request->input('max', 10000);
        $drawerName = trim($request->input('drawer_name', 'Drawer #' . ($request->input('draw_count', 0) + 1)));
        $model = $request->input('model', 'tiered'); // tiered, normal, uniform

        if ($max < 1) {
            $max = 10000;
        }

        $tier = 'Common';
        $tierColor = 'emerald';

        if ($model === 'uniform') {
            $amount = mt_rand(1, $max);
            $pct = ($amount / $max) * 100;
            if ($pct >= 90) { $tier = 'Legendary'; $tierColor = 'amber'; }
            elseif ($pct >= 70) { $tier = 'Epic'; $tierColor = 'purple'; }
            elseif ($pct >= 40) { $tier = 'Rare'; $tierColor = 'blue'; }
            else { $tier = 'Common'; $tierColor = 'emerald'; }
        } elseif ($model === 'normal') {
            // Box-Muller transform for normal distribution centered at max/2
            $u1 = mt_rand(1, 10000) / 10000;
            $u2 = mt_rand(1, 10000) / 10000;
            $z = sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
            $mean = $max / 2;
            $stdDev = $max / 6;
            $amount = round($mean + ($z * $stdDev));
            $amount = max(1, min($max, $amount));

            $pct = ($amount / $max) * 100;
            if ($pct >= 85 || $pct <= 15) { $tier = 'Extreme / Rare'; $tierColor = 'purple'; }
            else { $tier = 'Standard'; $tierColor = 'blue'; }
        } else {
            // Default: Realistic Tiered Probability Curve (like mobile games / lotteries)
            // 70% Common: 1 to 10% of max
            // 20% Uncommon: 10% to 35% of max
            // 8% Rare: 35% to 75% of max
            // 2% Jackpot: 75% to 100% of max
            $roll = mt_rand(1, 1000) / 10; // 0.1 to 100.0%

            if ($roll <= 70.0) {
                // 70% chance -> Common
                $minRange = 1;
                $maxRange = (int) max(1, $max * 0.10);
                $tier = 'Common';
                $tierColor = 'emerald';
            } elseif ($roll <= 90.0) {
                // 20% chance -> Uncommon
                $minRange = (int) ($max * 0.10) + 1;
                $maxRange = (int) max($minRange, $max * 0.35);
                $tier = 'Uncommon';
                $tierColor = 'blue';
            } elseif ($roll <= 98.0) {
                // 8% chance -> Rare Epic
                $minRange = (int) ($max * 0.35) + 1;
                $maxRange = (int) max($minRange, $max * 0.75);
                $tier = 'Epic';
                $tierColor = 'purple';
            } else {
                // 2% chance -> Grand Jackpot!
                $minRange = (int) ($max * 0.75) + 1;
                $maxRange = $max;
                $tier = 'JACKPOT 🔥';
                $tierColor = 'amber';
            }

            $amount = mt_rand($minRange, $maxRange);
        }

        return response()->json([
            'success' => true,
            'drawer_name' => $drawerName,
            'amount' => $amount,
            'max' => $max,
            'tier' => $tier,
            'tier_color' => $tierColor,
            'percentage_of_max' => round(($amount / $max) * 100, 2),
            'draw_number' => (int) $request->input('draw_count', 0) + 1,
        ]);
    }
}
