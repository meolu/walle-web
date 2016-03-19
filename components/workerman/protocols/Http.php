<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace app\components\workerman\protocols;

use Workerman\Connection\TcpConnection;

/**
 * http protocol
 */
class Http
{
    /**
     * Check the integrity of the package.
     *
     * @param string        $recv_buffer
     * @param TcpConnection $connection
     * @return int
     */
    public static function input($recv_buffer, TcpConnection $connection)
    {
        if (!strpos($recv_buffer, "\r\n\r\n")) {
            // Judge whether the package length exceeds the limit.
            if (strlen($recv_buffer) >= TcpConnection::$maxPackageSize) {
                $connection->close();
                return 0;
            }
            return 0;
        }

        list($header,) = explode("\r\n\r\n", $recv_buffer, 2);
        if (0 === strpos($recv_buffer, "POST")) {
            // find Content-Length
            $match = array();
            if (preg_match("/\r\nContent-Length: ?(\d+)/", $header, $match)) {
                $content_length = $match[1];
                return $content_length + strlen($header) + 4;
            } else {
                return 0;
            }
        } else {
            return strlen($header) + 4;
        }
    }

    /**
     * Parse $_POST、$_GET、$_COOKIE.
     *
     * @param string        $recv_buffer
     * @param TcpConnection $connection
     * @return array
     */
    public static function decode($recv_buffer, TcpConnection $connection)
    {
        // Init.
        $_POST                         = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = array();
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';
        // Clear cache.
        HttpCache::$header   = array('connection' => 'connection: keep-alive');
        HttpCache::$instance = new HttpCache();
        // $_SERVER
        $_SERVER = array(
            'QUERY_STRING'         => '',
            'REQUEST_METHOD'       => '',
            'REQUEST_URI'          => '',
            'SERVER_PROTOCOL'      => '',
            'SERVER_SOFTWARE'      => 'workerman/3.0',
            'SERVER_NAME'          => '',
            'HTTP_HOST'            => '',
            'HTTP_USER_AGENT'      => '',
            'HTTP_ACCEPT'          => '',
            'HTTP_ACCEPT_LANGUAGE' => '',
            'HTTP_ACCEPT_ENCODING' => '',
            'HTTP_COOKIE'          => '',
            'HTTP_CONNECTION'      => '',
            'REMOTE_ADDR'          => '',
            'REMOTE_PORT'          => '0',
        );

        // Parse headers.
        list($http_header, $http_body) = explode("\r\n\r\n", $recv_buffer, 2);
        $header_data = explode("\r\n", $http_header);

        list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ',
            $header_data[0]);

        $http_post_boundary = '';
        unset($header_data[0]);
        foreach ($header_data as $content) {
            // \r\n\r\n
            if (empty($content)) {
                continue;
            }
            list($key, $value) = explode(':', $content, 2);
            $key   = strtolower($key);
            $value = trim($value);
            switch ($key) {
                // HTTP_HOST
                case 'host':
                    $_SERVER['HTTP_HOST']   = $value;
                    $tmp                    = explode(':', $value);
                    $_SERVER['SERVER_NAME'] = $tmp[0];
                    if (isset($tmp[1])) {
                        $_SERVER['SERVER_PORT'] = $tmp[1];
                    }
                    break;
                // cookie
                case 'cookie':
                    $_SERVER['HTTP_COOKIE'] = $value;
                    parse_str(str_replace('; ', '&', $_SERVER['HTTP_COOKIE']), $_COOKIE);
                    break;
                // user-agent
                case 'user-agent':
                    $_SERVER['HTTP_USER_AGENT'] = $value;
                    break;
                // accept
                case 'accept':
                    $_SERVER['HTTP_ACCEPT'] = $value;
                    break;
                // accept-language
                case 'accept-language':
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $value;
                    break;
                // accept-encoding
                case 'accept-encoding':
                    $_SERVER['HTTP_ACCEPT_ENCODING'] = $value;
                    break;
                // connection
                case 'connection':
                    $_SERVER['HTTP_CONNECTION'] = $value;
                    break;
                case 'referer':
                    $_SERVER['HTTP_REFERER'] = $value;
                    break;
                case 'if-modified-since':
                    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $value;
                    break;
                case 'if-none-match':
                    $_SERVER['HTTP_IF_NONE_MATCH'] = $value;
                    break;
                case 'content-type':
                    if (!preg_match('/boundary="?(\S+)"?/', $value, $match)) {
                        $_SERVER['CONTENT_TYPE'] = $value;
                    } else {
                        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data';
                        $http_post_boundary      = '--' . $match[1];
                    }
                    break;
            }
        }

        // Parse $_POST.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'multipart/form-data') {
                self::parseUploadFiles($http_body, $http_post_boundary);
            } else {
                parse_str($http_body, $_POST);
                // $GLOBALS['HTTP_RAW_POST_DATA']
                $GLOBALS['HTTP_RAW_POST_DATA'] = $http_body;
            }
        }

        // QUERY_STRING
        $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if ($_SERVER['QUERY_STRING']) {
            // $GET
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else {
            $_SERVER['QUERY_STRING'] = '';
        }

        // REQUEST
        $_REQUEST = array_merge($_GET, $_POST);

        // REMOTE_ADDR REMOTE_PORT
        $_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
        $_SERVER['REMOTE_PORT'] = $connection->getRemotePort();

        return array('get' => $_GET, 'post' => $_POST, 'cookie' => $_COOKIE, 'server' => $_SERVER, 'files' => $_FILES);
    }

    /**
     * Http encode.
     *
     * @param string        $content
     * @param TcpConnection $connection
     * @return string
     */
    public static function encode($content, TcpConnection $connection)
    {
        // Default http-code.
        if (!isset(HttpCache::$header['Http-Code'])) {
            $header = "HTTP/1.1 200 OK\r\n";
        } else {
            $header = HttpCache::$header['Http-Code'] . "\r\n";
            unset(HttpCache::$header['Http-Code']);
        }

        // Content-Type
        if (!isset(HttpCache::$header['Content-Type'])) {
            $header .= "Content-Type: text/html;charset=utf-8\r\n";
        }

        // other headers
        foreach (HttpCache::$header as $key => $item) {
            if ('Set-Cookie' === $key && is_array($item)) {
                foreach ($item as $it) {
                    $header .= $it . "\r\n";
                }
            } else {
                $header .= $item . "\r\n";
            }
        }

        // header
        $header .= "Server: WorkerMan/3.0\r\nContent-Length: " . strlen($content) . "\r\n\r\n";

        // save session
        self::sessionWriteClose();

        // the whole http package
        return $header . $content;
    }

    /**
     * 设置http头
     *
     * @return bool|void
     */
    public static function header($content, $replace = true, $http_response_code = 0)
    {
        if (PHP_SAPI != 'cli') {
            return $http_response_code ? header($content, $replace, $http_response_code) : header($content, $replace);
        }
        if (strpos($content, 'HTTP') === 0) {
            $key = 'Http-Code';
        } else {
            $key = strstr($content, ":", true);
            if (empty($key)) {
                return false;
            }
        }

        if ('location' === strtolower($key) && !$http_response_code) {
            return self::header($content, true, 302);
        }

        if (isset(HttpCache::$codes[$http_response_code])) {
            HttpCache::$header['Http-Code'] = "HTTP/1.1 $http_response_code " . HttpCache::$codes[$http_response_code];
            if ($key === 'Http-Code') {
                return true;
            }
        }

        if ($key === 'Set-Cookie') {
            HttpCache::$header[$key][] = $content;
        } else {
            HttpCache::$header[$key] = $content;
        }

        return true;
    }

    /**
     * Remove header.
     *
     * @param string $name
     * @return void
     */
    public static function headerRemove($name)
    {
        if (PHP_SAPI != 'cli') {
            header_remove($name);
            return;
        }
        unset(HttpCache::$header[$name]);
    }

    /**
     * Set cookie.
     *
     * @param string  $name
     * @param string  $value
     * @param integer $maxage
     * @param string  $path
     * @param string  $domain
     * @param bool    $secure
     * @param bool    $HTTPOnly
     * @return bool|void
     */
    public static function setcookie(
        $name,
        $value = '',
        $maxage = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $HTTPOnly = false
    ) {
        if (PHP_SAPI != 'cli') {
            return setcookie($name, $value, $maxage, $path, $domain, $secure, $HTTPOnly);
        }
        return self::header(
            'Set-Cookie: ' . $name . '=' . rawurlencode($value)
            . (empty($domain) ? '' : '; Domain=' . $domain)
            . (empty($maxage) ? '' : '; Max-Age=' . $maxage)
            . (empty($path) ? '' : '; Path=' . $path)
            . (!$secure ? '' : '; Secure')
            . (!$HTTPOnly ? '' : '; HttpOnly'), false);
    }

    /**
     * sessionStart
     *
     * @return bool
     */
    public static function sessionStart()
    {
        if (PHP_SAPI != 'cli') {
            return session_start();
        }
        if (HttpCache::$instance->sessionStarted) {
            echo "already sessionStarted\nn";
            return true;
        }
        HttpCache::$instance->sessionStarted = true;
        // Generate a SID.
        if (!isset($_COOKIE[HttpCache::$sessionName]) || !is_file(HttpCache::$sessionPath . '/ses' . $_COOKIE[HttpCache::$sessionName])) {
            $file_name = tempnam(HttpCache::$sessionPath, 'ses');
            if (!$file_name) {
                return false;
            }
            HttpCache::$instance->sessionFile = $file_name;
            $session_id                       = substr(basename($file_name), strlen('ses'));
            return self::setcookie(
                HttpCache::$sessionName
                , $session_id
                , ini_get('session.cookie_lifetime')
                , ini_get('session.cookie_path')
                , ini_get('session.cookie_domain')
                , ini_get('session.cookie_secure')
                , ini_get('session.cookie_httponly')
            );
        }
        if (!HttpCache::$instance->sessionFile) {
            HttpCache::$instance->sessionFile = HttpCache::$sessionPath . '/ses' . $_COOKIE[HttpCache::$sessionName];
        }
        // Read session from session file.
        if (HttpCache::$instance->sessionFile) {
            $raw = file_get_contents(HttpCache::$instance->sessionFile);
            if ($raw) {
                session_decode($raw);
            }
        }
    }

    /**
     * Save session.
     *
     * @return bool
     */
    public static function sessionWriteClose()
    {
        if (PHP_SAPI != 'cli') {
            return session_write_close();
        }
        if (!empty(HttpCache::$instance->sessionStarted) && !empty($_SESSION)) {
            $session_str = session_encode();
            if ($session_str && HttpCache::$instance->sessionFile) {
                return file_put_contents(HttpCache::$instance->sessionFile, $session_str);
            }
        }
        return empty($_SESSION);
    }

    /**
     * End, like call exit in php-fpm.
     *
     * @param string $msg
     * @throws \Exception
     */
    public static function end($msg = '')
    {
        if (PHP_SAPI != 'cli') {
            exit($msg);
        }
        if ($msg) {
            echo $msg;
        }
        throw new \Exception('jump_exit');
    }

    /**
     * Get mime types.
     *
     * @return string
     */
    public static function getMimeTypesFile()
    {
        return __DIR__ . '/Http/mime.types';
    }

    /**
     * Parse $_FILES.
     *
     * @param string $http_body
     * @param string $http_post_boundary
     * @return void
     */
    protected static function parseUploadFiles($http_body, $http_post_boundary)
    {
        $http_body           = substr($http_body, 0, strlen($http_body) - (strlen($http_post_boundary) + 4));
        $boundary_data_array = explode($http_post_boundary . "\r\n", $http_body);
        if ($boundary_data_array[0] === '') {
            unset($boundary_data_array[0]);
        }
        foreach ($boundary_data_array as $boundary_data_buffer) {
            list($boundary_header_buffer, $boundary_value) = explode("\r\n\r\n", $boundary_data_buffer, 2);
            // Remove \r\n from the end of buffer.
            $boundary_value = substr($boundary_value, 0, -2);
            foreach (explode("\r\n", $boundary_header_buffer) as $item) {
                list($header_key, $header_value) = explode(": ", $item);
                $header_key = strtolower($header_key);
                switch ($header_key) {
                    case "content-disposition":
                        // Is file data.
                        if (preg_match('/name=".*?"; filename="(.*?)"$/', $header_value, $match)) {
                            // Parse $_FILES.
                            $_FILES[] = array(
                                'file_name' => $match[1],
                                'file_data' => $boundary_value,
                                'file_size' => strlen($boundary_value),
                            );
                            continue;
                        } // Is post field.
                        else {
                            // Parse $_POST.
                            if (preg_match('/name="(.*?)"$/', $header_value, $match)) {
                                $_POST[$match[1]] = $boundary_value;
                            }
                        }
                        break;
                }
            }
        }
    }
}

/**
 * Http cache for the current http response.
 */
class HttpCache
{
    public static $codes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * @var HttpCache
     */
    public static $instance = null;

    public static $header = array();
    public static $sessionPath = '';
    public static $sessionName = '';
    public $sessionStarted = false;
    public $sessionFile = '';

    public static function init()
    {
        self::$sessionName = ini_get('session.name');
        self::$sessionPath = session_save_path();
        if (!self::$sessionPath) {
            self::$sessionPath = sys_get_temp_dir();
        }
        @\session_start();
    }
}
