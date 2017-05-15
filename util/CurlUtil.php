<?php
namespace app\util;

class CurlUtil {
    // curl method
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const TIMEOUT = 30;
    const CONNECTTIMEOUT = 15;

    /**
     * 执行一个 HTTP 请求
     *
     * @param string $url 执行请求的Url
     * @param array|string $params
     * @param string $method post / get
     * @param array $header 头
     * @return array 结果数组
     */
    public static function request( $url, $params, $method = self::METHOD_POST, $header = null ) {
        // 初始化curl
        $curl = curl_init();
        // 设置header
        curl_setopt( $curl, CURLOPT_HEADER, false );
        // 要求结果为字符串且输出到屏幕上
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        // 设置等待时间
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT );
        curl_setopt( $curl, CURLOPT_TIMEOUT, self::TIMEOUT );
        if ( $header ) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        }
        if ( $params && is_array( $params ) ) {
            $params = http_build_query( $params, '', '&' );
        }
        if ( 'get' == $method ) {
            // 以GET方式发送请求
            curl_setopt( $curl, CURLOPT_URL, $url . ( $params ? ( '?' . $params ) : '' ) );
        } else {
            // 以POST方式发送请求
            curl_setopt( $curl, CURLOPT_URL, $url );
            // post提交方式
            curl_setopt( $curl, CURLOPT_POST, 1 );
            // 设置传送的参数
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params );
        }

        $res = curl_exec( $curl ); // 运行curl
        $err = curl_error( $curl );

        if ( false === $res || !empty( $err ) ) {
            $errno = curl_errno( $curl );
            $info = curl_getinfo( $curl );
            curl_close( $curl );
            //yii::error( "Curl request error errorno=$errno error=$err url=$url info=" .json_encode( $info ) );
            return false;
        }
        // 关闭curl
        curl_close( $curl );
        return $res;
    }

}