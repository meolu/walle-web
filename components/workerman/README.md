# Workerman
[![Gitter](https://badges.gitter.im/walkor/Workerman.svg)](https://gitter.im/walkor/Workerman?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=body_badge)

## What is it
Workerman is a library for event-driven programming in PHP. It has a huge number of features. Each worker is able to handle thousands of connections.

## Requires

PHP 5.3 or Higher  
A POSIX compatible operating system (Linux, OSX, BSD)  
POSIX and PCNTL extensions for PHP  

## Installation

```
composer require workerman/workerman
```

## Basic Usage

### A websocket server 
test.php
```php
<?php
use Workerman\Worker;
require_once './Workerman/Autoloader.php';

// Create a Websocket server
$ws_worker = new Worker("websocket://0.0.0.0:2346");

// 4 processes
$ws_worker->count = 4;

// Emitted when new connection come
$ws_worker->onConnect = function($connection)
{
    echo "New connection\n";
 };

// Emitted when data received
$ws_worker->onMessage = function($connection, $data)
{
    // Send hello $data
    $connection->send('hello ' . $data);
};

// Emitted when connection closed
$ws_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();
```

### An http server
test.php
```php
require_once './Workerman/Autoloader.php';
use Workerman\Worker;

// #### http worker ####
$http_worker = new Worker("http://0.0.0.0:2345");

// 4 processes
$http_worker->count = 4;

// Emitted when data received
$http_worker->onMessage = function($connection, $data)
{
    // $_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES are available
    var_dump($_GET, $_POST, $_COOKIE, $_SESSION, $_SERVER, $_FILES);
    // send data to client
    $connection->send("hello world \n");
};

// run all workers
Worker::runAll();
```

### A WebServer
test.php
```php
require_once './Workerman/Autoloader.php';
use Workerman\WebServer;

// WebServer
$web = new WebServer("http://0.0.0.0:80");

// 4 processes
$web->count = 4;

// Set the root of domains
$web->addRoot('www.your_domain.com', '/your/path/Web');
$web->addRoot('www.another_domain.com', '/another/path/Web');
// run all workers
Worker::runAll();
```

### A tcp server
test.php
```php
require_once './Workerman/Autoloader.php';
use Workerman\Worker;

// #### create socket and listen 1234 port ####
$tcp_worker = new Worker("tcp://0.0.0.0:1234");

// 4 processes
$tcp_worker->count = 4;

// Emitted when new connection come
$tcp_worker->onConnect = function($connection)
{
    echo "New Connection\n";
};

// Emitted when data received
$tcp_worker->onMessage = function($connection, $data)
{
    // send data to client
    $connection->send("hello $data \n");
};

// Emitted when new connection come
$tcp_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

Worker::runAll();
```

### Custom protocol
Protocols/MyTextProtocol.php
```php
namespace Protocols;
/**
 * User defined protocol
 * Format Text+"\n"
 */
class MyTextProtocol
{
    public static function input($recv_buffer)
    {
        // Find the position of the first occurrence of "\n"
        $pos = strpos($recv_buffer, "\n");
        // Not a complete package. Return 0 because the length of package can not be calculated
        if($pos === false)
        {
            return 0;
        }
        // Return length of the package
        return $pos+1;
    }

    public static function decode($recv_buffer)
    {
        return trim($recv_buffer);
    }

    public static function encode($data)
    {
        return $data."\n";
    }
}
```

test.php
```php
require_once './Workerman/Autoloader.php';
use Workerman\Worker;

// #### MyTextProtocol worker ####
$text_worker = new Worker("MyTextProtocol://0.0.0.0:5678");

$text_worker->onConnect = function($connection)
{
    echo "New connection\n";
};

$text_worker->onMessage =  function($connection, $data)
{
    // send data to client
    $connection->send("hello world \n");
};

$text_worker->onClose = function($connection)
{
    echo "Connection closed\n";
};

// run all workers
Worker::runAll();
```

### Timer
test.php
```php
require_once './Workerman/Autoloader.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$task = new Worker();
$task->onWorkerStart = function($task)
{
    // 2.5 seconds
    $time_interval = 2.5; 
    $timer_id = Timer::add($time_interval, 
        function()
        {
            echo "Timer run\n";
        }
    );
};

// run all workers
Worker::runAll();
```

run with:

```php test.php start```

## Available commands
```php test.php start  ```  
```php test.php start -d  ```  
![workerman start](http://www.workerman.net/img/workerman-start.png)  
```php test.php status  ```  
![workerman satus](http://www.workerman.net/img/workerman-status.png?a=123)
```php test.php stop  ```  
```php test.php restart  ```  
```php test.php reload  ```  

## Documentation

中文主页:[http://www.workerman.net](http://www.workerman.net)

中文文档: [http://doc3.workerman.net](http://doc3.workerman.net)

Documentation:[https://github.com/walkor/workerman-manual](https://github.com/walkor/workerman-manual/blob/master/english/src/SUMMARY.md)

# Benchmarks
```
CPU:      Intel(R) Core(TM) i3-3220 CPU @ 3.30GHz and 4 processors totally
Memory:   8G
OS:       Ubuntu 14.04 LTS
Software: ab
PHP:      5.5.9
```

**Codes**
```php
<?php
use Workerman\Worker;
$worker = new Worker('tcp://0.0.0.0:1234');
$worker->count=3;
$worker->onMessage = function($connection, $data)
{
    $connection->send("HTTP/1.1 200 OK\r\nConnection: keep-alive\r\nServer: workerman\1.1.4\r\n\r\nhello");
};
Worker::runAll();
```
**Result**

```shell
ab -n1000000 -c100 -k http://127.0.0.1:1234/
This is ApacheBench, Version 2.3 <$Revision: 1528965 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 127.0.0.1 (be patient)
Completed 100000 requests
Completed 200000 requests
Completed 300000 requests
Completed 400000 requests
Completed 500000 requests
Completed 600000 requests
Completed 700000 requests
Completed 800000 requests
Completed 900000 requests
Completed 1000000 requests
Finished 1000000 requests


Server Software:        workerman/3.1.4
Server Hostname:        127.0.0.1
Server Port:            1234

Document Path:          /
Document Length:        5 bytes

Concurrency Level:      100
Time taken for tests:   7.240 seconds
Complete requests:      1000000
Failed requests:        0
Keep-Alive requests:    1000000
Total transferred:      73000000 bytes
HTML transferred:       5000000 bytes
Requests per second:    138124.14 [#/sec] (mean)
Time per request:       0.724 [ms] (mean)
Time per request:       0.007 [ms] (mean, across all concurrent requests)
Transfer rate:          9846.74 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.0      0       5
Processing:     0    1   0.2      1       9
Waiting:        0    1   0.2      1       9
Total:          0    1   0.2      1       9

Percentage of the requests served within a certain time (ms)
  50%      1
  66%      1
  75%      1
  80%      1
  90%      1
  95%      1
  98%      1
  99%      1
 100%      9 (longest request)

```


# Demos

## [tadpole](http://kedou.workerman.net/)  
[Live demo](http://kedou.workerman.net/)  
[Source code](https://github.com/walkor/workerman)  
![workerman todpole](http://www.workerman.net/img/workerman-todpole.png)  

## [BrowserQuest](http://www.workerman.net/demos/browserquest/)   
[Live demo](http://www.workerman.net/demos/browserquest/)  
[Source code](https://github.com/walkor/BrowserQuest-PHP)  
![BrowserQuest width workerman](http://www.workerman.net/img/browserquest.jpg) 

## [web vmstat](http://www.workerman.net/demos/vmstat/)   
[Live demo](http://www.workerman.net/demos/vmstat/)  
[Source code](https://github.com/walkor/workerman-vmstat)  
![web vmstat](http://www.workerman.net/img/workerman-vmstat.png)   

## [live-ascii-camera](https://github.com/walkor/live-ascii-camera)   
[Live demo camera page](http://www.workerman.net/demos/live-ascii-camera/camera.html)  
[Live demo receive page](http://www.workerman.net/demos/live-ascii-camera/)  
[Source code](https://github.com/walkor/live-ascii-camera)  
![live-ascii-camera](http://www.workerman.net/img/live-ascii-camera.png)   

## [live-camera](https://github.com/walkor/live-camera)   
[Live demo camera page](http://www.workerman.net/demos/live-camera/camera.html)  
[Live demo receive page](http://www.workerman.net/demos/live-camera/)  
[Source code](https://github.com/walkor/live-camera)  
![live-camera](http://www.workerman.net/img/live-camera.jpg)  

## [chat room](http://chat.workerman.net/)  
[Live demo](http://chat.workerman.net/)  
[Source code](https://github.com/walkor/workerman-chat)  
![workerman-chat](http://www.workerman.net/img/workerman-chat.png)  

## [PHPSocket.IO](https://github.com/walkor/phpsocket.io)  
[Live demo](http://www.workerman.net/demos/phpsocketio-chat/)  
[Source code](https://github.com/walkor/phpsocket.io)  
![phpsocket.io](http://www.workerman.net/img/socket.io.png)  

## [statistics](http://www.workerman.net:55757/)  
[Live demo](http://www.workerman.net:55757/)  
[Source code](https://github.com/walkor/workerman-statistics)  
![workerman-statistics](http://www.workerman.net/img/workerman-statistics.png)  

## [flappybird](http://workerman.net/demos/flappy-bird/)  
[Live demo](http://workerman.net/demos/flappy-bird/)  
[Source code](https://github.com/walkor/workerman-flappy-bird)  
![workerman-statistics](http://www.workerman.net/img/workerman-flappy-bird.png)  

## [jsonRpc](https://github.com/walkor/workerman-JsonRpc)  
[Source code](https://github.com/walkor/workerman-JsonRpc)  
![workerman-jsonRpc](http://www.workerman.net/img/workerman-json-rpc.png)  

## [thriftRpc](https://github.com/walkor/workerman-thrift)  
[Source code](https://github.com/walkor/workerman-thrift)  
![workerman-thriftRpc](http://www.workerman.net/img/workerman-thrift.png)  

## [web-msg-sender](https://github.com/walkor/web-msg-sender)  
[Live demo send page](http://workerman.net:3333/)  
[Live demo receive page](http://workerman.net/web-msg-sender.html)  
[Source code](https://github.com/walkor/web-msg-sender)  
![web-msg-sender](http://www.workerman.net/img/web-msg-sender.png)  

## [shadowsocks-php](https://github.com/walkor/shadowsocks-php)
[Source code](https://github.com/walkor/shadowsocks-php)  
![shadowsocks-php](http://www.workerman.net/img/shadowsocks-php.png)  

## [queue](https://github.com/walkor/workerman-queue)
[Source code](https://github.com/walkor/workerman-queue)  

## LICENSE

Workerman is released under the [MIT license](https://github.com/walkor/workerman/blob/master/MIT-LICENSE.txt).
