<?php

/*
 * @Author: Austin
 * @Date: 2020-01-09 18:18:25
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-07 19:07:43
 */

namespace Ifantace\LaravelCommon\Services;

use Ifantace\LaravelCommon\Traits\CommonTraits;
use Ifantace\LaravelCommon\Objects\CustomResponse;
use Illuminate\Http\Request;

class CommonService
{
    use CommonTraits;

    /**
     * request from route
     *
     * @var Request
     */
    protected $input;

    /**
     * custom response object
     *
     * @var CustomResponse
     */
    protected $response;

    /**
     * Service初始化
     *
     * @param Request $input
     * @return void
     */
    public function init(Request $input, CustomResponse $response)
    {
        $this->input = $input;
        $this->response = $response;
    }

    /**
     * 設定service的response
     *
     * @param integer $status
     * @param string $message
     * @param string $ui_message
     * @param array $data
     * @return static
     */
    public function setResponse(int $status, $message, $ui_message, array $data = [])
    {
        $this->response->setStatus($status)->setMessage($message)->setUIMessage($ui_message)->setData($data);
        return $this;
    }
}
