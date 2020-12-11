<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-11 12:58:28
 * @Description  : 紀錄Request and Response
 */

namespace Ifantace\LaravelCommon\Middleware;

use Closure;
use Ifantace\LaravelCommon\Objects\CommonFunction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RRLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $event_code = $request->input('event_code');
        if ($event_code  === null) {
            $event_code = CommonFunction::generateRandomKey(8);
            $request->request->add(['event_code' => $event_code]);
        }
        $user = Auth::user();
        $request_log_string = CommonFunction::createLogString(
            'Request',
            [
                'Ip' => $request->ip(),
                'Method' => $request->method(),
                'Url' => $request->fullUrl(),
                'UserID' => $user == null ? null : $user->id,
                'Parameters' => $request->all()
            ],
            $event_code
        );
        if (strlen($request_log_string) > 2048) {
            $request_log_string = CommonFunction::createLogString(
                'Request',
                [
                    'Ip' => $request->ip(),
                    'Method' => $request->method(),
                    'Url' => $request->fullUrl(),
                    'UserID' => $user == null ? null : $user->id,
                    'Parameters' => "Too long"
                ],
                $event_code
            );
        }
        // Log::info('Request', ['request' => $request, 'event_code' => $event_code]);
        Log::info($request_log_string);

        /**
         * @var \Illuminate\Http\Response
         */
        $response = $next($request);
        $content = $response->getOriginalContent();
        if (!is_array($content)) {
            $content = $response->getContent();
        }
        $response_log_string = CommonFunction::createLogString(
            'Response',
            [
                'StatusCode' => $response->getStatusCode(),
                'Content' => $content

            ],
            $event_code
        );
        if (strlen($response_log_string) > 2048) {
            $response_log_string =  CommonFunction::createLogString(
                'Response',
                [
                    'StatusCode' => $response->getStatusCode(),
                    'Content' => "Too long"

                ],
                $event_code
            );
        }
        // Log::info('Response', ['response' => $response, 'event_code' => $event_code]);
        Log::info($response_log_string);
        return $response;
    }
}
