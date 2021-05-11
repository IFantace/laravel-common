<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2021-05-11 16:37:19
 * @Description  : 自定義回應物件
 */

namespace Ifantace\LaravelCommon\Objects;

use Exception;

class CommonResponse
{
    /**
     * 狀態碼
     *
     * @var int
     */
    private $status;

    /**
     * message
     *
     * @var string
     */
    private $message;

    /**
     * ui_message
     *
     * @var string
     */
    private $ui_message;

    /**
     * file
     *
     * @var string
     */
    private $file;

    /**
     * class
     *
     * @var string
     */
    private $class;

    /**
     * function
     *
     * @var string
     */
    private $function;

    /**
     * line
     *
     * @var int
     */
    private $line;

    /**
     * message
     *
     * @var string
     */
    private $event_code;

    /**
     * data
     *
     * @var array
     */
    private $data;

    /**
     * 建立並初始化event_code
     *
     * @param string $event_code
     */
    public function __construct($event_code)
    {
        $this->event_code = $event_code;
    }

    /**
     * 設定回應的status
     *
     * @param int $status > 0: success, -1: 參數錯誤 -2:驗證錯誤 -3:執行錯誤 -4:非預期的錯誤
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * 設定回應的message
     *
     * @param string $message RD看的message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    /**
     * 設定回應的ui_message
     *
     * @param string $ui_message 使用者看的ui_message
     *
     * @return $this
     */
    public function setUIMessage($ui_message)
    {
        $this->ui_message = $ui_message;
        return $this;
    }

    /**
     * 批次設定必要值
     *
     * @param int $status
     * @param string $message
     * @param string $ui_message
     *
     * @return $this
     */
    public function setCommon($status, $message, $ui_message)
    {
        $this->setStatus($status);
        $this->setMessage($message);
        $this->setUIMessage($ui_message);
        return $this;
    }

    /**
     * 設定response夾帶的data
     *
     * @param array $data key=>value形式
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 設定此回應的系統資料
     *
     * @return $this
     */
    public function setBacktrace()
    {
        $back_trace = debug_backtrace();
        $caller = array_shift($back_trace);
        $caller_source = array_shift($back_trace);
        $this->setFile(isset($caller["file"]) ? $caller["file"] : null);
        $this->setClass(isset($caller["class"]) ? $caller["class"] : null);
        $this->setLine(isset($caller["line"]) ? $caller["line"] : null);
        $this->setFunction(isset($caller_source["function"]) ? $caller_source["function"] : null);
        return $this;
    }

    /**
     * 設定回應的file
     *
     * @param string $file 回應的file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * 設定回應的class
     *
     * @param string $class 回應的class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * 設定此回應所在的function
     *
     * @param string $function function名稱
     *
     * @return $this
     */
    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * 設定此回應所在的行數
     *
     * @param int $line 行數
     *
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;
        return $this;
    }

    /**
     * 發生意外狀況時，所夾帶的Exception檔案
     *
     * @param Throwable $error
     *
     * @return $this
     */
    public function setException(Exception $error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * 產生response array
     *
     * @return array
     */
    public function createResponseArray()
    {
        $response_array = [
            "status" => $this->status,
            "message" => $this->message,
            "ui_message" => $this->ui_message
        ];
        if (isset($this->file) || isset($this->class) || isset($this->function) || isset($this->line)) {
            $response_array['backtrace'] = [
                "file" => $this->file,
                "class" => $this->class,
                "function" => $this->function,
                "line" => $this->line
            ];
        }
        if (is_array($this->data)) {
            foreach ($this->data as $key => $value) {
                $response_array[$key] = $value;
            }
        }
        if (isset($this->error)) {
            $response_array["error"] = [
                "file" => $this->error->getFile(),
                "line" => $this->error->getLine(),
                "message" => $this->error->getMessage(),
            ];
        }
        ksort($response_array);
        return $response_array;
    }

    /**
     * get response array as return
     *
     * @return array
     */
    public function getResponseArray()
    {
        $response_array = $this->createResponseArray();
        // if ($need_record) {
        //     $this->recordResponse($response_array);
        // }
        unset($response_array["backtrace"]);
        unset($response_array["error"]);
        return $response_array;
    }

    // /**
    //  * record response array
    //  *
    //  * @param array $response_array
    //  * @return void
    //  */
    // private function recordResponse(array $response_array)
    // {
    //     Log::info(
    //         $this->createLogString(
    //             "Request-Response",
    //             $response_array,
    //             $this->event_code,
    //         )
    //     );
    // }

    // /**
    //  * 丟出一個exception，用於中斷程式
    //  *
    //  * @param boolean $need_record record response at the same time
    //  * @return void
    //  */
    // public function throwResponseException($need_record = false)
    // {
    //     $response_array = $this->getResponseArray($need_record);
    //     $this_exception = new ResponseException(isset($response_array["message"]) ? $response_array["message"] : "");
    //     $this_exception->setResponse($response_array);
    //     throw $this_exception;
    // }

    /**
     * Get 狀態碼
     *
     * @return int
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get message
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get ui_message
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getUiMessage()
    {
        return $this->ui_message;
    }

    /**
     * Get file
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get class
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Get function
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Get line
     *
     * @return int
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Get message
     *
     * @return string
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getEventCode()
    {
        return $this->event_code;
    }

    /**
     * Set message
     *
     * @param string $event_code message
     *
     * @return $this
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function setEventCode(string $event_code)
    {
        $this->event_code = $event_code;
        return $this;
    }

    /**
     * Get data
     *
     * @return array
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getData()
    {
        return $this->data;
    }
}
