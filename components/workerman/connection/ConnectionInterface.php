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
 * ConnectionInterface.
 */
abstract class  ConnectionInterface
{
    /**
     * Statistics for status command.
     *
     * @var array
     */
    public static $statistics = array(
        'connection_count' => 0,
        'total_request'    => 0,
        'throw_exception'  => 0,
        'send_fail'        => 0,
    );

    /**
     * Emitted when data is received.
     *
     * @var callback
     */
    public $onMessage = null;

    /**
     * Emitted when the other end of the socket sends a FIN packet.
     *
     * @var callback
     */
    public $onClose = null;

    /**
     * Emitted when an error occurs with connection.
     *
     * @var callback
     */
    public $onError = null;

    /**
     * Sends data on the connection.
     *
     * @param string $send_buffer
     * @return void|boolean
     */
    abstract public function send($send_buffer);

    /**
     * Get remote IP.
     *
     * @return string
     */
    abstract public function getRemoteIp();

    /**
     * Get remote port.
     *
     * @return int
     */
    abstract public function getRemotePort();

    /**
     * Close connection.
     *
     * @param $data
     * @return void
     */
    abstract public function close($data = null);
}
