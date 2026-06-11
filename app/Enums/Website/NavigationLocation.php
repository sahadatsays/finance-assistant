<?php

namespace App\Enums\Website;

enum NavigationLocation: string
{
    case Header = 'header';
    case FooterProduct = 'footer_product';
    case FooterResources = 'footer_resources';
    case FooterCompany = 'footer_company';
    case FooterLegal = 'footer_legal';
}
