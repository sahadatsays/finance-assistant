<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Platform\Website\WebsiteContentService;
use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function __construct(private WebsiteContentService $content) {}

    public function __invoke(): View
    {
        $seo = $this->content->seo('pricing');

        return view('marketing.pricing', [
            'seo' => [
                'title' => $seo['title'],
                'description' => $seo['description'],
            ],
            'plans' => $this->content->activePlans(),
            'featureLabels' => config('marketing.feature_labels'),
            'faq' => $this->content->activeFaqs('pricing'),
        ]);
    }
}
