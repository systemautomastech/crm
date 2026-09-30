<?php

namespace Automas\Pos\Traits;

use Automas\Pos\Models\PosDiscount;
use Carbon\Carbon;

trait getDiscountFromTrait
{
    /**
     * Get applicable discount for a product
     * Priority: Highest discount value wins
     */
    public function getApplicableDiscount($productId, $quantity = 1, $categoryId = null)
    {
        $today = Carbon::today();

        $discounts = PosDiscount::active()
            ->forCreator()
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('min_quantity', '<=', $quantity)
            ->where(function($query) use ($productId, $categoryId) {
                $query->whereHas('products', function($q) use ($productId) {
                    $q->where('product_id', $productId);
                })
                ->orWhere('category_id', $categoryId);
            })
            ->get();

        // Return discount with highest value
        return $discounts->sortByDesc(function($discount) {
            // For percentage, use the percentage value
            // For fixed, convert to percentage equivalent for comparison
            return $discount->discount_type === 'percentage' 
                ? $discount->discount_value 
                : 100; // Fixed discounts get priority
        })->first();
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscountAmount($price, $discount)
    {
        if (!$discount) {
            return 0;
        }

        if ($discount->discount_type === 'percentage') {
            return ($price * $discount->discount_value) / 100;
        }

        return $discount->discount_value;
    }
}
