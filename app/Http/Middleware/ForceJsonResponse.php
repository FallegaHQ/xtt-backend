<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response{
        $request->headers->set('Accept', 'application/json');

        // Process the request
        $response = $next($request);

        // Double check
        if(!$response->headers->has('Content-Type')){
            $response->headers->set('Content-Type', 'application/json');
        }

        // This allows ?format=jsonp for JSONP responses or maybe ?format=xml for XML, etc.
        if($request->has('format')){
            $format = $request->input('format');

            if($format === 'jsonp' && $request->has('callback')){
                $callback = $request->input('callback');

                // If the response is already JSON, convert it to JSONP
                if($this->isJsonResponse($response)){
                    $content      = $response->getContent();
                    $jsonpContent = "{$callback}({$content})";

                    $response->setContent($jsonpContent);
                    $response->headers->set('Content-Type', 'application/javascript');
                }
            }
        }

        // Add common API headers if this is a JSON response
        if($this->isJsonResponse($response)){
            // Add cache control headers for API responses
            $response->headers->set('Cache-Control', 'no-cache, private');
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }

        return $response;
    }

    protected function isJsonResponse(Response $response): bool{
        $contentType = $response->headers->get('Content-Type');

        return $contentType && str_contains($contentType, 'application/json');
    }
}
