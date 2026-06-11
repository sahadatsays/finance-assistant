<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Platform\Plan;
use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function __invoke(): View
    {
        return view('marketing.pricing', [
            'seo' => config('marketing.seo.pricing'),
            'plans' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price_monthly')
                ->get(),
            'featureLabels' => config('marketing.feature_labels'),
            'faq' => config('marketing.pricing_faq'),
        ]);
    }
}
