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

// Date.timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Shanghai');
}
// Display errors.
ini_set('display_errors', 'on');
// Reporting all.
error_reporting(E_ALL);

// For onError callback.
defined('WORKERMAN_CONNECT_FAIL') || define('WORKERMAN_CONNECT_FAIL', 1);
// For onError callback.
defined('WORKERMAN_SEND_FAIL') || define('WORKERMAN_SEND_FAIL', 2);