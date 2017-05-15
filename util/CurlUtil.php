<?php
namespace app\util;

class CurlUtil {
    // curl method
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';
    const TIMEOUT = 30;
    const CONNECTTIMEOUT = 15;

    /**
     * ִ��һ�� HTTP ����
     *
     * @param string $url ִ�������Url
     * @param array|string $params
     * @param string $method post / get
     * @param array $header ͷ
     * @return array �������
     */
    public static function request( $url, $params, $method = self::METHOD_POST, $header = null ) {
        // ��ʼ��curl
        $curl = curl_init();
        // ����header
        curl_setopt( $curl, CURLOPT_HEADER, false );
        // Ҫ����Ϊ�ַ������������Ļ��
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
        // ���õȴ�ʱ��
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT );
        curl_setopt( $curl, CURLOPT_TIMEOUT, self::TIMEOUT );
        if ( $header ) {
            curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        }
        if ( $params && is_array( $params ) ) {
            $params = http_build_query( $params, '', '&' );
        }
        if ( 'get' == $method ) {
            // ��GET��ʽ��������
            curl_setopt( $curl, CURLOPT_URL, $url . ( $params ? ( '?' . $params ) : '' ) );
        } else {
            // ��POST��ʽ��������
            curl_setopt( $curl, CURLOPT_URL, $url );
            // post�ύ��ʽ
            curl_setopt( $curl, CURLOPT_POST, 1 );
            // ���ô��͵Ĳ���
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $params );
        }

        $res = curl_exec( $curl ); // ����curl
        $err = curl_error( $curl );

        if ( false === $res || !empty( $err ) ) {
            $errno = curl_errno( $curl );
            $info = curl_getinfo( $curl );
            curl_close( $curl );
            //yii::error( "Curl request error errorno=$errno error=$err url=$url info=" .json_encode( $info ) );
            return false;
        }
        // �ر�curl
        curl_close( $curl );
        return $res;
    }

}