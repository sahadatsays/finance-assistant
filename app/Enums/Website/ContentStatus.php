<?php

namespace App\Enums\Website;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
