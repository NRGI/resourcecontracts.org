<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * The language cookie was historically written with setcookie() in plain text.
     *
     * @var array<int, string>
     */
    protected $except = [
        'rc_admin_lang',
    ];
}
