<?php
/* *****************************************************************
 * @Author: wushuiyong
 * @Created Time : 一  7/27 21:38:38 2015
 *
 * @File Name: LoggerDetail.php
 * @Description:
 * *****************************************************************/
namespace app\components;

class LoggerDetail {

    public static function formatChildDetail($detail, $name = null) {
        $name = $name ? $name . '.' . $detail['name'] : $detail['name'];
        $list[$name] = [
            'name'    => $detail['name'],
            'error'   => $detail['error'],
            'warning' => $detail['warning'],
            'notice'  => $detail['notice'],
        ];
        if (!empty($detail['child'])) {
            foreach ($detail['child'] as $child) {
                $list = array_merge($list, static::formatChildDetail($child, $name));
            }
        }
        return $list;
    }
}
