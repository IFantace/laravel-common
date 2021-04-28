<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : IFantace
 * @LastEditTime : 2021-04-28 12:25:47
 * @Description  : curl操作物件
 */

namespace Ifantace\LaravelCommon\Objects;

use Illuminate\Support\Facades\Log;

class CurlEvent
{
    /**
     * the code of this event
     *
     * @var string
     */
    private $event_code;

    public function __construct($event_code = null)
    {
        $this->event_code = $event_code !== null ? $event_code : CommonFunction::generateRandomKey(8);
    }

    /**
     * Send request.
     *
     * @param string $url Url.
     * @param string $method Request method. 'GET', 'POST', 'PUT'...
     * @param array $data Data. 含有兩部分[params,bodies]
     * @param array $header Headers.
     *
     * @return array|string
     */
    public function sendRequest(
        $url,
        $method = 'GET',
        array $data = [],
        array $header = [],
        array $options = [
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 15
        ]
    ) {
        $send_microtime = microtime('now');
        $request_id = CommonFunction::generateRandomKey(8);
        Log::info(
            CommonFunction::createLogString(
                'CurlSend',
                [
                    'Url' => $url,
                    'Header' => $header,
                    'Data' => CommonFunction::jsonEncodeUnescaped($data),
                    'Option' => $options,
                    'RequestID' => $request_id,
                    'StartAt' => $send_microtime
                ],
                $this->event_code
            )
        );
        if (isset($data['params'])) {
            if (is_array($data['params'])) {
                $this_params = http_build_query($data['params']);
                if (strpos($url, '?') === false) {
                    $url .= '?';
                } else {
                    $url .= '&';
                }
                $url .= $this_params;
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if (isset($data['bodies'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data['bodies']);
        }
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        $output = curl_exec($ch);
        $receive_microtime = microtime('now');
        $status_code = curl_errno($ch);
        Log::info(
            CommonFunction::createLogString(
                'CurlReceive',
                [
                    'StatusCode' => $status_code,
                    'ResponseBody' => $status_code == 0 ? $output : null,
                    'RequestID' => $request_id,
                    'EndAt' => $receive_microtime,
                    'TotalTime' => $receive_microtime - $send_microtime
                ],
                $this->event_code
            )
        );
        if ($status_code == 0) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            Log::warning(
                CommonFunction::createLogString(
                    'CurlError',
                    [
                        'ErrorMessage' => $error,
                        'RequestID' => $request_id,
                    ],
                    $this->event_code
                )
            );
            curl_close($ch);
            return $error;
        }
    }

    /**
     * Get the code of this event
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
     * Set the code of this event
     *
     * @param string $event_code the code of this event
     *
     * @return $this
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function setEventCode($event_code)
    {
        $this->event_code = $event_code;
        return $this;
    }
}
