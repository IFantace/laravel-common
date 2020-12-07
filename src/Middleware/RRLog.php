<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-07 16:48:29
 * @Description  : 紀錄Request and Response
 */

namespace Ifantace\LaravelCommon\Middleware;

use Closure;
use Ifantace\LaravelCommon\Traits\CommonTraits;
use Illuminate\Support\Facades\Log;

class RRLog
{
    use CommonTraits;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $event_uuid = $this->genUuid();
        Log::info(
            $this->createLogString(
                'Request',
                [
                    'Ip' => $request->ip(),
                    'Method' => $request->method(),
                    'Url' => $request->fullUrl(),
                    'User' => $this->getCurrentUserUuid(),
                    'Parameters' => $request->all()
                ],
                $event_uuid
            )
        );
        $request->request->add(['event_uuid' => $event_uuid]);

        /**
         * @var \Illuminate\Http\Response
         */
        $response = $next($request);
        $content = $response->getOriginalContent();
        if (!is_array($content)) {
            $content = $response->getContent();
        }
        Log::info(
            $this->createLogString(
                'Response',
                [
                    'StatusCode' => $response->getStatusCode(),
                    'Content' => $content

                ],
                $event_uuid
            )
        );
        return $response;
    }
}
