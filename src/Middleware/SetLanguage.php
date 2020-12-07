<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-07 19:07:12
 * @Description  : 設定語言
 */

namespace Ifantace\LaravelCommon\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLanguage
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
        if ($request->has("language")) {
            switch ($request->input("language")) {
                case "en":
                case "english":
                    App::setlocale("en");
                    break;
                case "chinese":
                case "zh":
                case "zh_tw":
                default:
                    App::setlocale("zh_TW");
                    break;
            }
        }
        return $next($request);
    }
}
