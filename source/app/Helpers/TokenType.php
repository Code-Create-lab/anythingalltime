<?php

namespace App\Helpers;

enum TokenType: string
{
    case User = 'User';
    case Store = 'Store';
    case Driver = 'Driver';
}
