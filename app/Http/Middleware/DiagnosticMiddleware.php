<?php

namespace App\Http\Middleware;

use Closure;
use App\Utils\Helper;

class DiagnosticMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request request
     * @param \Closure                 $next    next
     * 
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the response
        $response = $next($request);
        

        $content = Helper::isJson($response->getContent(), true);
        if ($content && (is_array($content) OR is_object($content)) ) {
            // Calculate execution time
            $executionTime = microtime(true) - LARAVEL_START;

            $query_diagnostic = app('db')->getQueryLog();
            // dd($query_diagnostic);
            if (!config('app.debug')) {
                foreach ($query_diagnostic as $key => $val) {
                    $query_diagnostic[$key] = $val['time'].' ms';
                }
            }

            // I assume you're using valid json in your responses
            // Then I manipulate them below
            if (config('app.debug')) {
                $all_request = $request->all();
                if ($all_request) {
                    $content = ['request'=>$all_request] + $content;
                }
            }
            $content = $content + ['diagnostic'=>[
                'runtime' => number_format(round($executionTime*1000, 4), 4).' ms',
                'memoryusage' => Helper::convertSize(memory_get_usage(true)),
                'query' => $query_diagnostic
            ]];

            // dd($content);
            // Change the content of your response
            $response->setContent(json_encode($content));
        }

        // Return the response
        return $response;
    }
}
