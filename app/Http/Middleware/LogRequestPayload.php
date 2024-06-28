<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogRequestPayload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Extract request details
        $method = $request->method();
        $url = $request->fullUrl();
        $headers = json_encode($request->header()); // Convert headers array to JSON string
        $payload = json_encode($request->all()); // Convert payload array to JSON string
        
        // Extract host from headers
        $host = $request->getHost();

        // Extract headers of interest
        $connection = $request->header('connection');
        $cacheControl = $request->header('cache-control');
        $secChUA = $request->header('sec-ch-ua');
        $secChUAMobile = $request->header('sec-ch-ua-mobile');
        $secChUAPlatform = $request->header('sec-ch-ua-platform');
        $upgradeInsecureRequests = $request->header('upgrade-insecure-requests');
        $userAgent = $request->header('user-agent');
        $secGPC = $request->header('sec-gpc');
        $acceptLanguage = $request->header('accept-language');
        $secFetchSite = $request->header('sec-fetch-site');
        $secFetchMode = $request->header('sec-fetch-mode');
        $secFetchUser = $request->header('sec-fetch-user');
        $secFetchDest = $request->header('sec-fetch-dest');
        $acceptEncoding = $request->header('accept-encoding');

        // Extract XSRF-TOKEN cookie value and ensure it's fully encoded
        $xsrfToken = $request->cookie('XSRF-TOKEN');
        $xsrfToken = mb_convert_encoding($xsrfToken, 'UTF-8', 'UTF-8');

        // Parse sec-ch-ua header into components
        $browserName = '';
        $browserVersion = '';

        if ($secChUA) {
            preg_match_all('/"([^"]+)";v="([^"]+)"/', $secChUA, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $type = $match[1];
                $version = $match[2];

                if ($type === 'Chromium') {
                    $browserName = 'Chromium';
                    $browserVersion = $version;
                    break; // Assuming Chromium version is unique and comes first
                }
            }
        }

        // Extract user-agent details
        $userAgentBrowser = '';
        $userAgentVersion = '';

        if ($userAgent) {
            // Example parsing for common user-agent formats (modify as per your specific needs)
            if (preg_match('/Chrome\/(\S+)/', $userAgent, $matches)) {
                $userAgentBrowser = 'Chrome';
                $userAgentVersion = $matches[1];
            } elseif (preg_match('/Firefox\/(\S+)/', $userAgent, $matches)) {
                $userAgentBrowser = 'Firefox';
                $userAgentVersion = $matches[1];
            } elseif (preg_match('/Safari\/(\S+)/', $userAgent, $matches)) {
                $userAgentBrowser = 'Safari';
                $userAgentVersion = $matches[1];
            } else {
                // Default to general user-agent string
                $userAgentBrowser = 'Other';
                $userAgentVersion = '';
            }
        }

        // Extract accept-encoding details
        $acceptEncodingBrowser = '';

        if ($acceptEncoding) {
            // Example parsing for accept-encoding values (modify as per your specific needs)
            if (strpos($acceptEncoding, 'gzip') !== false) {
                $acceptEncodingBrowser = 'gzip';
            } elseif (strpos($acceptEncoding, 'deflate') !== false) {
                $acceptEncodingBrowser = 'deflate';
            } elseif (strpos($acceptEncoding, 'br') !== false) {
                $acceptEncodingBrowser = 'br';
            } else {
                // Default to general accept-encoding value
                $acceptEncodingBrowser = 'other';
            }
        }

        // Remove "max-age=" and its value from cacheControl if it exists
        if ($cacheControl) {
            $cacheControl = preg_replace('/max-age=/', '', $cacheControl);
            $cacheControl = trim(preg_replace('/,\s*,/', ',', $cacheControl), ', ');
        }

        // Remove question mark from secChUAPlatform
        if ($secChUAMobile) {
            $secChUAMobile = str_replace('?', '', $secChUAMobile);
        }
        if ($secChUAPlatform) {
            $secChUAPlatform = str_replace('"', '', $secChUAPlatform);
        }
        if ($secFetchUser) {
            $secFetchUser = str_replace('?', '', $secFetchUser);
        }
        if ($acceptLanguage) {
            $acceptLanguage = str_replace('en-US,', '', $acceptLanguage);
        }
        // Prepare CSV data
        $csvData = [
            // now()->format('Y-m-d H:i:s'), 
            // $payload,
            // $method,
            $url,
            $host, // Add host to CSV data
            $connection ?? '', // Add connection header to CSV data
            $cacheControl ?? '', // Add cache-control header to CSV data
            $browserName,
            $browserVersion,
            $upgradeInsecureRequests ?? '', // Add upgrade-insecure-requests header to CSV data
            $userAgentBrowser,
            $userAgentVersion,
            $secChUAMobile ?? '', // Add sec-ch-ua-mobile header to CSV data
            $secChUAPlatform ?? '', // Add sec-ch-ua-platform header to CSV data
            $secGPC ?? '', // Add sec-gpc header to CSV data
            $acceptLanguage ?? '', // Add accept-language header to CSV data
            $secFetchSite ?? '', // Add sec-fetch-site header to CSV data
            $secFetchMode ?? '', // Add sec-fetch-mode header to CSV data
            $secFetchUser ?? '', // Add sec-fetch-user header to CSV data
            $secFetchDest ?? '', // Add sec-fetch-dest header to CSV data
            $acceptEncodingBrowser, // Add accept-encoding category to CSV data
            // $xsrfToken ?? '', // Add XSRF-TOKEN cookie value to CSV data
            // $headers,
        ];

        // CSV file path
        $csvFilePath = storage_path('logs/request_traffic.csv');

        // Write data to CSV file
        $file = fopen($csvFilePath, 'a');
        fputcsv($file, $csvData);
        fclose($file);

        return $next($request);
    }
}
