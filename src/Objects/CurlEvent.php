<?php

/*
 * @Author       : Austin
 * @Date         : 2020-03-25 17:09:18
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-07 17:24:01
 * @Description  : {{Description this}}
 */

namespace Ifantace\LaravelCommon\Objects;

use Ifantace\LaravelCommon\Traits\CommonTraits;
use Illuminate\Support\Facades\Log;

class CurlEvent
{
    use CommonTraits;

    /**
     * the uuid of this event
     *
     * @var string
     */
    private $event_uuid;

    public function __construct($event_uuid = null)
    {
        $this->event_uuid = $event_uuid !== null ? $event_uuid : $this->genUuid();
    }
    /**
     * Send request.
     *
     * @param string $url Url.
     * @param string $method Request method. 'GET', 'POST', 'PUT'...
     * @param array $data Data. 含有兩部分[param,body]
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
        $request_id = $this->generateRandomKey(8);
        Log::info(
            $this->createLogString(
                'CurlSend',
                [
                    'Url' => $url,
                    'Header' => $header,
                    'Data' => $this->jsonEncodeUnescaped($data),
                    'Option' => $options,
                    'RequestID' => $request_id
                ],
                $this->event_uuid
            )
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if (isset($data['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        foreach ($options as $key => $value) {
            curl_setopt($ch, $key, $value);
        }
        $output = curl_exec($ch);
        $status_code = curl_errno($ch);
        Log::info(
            $this->createLogString(
                'CurlReceive',
                [
                    'StatusCode' => $status_code,
                    'ResponseBody' => $status_code == 0 ? $output : null,
                    'RequestID' => $request_id
                ],
                $this->event_uuid
            )
        );
        if ($status_code == 0) {
            curl_close($ch);
            return $output;
        } else {
            $error = curl_error($ch);
            Log::warning(
                $this->createLogString(
                    'CurlError',
                    [
                        'ErrorMessage' => $error,
                        'RequestID' => $request_id
                    ],
                    $this->event_uuid
                )
            );
            curl_close($ch);
            return $error;
        }
    }
}
