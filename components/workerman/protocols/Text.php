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
 * Text Protocol.
 */
class Text
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
        // Judge whether the package length exceeds the limit.
        if (strlen($buffer) >= TcpConnection::$maxPackageSize) {
            $connection->close();
            return 0;
        }
        //  Find the position of  "\n".
        $pos = strpos($buffer, "\n");
        // No "\n", packet length is unknown, continue to wait for the data so return 0.
        if ($pos === false) {
            return 0;
        }
        // Return the current package length.
        return $pos + 1;
    }

    /**
     * Encode.
     *
     * @param string $buffer
     * @return string
     */
    public static function encode($buffer)
    {
        // Add "\n"
        return $buffer . "\n";
    }

    /**
     * Decode.
     *
     * @param string $buffer
     * @return string
     */
    public static function decode($buffer)
    {
        // Remove "\n"
        return trim($buffer);
    }
}
