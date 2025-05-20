<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Only enforce canonical domain in production environment
        if (app()->environment('production')) {
            // Define the canonical domain
            $canonicalDomain = 'dofus-challenges.fr';

            // Get the current host
            $host = $request->getHost();

            // Check if we need to redirect
            if ($host !== $canonicalDomain) {
                // Build the redirect URL
                $scheme = $request->getScheme();
                $path = $request->getRequestUri();

                // Remove query parameters if they exist
                if (strpos($path, '?') !== false) {
                    $path = substr($path, 0, strpos($path, '?'));
                }

                // Remove trailing slashes
                $path = rtrim($path, '/');

                // Create the canonical URL
                $canonicalUrl = $scheme . '://' . $canonicalDomain . $path;

                // Redirect to the canonical URL with a 301 (permanent) redirect
                return redirect($canonicalUrl, 301);
            }
        }

        return $next($request);
    }
}
