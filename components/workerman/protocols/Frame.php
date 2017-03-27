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

use app\components\workerman\connection\TcpConnection;

/**
 * Frame Protocol.
 */
class Frame
{
    /**
     * Check the integrity of the package.
     *
     * @param string        $buffer
     * @param TcpConnection $connection
     * @return int
     */
    public static function input($buffer, TcpConnection $connection)
    {
        if (strlen($buffer) < 4) {
            return 0;
        }
        $unpack_data = unpack('Ntotal_length', $buffer);
        return $unpack_data['total_length'];
    }

    /**
     * Encode.
     *
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        return substr($buffer, 4);
    }

    /**
     * Decode.
     *
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
        $total_length = 4 + strlen($buffer);
        return pack('N', $total_length) . $buffer;
    }
}
