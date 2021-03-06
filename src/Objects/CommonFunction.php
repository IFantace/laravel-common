<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-12-11 11:45:28
 * @LastEditors  : IFantace
 * @LastEditTime : 2021-03-24 17:15:06
 * @Description  : 常用的function
 */

namespace Ifantace\LaravelCommon\Objects;

use DateTime;
use Illuminate\Support\Facades\File;

final class CommonFunction
{
    /**
     * Load json file in config/json folder.
     *
     * @param \Illuminate\Http\Request|string $file_name File name in config/json or request with file_name column.
     *
     * @return array|false Array of content.
     *
     * @throws BindingResolutionException
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function loadConfigJson($file_name)
    {
        if (is_string($file_name)) {
            $file_name = $file_name;
        } else {
            $file_name = $file_name->input('file_name');
        }
        return json_decode(File::get(config_path('JSON/' . $file_name . '.json')), true);
    }

    /**
     * Generate random string with number or character.
     *
     * @param int $length Length of string.
     * @param int $mode 0 ~ 7, binary 0 bit: with number, 1 bit: with upper case, 2 bit: with lower case.
     *
     * @return string|false Random string.
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function generateRandomKey($length, $mode = 7)
    {
        if ($mode === 0 || $mode > 7 || $length === 0) {
            return false;
        }
        $random_string = '';
        $threshold_of_number = ($mode & 1) ? 10 : 0;
        $threshold_of_uppercase = (($mode & 2) ? 26 : 0) + $threshold_of_number;
        $threshold_of_lowercase = (($mode & 4) ? 26 : 0) + $threshold_of_uppercase;
        for ($j = 0; $j < $length; $j++) {
            $random_number = mt_rand(0, $threshold_of_lowercase - 1);
            if ($random_number < $threshold_of_number) {
                $in = chr(48 + $random_number);
            } elseif ($random_number < $threshold_of_uppercase) {
                $in = chr(65 + $random_number - $threshold_of_number);
            } elseif ($random_number < $threshold_of_lowercase) {
                $in = chr(97 + $random_number - $threshold_of_uppercase);
            }
            $random_string = $random_string . $in;
        }
        return $random_string;
    }

    /**
     * Use JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE to json_encode array.
     *
     * @param array $array Array which needs to .
     *
     * @return string|false String of json_encode result
     *
     */
    final public static function jsonEncodeUnescaped(array $array)
    {
        return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Return custom log string
     *
     * @param string $event Event name.
     * @param array $data Array of data.
     * @param string $event_code Event uuid.
     *
     * @return string Event string of json_encode result
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function createLogString($event, array $data, $event_code)
    {
        return self::jsonEncodeUnescaped([
            'EVENT' => $event,
            'DATA' => $data,
            'EVENT-CODE' => $event_code
        ]);
    }

    /**
     * Generate random uuid.
     *
     * @return string Uuid.
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function genUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for  'time_lo w'
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for  'time_mi d'
            mt_rand(0, 0xffff),
            // 16 bits for  'time_hi_and_version',
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for  'clk_seq_hi_re s',
            // 8 bits for  'clk_seq_lo w',
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for  'nod e'
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Valid string of date.
     *
     * @param string $date String of date.
     * @param string $format String of format info.
     *
     * @return bool
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function validateDate($datetime, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $datetime);
        return $d && $d->format($format) == $datetime;
    }

    /**
     * Return whereRaw content, which equal whereIn.
     *
     * @param string $column_name Column name.
     * @param array $data_array Search array.
     *
     * @return string String of whereIn in whereRaw
     *
     * @author IFantace <aa431125@gmail.com>
     */
    final public static function createWhereInRaw($column_name, array $data_array)
    {
        if (count($data_array) == 0) {
            return '1 = 0';
        }
        return $column_name . ' In (\'' . join('\',\'', $data_array) . '\')';
    }
}
