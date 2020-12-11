<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-11 16:45:00
 * @Description  : 商業邏輯的部份
 */

namespace Ifantace\LaravelCommon\Services;

use Ifantace\LaravelCommon\Objects\CommonResponse;
use Illuminate\Http\Request;

class CommonService
{
    /**
     * request from route
     *
     * @var Request
     */
    protected $request;

    /**
     * custom response object
     *
     * @var CommonResponse
     */
    protected $response;

    public function __construct(Request $request = null, CommonResponse $response = null)
    {
        if ($request !== null) {
            $this->setRequest($request);
        }
        if ($response !== null) {
            $this->setResponse($response);
        }
    }

    /**
     * Service初始化
     *
     * @param Request $request
     *
     * @return $this
     */
    public function init(Request $request, CommonResponse $response)
    {
        $this->setRequest($request);
        $this->setResponse($response);
        return $this;
    }

    /**
     * 設定service的response
     *
     * @param integer $status
     * @param string $message
     * @param string $ui_message
     * @param array $data
     *
     * @return $this
     */
    public function setResponseData($status, $message, $ui_message, array $data = [])
    {
        $this->response->setStatus($status)->setMessage($message)->setUIMessage($ui_message)->setData($data);
        return $this;
    }

    /**
     * Get request from route
     *
     * @return Request
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request from route
     *
     * @param Request $request request from route
     *
     * @return $this
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get custom response object
     *
     * @return CommonResponse
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set custom response object
     *
     * @param CommonResponse $response custom response object
     *
     * @return $this
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function setResponse(CommonResponse $response)
    {
        $this->response = $response;
        return $this;
    }
}
