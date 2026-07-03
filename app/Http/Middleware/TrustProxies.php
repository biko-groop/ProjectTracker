<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Railway (and most PaaS) ينهي TLS عند بروكسي ويمرّر الطلب عبر HTTP مع
     * X-Forwarded-Proto: https. الثقة بالبروكسي تجعل Laravel يكتشف HTTPS
     * فيولّد روابط الأصول (CSS/JS) بـ https ويمنع حجب المحتوى المختلط.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
