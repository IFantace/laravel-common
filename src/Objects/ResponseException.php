<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-11 16:43:12
 * @Description  : 自定義例外物件
 */

namespace Ifantace\LaravelCommon\Objects;

use Exception;

class ResponseException extends \Exception
{
    /**
     * response array
     *
     * @var array
     */
    private $response;

    public function __construct(
        $message,
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * get response object
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * set response object
     *
     * @param array $response
     *
     * @return $this
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
        return $this;
    }
}
