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
namespace  app\components\workerman\protocols;

use  app\components\workerman\connection\ConnectionInterface;

/**
 * WebSocket protocol.
 */
class Websocket implements \app\components\workerman\protocols\ProtocolInterface
{
    /**
     * Minimum head length of websocket protocol.
     *
     * @var int
     */
    const MIN_HEAD_LEN = 6;

    /**
     * Websocket blob type.
     *
     * @var string
     */
    const BINARY_TYPE_BLOB = "\x81";

    /**
     * Websocket arraybuffer type.
     *
     * @var string
     */
    const BINARY_TYPE_ARRAYBUFFER = "\x82";

    /**
     * Check the integrity of the package.
     *
     * @param string              $buffer
     * @param ConnectionInterface $connection
     * @return int
     */
    public static function input($buffer, ConnectionInterface $connection)
    {
        // Receive length.
        $recv_len = strlen($buffer);
        // We need more data.
        if ($recv_len < self::MIN_HEAD_LEN) {
            return 0;
        }

        // Has not yet completed the handshake.
        if (empty($connection->websocketHandshake)) {
            return self::dealHandshake($buffer, $connection);
        }

        // Buffer websocket frame data.
        if ($connection->websocketCurrentFrameLength) {
            // We need more frame data.
            if ($connection->websocketCurrentFrameLength > $recv_len) {
                // Return 0, because it is not clear the full packet length, waiting for the frame of fin=1.
                return 0;
            }
        } else {
            $data_len     = ord($buffer[1]) & 127;
            $firstbyte    = ord($buffer[0]);
            $is_fin_frame = $firstbyte >> 7;
            $opcode       = $firstbyte & 0xf;
            switch ($opcode) {
                case 0x0:
                    break;
                // Blob type.
                case 0x1:
                    break;
                // Arraybuffer type.
                case 0x2:
                    break;
                // Close package.
                case 0x8:
                    // Try to emit onWebSocketClose callback.
                    if (isset($connection->onWebSocketClose)) {
                        try {
                            call_user_func($connection->onWebSocketClose, $connection);
                        } catch (\Exception $e) {
                            echo $e;
                            exit(250);
                        }
                    } // Close connection.
                    else {
                        $connection->close();
                    }
                    return 0;
                // Ping package.
                case 0x9:
                    // Try to emit onWebSocketPing callback.
                    if (isset($connection->onWebSocketPing)) {
                        try {
                            call_user_func($connection->onWebSocketPing, $connection);
                        } catch (\Exception $e) {
                            echo $e;
                            exit(250);
                        }
                    } // Send pong package to client.
                    else {
                        $connection->send(pack('H*', '8a00'), true);
                    }
                    // Consume data from receive buffer.
                    if (!$data_len) {
                        $connection->consumeRecvBuffer(self::MIN_HEAD_LEN);
                        return 0;
                    }
                    break;
                // Pong package.
                case 0xa:
                    // Try to emit onWebSocketPong callback.
                    if (isset($connection->onWebSocketPong)) {
                        try {
                            call_user_func($connection->onWebSocketPong, $connection);
                        } catch (\Exception $e) {
                            echo $e;
                            exit(250);
                        }
                    }
                    //  Consume data from receive buffer.
                    if (!$data_len) {
                        $connection->consumeRecvBuffer(self::MIN_HEAD_LEN);
                        return 0;
                    }
                    break;
                // Wrong opcode. 
                default :
                    echo "error opcode $opcode and close websocket connection\n";
                    $connection->close();
                    return 0;
            }

            // Calculate packet length.
            $head_len = self::MIN_HEAD_LEN;
            if ($data_len === 126) {
                $head_len = 8;
                if ($head_len > $recv_len) {
                    return 0;
                }
                $pack     = unpack('ntotal_len', substr($buffer, 2, 2));
                $data_len = $pack['total_len'];
            } else {
                if ($data_len === 127) {
                    $head_len = 14;
                    if ($head_len > $recv_len) {
                        return 0;
                    }
                    $arr      = unpack('N2', substr($buffer, 2, 8));
                    $data_len = $arr[1] * 4294967296 + $arr[2];
                }
            }
            $current_frame_length = $head_len + $data_len;
            if ($is_fin_frame) {
                return $current_frame_length;
            } else {
                $connection->websocketCurrentFrameLength = $current_frame_length;
            }
        }

        // Received just a frame length data.
        if ($connection->websocketCurrentFrameLength == $recv_len) {
            self::decode($buffer, $connection);
            $connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
            $connection->websocketCurrentFrameLength = 0;
            return 0;
        } // The length of the received data is greater than the length of a frame.
        elseif ($connection->websocketCurrentFrameLength < $recv_len) {
            self::decode(substr($buffer, 0, $connection->websocketCurrentFrameLength), $connection);
            $connection->consumeRecvBuffer($connection->websocketCurrentFrameLength);
            $current_frame_length                    = $connection->websocketCurrentFrameLength;
            $connection->websocketCurrentFrameLength = 0;
            // Continue to read next frame.
            return self::input(substr($buffer, $current_frame_length), $connection);
        } // The length of the received data is less than the length of a frame.
        else {
            return 0;
        }
    }

    /**
     * Websocket encode.
     *
     * @param string              $buffer
     * @param ConnectionInterface $connection
     * @return string
     */
    public static function encode($buffer, ConnectionInterface $connection)
    {
        $len = strlen($buffer);
        if (empty($connection->websocketType)) {
            $connection->websocketType = self::BINARY_TYPE_BLOB;
        }

        $first_byte = $connection->websocketType;

        if ($len <= 125) {
            $encode_buffer = $first_byte . chr($len) . $buffer;
        } else {
            if ($len <= 65535) {
                $encode_buffer = $first_byte . chr(126) . pack("n", $len) . $buffer;
            } else {
                $encode_buffer = $first_byte . chr(127) . pack("xxxxN", $len) . $buffer;
            }
        }

        // Handshake not completed so temporary buffer websocket data waiting for send.
        if (empty($connection->websocketHandshake)) {
            if (empty($connection->tmpWebsocketData)) {
                $connection->tmpWebsocketData = '';
            }
            $connection->tmpWebsocketData .= $encode_buffer;
            // Return empty string.
            return '';
        }

        return $encode_buffer;
    }

    /**
     * Websocket decode.
     *
     * @param string              $buffer
     * @param ConnectionInterface $connection
     * @return string
     */
    public static function decode($buffer, ConnectionInterface $connection)
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data  = substr($buffer, 8);
        } else {
            if ($len === 127) {
                $masks = substr($buffer, 10, 4);
                $data  = substr($buffer, 14);
            } else {
                $masks = substr($buffer, 2, 4);
                $data  = substr($buffer, 6);
            }
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        if ($connection->websocketCurrentFrameLength) {
            $connection->websocketDataBuffer .= $decoded;
            return $connection->websocketDataBuffer;
        } else {
            $decoded                         = $connection->websocketDataBuffer . $decoded;
            $connection->websocketDataBuffer = '';
            return $decoded;
        }
    }

    /**
     * Websocket handshake.
     *
     * @param string                              $buffer
     * @param \Workerman\Connection\TcpConnection $connection
     * @return int
     */
    protected static function dealHandshake($buffer, $connection)
    {
        // HTTP protocol.
        if (0 === strpos($buffer, 'GET')) {
            // Find \r\n\r\n.
            $heder_end_pos = strpos($buffer, "\r\n\r\n");
            if (!$heder_end_pos) {
                return 0;
            }

            // Get Sec-WebSocket-Key.
            $Sec_WebSocket_Key = '';
            if (preg_match("/Sec-WebSocket-Key: *(.*?)\r\n/i", $buffer, $match)) {
                $Sec_WebSocket_Key = $match[1];
            } else {
                $connection->send("HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Sec-WebSocket-Key not found.<br>This is a WebSocket service and can not be accessed via HTTP.",
                    true);
                $connection->close();
                return 0;
            }
            // Calculation websocket key.
            $new_key = base64_encode(sha1($Sec_WebSocket_Key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
            // Handshake response data.
            $handshake_message = "HTTP/1.1 101 Switching Protocols\r\n";
            $handshake_message .= "Upgrade: websocket\r\n";
            $handshake_message .= "Sec-WebSocket-Version: 13\r\n";
            $handshake_message .= "connection: Upgrade\r\n";
            $handshake_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
            // Mark handshake complete..
            $connection->websocketHandshake = true;
            // Websocket data buffer.
            $connection->websocketDataBuffer = '';
            // Current websocket frame length.
            $connection->websocketCurrentFrameLength = 0;
            // Current websocket frame data.
            $connection->websocketCurrentFrameBuffer = '';
            // Consume handshake data.
            $connection->consumeRecvBuffer(strlen($buffer));
            // Send handshake response.
            $connection->send($handshake_message, true);

            // There are data waiting to be sent.
            if (!empty($connection->tmpWebsocketData)) {
                $connection->send($connection->tmpWebsocketData, true);
                $connection->tmpWebsocketData = '';
            }
            // blob or arraybuffer
            if (empty($connection->websocketType)) {
                $connection->websocketType = self::BINARY_TYPE_BLOB;
            }
            // Try to emit onWebSocketConnect callback.
            if (isset($connection->onWebSocketConnect)) {
                self::parseHttpHeader($buffer);
                try {
                    call_user_func($connection->onWebSocketConnect, $connection, $buffer);
                } catch (\Exception $e) {
                    echo $e;
                    exit(250);
                }
                $_GET = $_COOKIE = $_SERVER = array();
            }
            return 0;
        } // Is flash policy-file-request.
        elseif (0 === strpos($buffer, '<polic')) {
            $policy_xml = '<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>' . "\0";
            $connection->send($policy_xml, true);
            $connection->consumeRecvBuffer(strlen($buffer));
            return 0;
        }
        // Bad websocket handshake request.
        $connection->send("HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b><br>Invalid handshake data for websocket. ",
            true);
        $connection->close();
        return 0;
    }

    /**
     * Parse http header.
     *
     * @param string $buffer
     * @return void
     */
    protected static function parseHttpHeader($buffer)
    {
        $header_data = explode("\r\n", $buffer);
        $_SERVER     = array();
        list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ',
            $header_data[0]);
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
                // HTTP_COOKIE
                case 'cookie':
                    $_SERVER['HTTP_COOKIE'] = $value;
                    parse_str(str_replace('; ', '&', $_SERVER['HTTP_COOKIE']), $_COOKIE);
                    break;
                // HTTP_USER_AGENT
                case 'user-agent':
                    $_SERVER['HTTP_USER_AGENT'] = $value;
                    break;
                // HTTP_REFERER
                case 'referer':
                    $_SERVER['HTTP_REFERER'] = $value;
                    break;
                case 'origin':
                    $_SERVER['HTTP_ORIGIN'] = $value;
                    break;
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
    }
}
