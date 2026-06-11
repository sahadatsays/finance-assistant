<?php

namespace App\Enums;

enum LoginMethod: string
{
    case Password = 'password';
    case Passkey = 'passkey';
    case TwoFactor = 'two_factor';
    case ApiToken = 'api_token';
}
