<?php

namespace App\Http\Controllers;

use App\Models\Embed;
use App\Services\EmbedService;
use Illuminate\Http\Response;

class EmbedController extends Controller
{
    /**
     * Serve the raw embedded page. The CSP sandbox header keeps it on an
     * opaque origin even when the URL is opened directly, outside the iframe.
     * frame-ancestors lets our own pages frame it; browsers give it precedence
     * over an X-Frame-Options header a proxy or host may add in production.
     */
    public function raw(Embed $embed, EmbedService $service): Response
    {
        return response($service->document($embed))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Security-Policy', 'sandbox '.EmbedService::SANDBOX."; frame-ancestors 'self'")
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
