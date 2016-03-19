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
namespace app\components\workerman\connection;

/**
 * UdpConnection.
 */
class UdpConnection extends ConnectionInterface
{
    /**
     * Application layer protocol.
     * The format is like this Workerman\\Protocols\\Http.
     *
     * @var \Workerman\Protocols\ProtocolInterface
     */
    public $protocol = null;

    /**
     * Udp socket.
     *
     * @var resource
     */
    protected $_socket = null;

    /**
     * Remote ip.
     *
     * @var string
     */
    protected $_remoteIp = '';

    /**
     * Remote port.
     *
     * @var int
     */
    protected $_remotePort = 0;

    /**
     * Remote address.
     *
     * @var string
     */
    protected $_remoteAddress = '';

    /**
     * Construct.
     *
     * @param resource $socket
     * @param string   $remote_address
     */
    public function __construct($socket, $remote_address)
    {
        $this->_socket        = $socket;
        $this->_remoteAddress = $remote_address;
    }

    /**
     * Sends data on the connection.
     *
     * @param string $send_buffer
     * @param bool   $raw
     * @return void|boolean
     */
    public function send($send_buffer, $raw = false)
    {
        if (false === $raw && $this->protocol) {
            $parser      = $this->protocol;
            $send_buffer = $parser::encode($send_buffer, $this);
            if ($send_buffer === '') {
                return null;
            }
        }
        return strlen($send_buffer) === stream_socket_sendto($this->_socket, $send_buffer, 0, $this->_remoteAddress);
    }

    /**
     * Get remote IP.
     *
     * @return string
     */
    public function getRemoteIp()
    {
        if (!$this->_remoteIp) {
            list($this->_remoteIp, $this->_remotePort) = explode(':', $this->_remoteAddress, 2);
        }
        return $this->_remoteIp;
    }

    /**
     * Get remote port.
     *
     * @return int
     */
    public function getRemotePort()
    {
        if (!$this->_remotePort) {
            list($this->_remoteIp, $this->_remotePort) = explode(':', $this->_remoteAddress, 2);
        }
        return $this->_remotePort;
    }

    /**
     * Close connection.
     *
     * @param mixed $data
     * @return bool
     */
    public function close($data = null)
    {
        if ($data !== null) {
            $this->send($data);
        }
        return true;
    }
}
