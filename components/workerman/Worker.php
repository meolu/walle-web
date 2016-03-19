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
namespace app\components\workerman;

require_once __DIR__ . '/Lib/Constants.php';

use app\components\workerman\events\EventInterface;
use app\components\workerman\connection\ConnectionInterface;
use app\components\workerman\connection\TcpConnection;
use app\components\workerman\connection\UdpConnection;
use app\components\workerman\lib\Timer;
use Exception;

/**
 * Worker class
 * A container for listening ports
 */
class Worker
{
    /**
     * Version.
     *
     * @var string
     */
    const VERSION = '3.3.0';

    /**
     * Status starting.
     *
     * @var int
     */
    const STATUS_STARTING = 1;

    /**
     * Status running.
     *
     * @var int
     */
    const STATUS_RUNNING = 2;

    /**
     * Status shutdown.
     *
     * @var int
     */
    const STATUS_SHUTDOWN = 4;

    /**
     * Status reloading.
     *
     * @var int
     */
    const STATUS_RELOADING = 8;

    /**
     * After sending the restart command to the child process KILL_WORKER_TIMER_TIME seconds,
     * if the process is still living then forced to kill.
     *
     * @var int
     */
    const KILL_WORKER_TIMER_TIME = 2;

    /**
     * Default backlog. Backlog is the maximum length of the queue of pending connections.
     *
     * @var int
     */
    const DEFAUL_BACKLOG = 1024;

    /**
     * Max udp package size.
     *
     * @var int
     */
    const MAX_UDP_PACKAGE_SIZE = 65535;

    /**
     * Worker id.
     *
     * @var int
     */
    public $id = 0;

    /**
     * Name of the worker processes.
     *
     * @var string
     */
    public $name = 'none';

    /**
     * Number of worker processes.
     *
     * @var int
     */
    public $count = 1;

    /**
     * Unix user of processes, needs appropriate privileges (usually root).
     *
     * @var string
     */
    public $user = '';

    /**
     * Unix group of processes, needs appropriate privileges (usually root).
     *
     * @var string
     */
    public $group = '';

    /**
     * reloadable.
     *
     * @var bool
     */
    public $reloadable = true;

    /**
     * reuse port.
     *
     * @var bool
     */
    public $reusePort = false;

    /**
     * Emitted when worker processes start.
     *
     * @var callback
     */
    public $onWorkerStart = null;

    /**
     * Emitted when a socket connection is successfully established.
     *
     * @var callback
     */
    public $onConnect = null;

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
     * Emitted when the send buffer becomes full.
     *
     * @var callback
     */
    public $onBufferFull = null;

    /**
     * Emitted when the send buffer becomes empty.
     *
     * @var callback
     */
    public $onBufferDrain = null;

    /**
     * Emitted when worker processes stoped.
     *
     * @var callback
     */
    public $onWorkerStop = null;

    /**
     * Emitted when worker processes get reload command.
     *
     * @var callback
     */
    public $onWorkerReload = null;

    /**
     * Transport layer protocol.
     *
     * @var string
     */
    public $transport = 'tcp';

    /**
     * Store all connections of clients.
     *
     * @var array
     */
    public $connections = [];

    /**
     * Application layer protocol.
     *
     * @var Protocols\ProtocolInterface
     */
    public $protocol = '';

    /**
     * Root path for autoload.
     *
     * @var string
     */
    protected $_autoloadRootPath = '';

    /**
     * Daemonize.
     *
     * @var bool
     */
    public static $daemonize = false;

    /**
     * Stdout file.
     *
     * @var string
     */
    public static $stdoutFile = '/dev/null';

    /**
     * The file to store master process PID.
     *
     * @var string
     */
    public static $pidFile = '';

    /**
     * Log file.
     *
     * @var mixed
     */
    public static $logFile = '';

    /**
     * Global event loop.
     *
     * @var Events\EventInterface
     */
    public static $globalEvent = null;

    /**
     * The PID of master process.
     *
     * @var int
     */
    protected static $_masterPid = 0;

    /**
     * Listening socket.
     *
     * @var resource
     */
    protected $_mainSocket = null;

    /**
     * Socket name. The format is like this http://0.0.0.0:80 .
     *
     * @var string
     */
    protected $_socketName = '';

    /**
     * Context of socket.
     *
     * @var resource
     */
    protected $_context = null;

    /**
     * All worker instances.
     *
     * @var array
     */
    protected static $_workers = [];

    /**
     * All worker porcesses pid.
     * The format is like this [worker_id=>[pid=>pid, pid=>pid, ..], ..]
     *
     * @var array
     */
    protected static $_pidMap = [];

    /**
     * All worker processes waiting for restart.
     * The format is like this [pid=>pid, pid=>pid].
     *
     * @var array
     */
    protected static $_pidsToRestart = [];

    /**
     * Mapping from PID to worker process ID.
     * The format is like this [worker_id=>[0=>$pid, 1=>$pid, ..], ..].
     *
     * @var array
     */
    protected static $_idMap = [];

    /**
     * Current status.
     *
     * @var int
     */
    protected static $_status = self::STATUS_STARTING;

    /**
     * Maximum length of the worker names.
     *
     * @var int
     */
    protected static $_maxWorkerNameLength = 12;

    /**
     * Maximum length of the socket names.
     *
     * @var int
     */
    protected static $_maxSocketNameLength = 12;

    /**
     * Maximum length of the process user names.
     *
     * @var int
     */
    protected static $_maxUserNameLength = 12;

    /**
     * The file to store status info of current worker process.
     *
     * @var string
     */
    protected static $_statisticsFile = '';

    /**
     * Start file.
     *
     * @var string
     */
    protected static $_startFile = '';

    /**
     * Status info of current worker process.
     *
     * @var array
     */
    protected static $_globalStatistics = [
        'start_timestamp'  => 0,
        'worker_exit_info' => []
    ];

    /**
     * Available event loops.
     *
     * @var array
     */
    protected static $_availableEventLoops = [
        'libevent',
        'ev'
    ];

    /**
     * Current eventLoop name.
     *
     * @var string
     */
    protected static $_eventLoopName = 'select';

    /**
     * PHP built-in protocols.
     *
     * @var array
     */
    protected static $_builtinTransports = [
        'tcp'   => 'tcp',
        'udp'   => 'udp',
        'unix'  => 'unix',
        'ssl'   => 'tcp',
        'tsl'   => 'tcp',
        'sslv2' => 'tcp',
        'sslv3' => 'tcp',
        'tls'   => 'tcp'
    ];

    /**
     * Run all worker instances.
     *
     * @return void
     */
    public static function runAll()
    {
        self::checkSapiEnv();
        self::init();
        self::parseCommand();
        self::daemonize();
        self::initWorkers();
        self::installSignal();
        self::saveMasterPid();
        self::forkWorkers();
        self::displayUI();
        self::resetStd();
        self::monitorWorkers();
    }

    /**
     * Check sapi.
     *
     * @return void
     */
    protected static function checkSapiEnv()
    {
        // Only for cli.
        if (php_sapi_name() != "cli") {
            exit("only run in command line mode \n");
        }
    }

    /**
     * Init.
     *
     * @return void
     */
    protected static function init()
    {
        // Start file.
        $backtrace        = debug_backtrace();
        self::$_startFile = $backtrace[count($backtrace) - 1]['file'];

        // Pid file.
        if (empty(self::$pidFile)) {
            self::$pidFile = __DIR__ . "/../" . str_replace('/', '_', self::$_startFile) . ".pid";
        }

        // Log file.
        if (empty(self::$logFile)) {
            self::$logFile = __DIR__ . '/../workerman.log';
        }
        touch(self::$logFile);
        chmod(self::$logFile, 0622);

        // State.
        self::$_status = self::STATUS_STARTING;

        // For statistics.
        self::$_globalStatistics['start_timestamp'] = time();
        self::$_statisticsFile                      = sys_get_temp_dir() . '/workerman.status';

        // Process title.
        self::setProcessTitle('WorkerMan: master process  start_file=' . self::$_startFile);

        // Init data for worker id.
        self::initId();

        // Timer init.
        Timer::init();
    }

    /**
     * Init All worker instances.
     *
     * @return void
     */
    protected static function initWorkers()
    {
        foreach (self::$_workers as $worker) {
            // Worker name.
            if (empty($worker->name)) {
                $worker->name = 'none';
            }

            // Get maximum length of worker name.
            $worker_name_length = strlen($worker->name);
            if (self::$_maxWorkerNameLength < $worker_name_length) {
                self::$_maxWorkerNameLength = $worker_name_length;
            }

            // Get maximum length of socket name.
            $socket_name_length = strlen($worker->getSocketName());
            if (self::$_maxSocketNameLength < $socket_name_length) {
                self::$_maxSocketNameLength = $socket_name_length;
            }

            // Get unix user of the worker process.
            if (empty($worker->user)) {
                $worker->user = self::getCurrentUser();
            } else {
                if (posix_getuid() !== 0 && $worker->user != self::getCurrentUser()) {
                    self::log('Waring: You must have the root privileges to change uid and gid.');
                }
            }

            // Get maximum length of unix user name.
            $user_name_length = strlen($worker->user);
            if (self::$_maxUserNameLength < $user_name_length) {
                self::$_maxUserNameLength = $user_name_length;
            }

            // Listen.
            if (!$worker->reusePort) {
                $worker->listen();
            }
        }
    }

    /**
     * Init idMap.
     * return void
     */
    protected static function initId()
    {
        foreach (self::$_workers as $worker_id => $worker) {
            self::$_idMap[$worker_id] = array_fill(0, $worker->count, 0);
        }
    }

    /**
     * Get unix user of current porcess.
     *
     * @return string
     */
    protected static function getCurrentUser()
    {
        $user_info = posix_getpwuid(posix_getuid());
        return $user_info['name'];
    }

    /**
     * Display staring UI.
     *
     * @return void
     */
    protected static function displayUI()
    {
        echo "\033[1A\n\033[K-----------------------\033[47;30m WORKERMAN \033[0m-----------------------------\n\033[0m";
        echo 'Workerman version:', Worker::VERSION, "          PHP version:", PHP_VERSION, "\n";
        echo "------------------------\033[47;30m WORKERS \033[0m-------------------------------\n";
        echo "\033[47;30muser\033[0m", str_pad('',
            self::$_maxUserNameLength + 2 - strlen('user')), "\033[47;30mworker\033[0m", str_pad('',
            self::$_maxWorkerNameLength + 2 - strlen('worker')), "\033[47;30mlisten\033[0m", str_pad('',
            self::$_maxSocketNameLength + 2 - strlen('listen')), "\033[47;30mprocesses\033[0m \033[47;30m", "status\033[0m\n";

        foreach (self::$_workers as $worker) {
            echo str_pad($worker->user, self::$_maxUserNameLength + 2), str_pad($worker->name,
                self::$_maxWorkerNameLength + 2), str_pad($worker->getSocketName(),
                self::$_maxSocketNameLength + 2), str_pad(' ' . $worker->count, 9), " \033[32;40m [OK] \033[0m\n";;
        }
        echo "----------------------------------------------------------------\n";
        if (self::$daemonize) {
            global $argv;
            $start_file = $argv[0];
            echo "Input \"php $start_file stop\" to quit. Start success.\n";
        } else {
            echo "Press Ctrl-C to quit. Start success.\n";
        }
    }

    /**
     * Parse command.
     * php yourfile.php start | stop | restart | reload | status
     *
     * @return void
     */
    protected static function parseCommand()
    {
        global $argv;
        // Check argv;
        $start_file = $argv[0];
        // walle's argv like:
        // Array
        // (
        // [0] => ./yii
        // [1] => walle/ws
        // [2] => start
        // [3] => -d
        // )
        if (!isset($argv[2])) {
            exit("Usage: ./yii walle/ws {start|stop|restart|reload|status|kill}\n");
        }

        // Get command.
        $command  = trim($argv[2]);
        $command2 = isset($argv[3]) ? $argv[3] : '';

        // Start command.
        $mode = '';
        if ($command === 'start') {
            if ($command2 === '-d') {
                $mode = 'in DAEMON mode';
            } else {
                $mode = 'in DEBUG mode';
            }
        }
        self::log("Workerman[$start_file] $command $mode");

        // Get master process PID.
        $master_pid      = @file_get_contents(self::$pidFile);
        $master_is_alive = $master_pid && @posix_kill($master_pid, 0);
        // Master is still alive?
        if ($master_is_alive) {
            if ($command === 'start') {
                self::log("Workerman[$start_file] already running");
                exit;
            }
        } elseif ($command !== 'start' && $command !== 'restart') {
            self::log("Workerman[$start_file] not run");
        }

        // Execure command.
        switch ($command) {
            case 'kill':
                exec("ps aux | grep $start_file | grep -v grep | awk '{print $2}' |xargs kill -SIGINT");
                exec("ps aux | grep $start_file | grep -v grep | awk '{print $2}' |xargs kill -SIGKILL");
                break;
            case 'start':
                if ($command2 === '-d') {
                    Worker::$daemonize = true;
                }
                break;
            case 'status':
                if (is_file(self::$_statisticsFile)) {
                    @unlink(self::$_statisticsFile);
                }
                // Master process will send status signal to all child processes.
                posix_kill($master_pid, SIGUSR2);
                // Waiting amoment.
                usleep(100000);
                // Display statisitcs data from a disk file.
                @readfile(self::$_statisticsFile);
                exit(0);
            case 'restart':
            case 'stop':
                self::log("Workerman[$start_file] is stoping ...");
                // Send stop signal to master process.
                $master_pid && posix_kill($master_pid, SIGINT);
                // Timeout.
                $timeout    = 5;
                $start_time = time();
                // Check master process is still alive?
                while (1) {
                    $master_is_alive = $master_pid && posix_kill($master_pid, 0);
                    if ($master_is_alive) {
                        // Timeout?
                        if (time() - $start_time >= $timeout) {
                            self::log("Workerman[$start_file] stop fail");
                            exit;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    // Stop success.
                    self::log("Workerman[$start_file] stop success");
                    if ($command === 'stop') {
                        exit(0);
                    }
                    if ($command2 === '-d') {
                        Worker::$daemonize = true;
                    }
                    break;
                }
                break;
            case 'reload':
                posix_kill($master_pid, SIGUSR1);
                self::log("Workerman[$start_file] reload");
                exit;
            default :
                exit("Usage: php yourfile.php {start|stop|restart|reload|status|kill}\n");
        }
    }

    /**
     * Install signal handler.
     *
     * @return void
     */
    protected static function installSignal()
    {
        // stop
        pcntl_signal(SIGINT, ['\Workerman\Worker', 'signalHandler'], false);
        // reload
        pcntl_signal(SIGUSR1, ['\Workerman\Worker', 'signalHandler'], false);
        // status
        pcntl_signal(SIGUSR2, ['\Workerman\Worker', 'signalHandler'], false);
        // ignore
        pcntl_signal(SIGPIPE, SIG_IGN, false);
    }

    /**
     * Reinstall signal handler.
     *
     * @return void
     */
    protected static function reinstallSignal()
    {
        // uninstall stop signal handler
        pcntl_signal(SIGINT, SIG_IGN, false);
        // uninstall reload signal handler
        pcntl_signal(SIGUSR1, SIG_IGN, false);
        // uninstall  status signal handler
        pcntl_signal(SIGUSR2, SIG_IGN, false);
        // reinstall stop signal handler
        self::$globalEvent->add(SIGINT, EventInterface::EV_SIGNAL, ['\Workerman\Worker', 'signalHandler']);
        //  uninstall  reload signal handler
        self::$globalEvent->add(SIGUSR1, EventInterface::EV_SIGNAL, ['\Workerman\Worker', 'signalHandler']);
        // uninstall  status signal handler
        self::$globalEvent->add(SIGUSR2, EventInterface::EV_SIGNAL, ['\Workerman\Worker', 'signalHandler']);
    }

    /**
     * Signal hander.
     *
     * @param int $signal
     */
    public static function signalHandler($signal)
    {
        switch ($signal) {
            // Stop.
            case SIGINT:
                self::stopAll();
                break;
            // Reload.
            case SIGUSR1:
                self::$_pidsToRestart = self::getAllWorkerPids();
                self::reload();
                break;
            // Show status.
            case SIGUSR2:
                self::writeStatisticsToStatusFile();
                break;
        }
    }

    /**
     * Run as deamon mode.
     *
     * @throws Exception
     */
    protected static function daemonize()
    {
        if (!self::$daemonize) {
            return;
        }
        umask(0);
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === posix_setsid()) {
            throw new Exception("setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = pcntl_fork();
        if (-1 === $pid) {
            throw new Exception("fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
    }

    /**
     * Redirect standard input and output.
     *
     * @throws Exception
     */
    protected static function resetStd()
    {
        if (!self::$daemonize) {
            return;
        }
        global $STDOUT, $STDERR;
        $handle = fopen(self::$stdoutFile, "a");
        if ($handle) {
            unset($handle);
            @fclose(STDOUT);
            @fclose(STDERR);
            $STDOUT = fopen(self::$stdoutFile, "a");
            $STDERR = fopen(self::$stdoutFile, "a");
        } else {
            throw new Exception('can not open stdoutFile ' . self::$stdoutFile);
        }
    }

    /**
     * Save pid.
     *
     * @throws Exception
     */
    protected static function saveMasterPid()
    {
        self::$_masterPid = posix_getpid();
        if (false === @file_put_contents(self::$pidFile, self::$_masterPid)) {
            throw new Exception('can not save pid to ' . self::$pidFile);
        }
    }

    /**
     * Get event loop name.
     *
     * @return string
     */
    protected static function getEventLoopName()
    {
        foreach (self::$_availableEventLoops as $name) {
            if (extension_loaded($name)) {
                self::$_eventLoopName = $name;
                break;
            }
        }
        return self::$_eventLoopName;
    }

    /**
     * Get all pids of worker processes.
     *
     * @return array
     */
    protected static function getAllWorkerPids()
    {
        $pid_array = [];
        foreach (self::$_pidMap as $worker_pid_array) {
            foreach ($worker_pid_array as $worker_pid) {
                $pid_array[$worker_pid] = $worker_pid;
            }
        }
        return $pid_array;
    }

    /**
     * Fork some worker processes.
     *
     * @return void
     */
    protected static function forkWorkers()
    {
        foreach (self::$_workers as $worker) {
            if (self::$_status === self::STATUS_STARTING) {
                if (empty($worker->name)) {
                    $worker->name = $worker->getSocketName();
                }
                $worker_name_length = strlen($worker->name);
                if (self::$_maxWorkerNameLength < $worker_name_length) {
                    self::$_maxWorkerNameLength = $worker_name_length;
                }
            }

            while (count(self::$_pidMap[$worker->workerId]) < $worker->count) {
                static::forkOneWorker($worker);
            }
        }
    }

    /**
     * Fork one worker process.
     *
     * @param Worker $worker
     * @throws Exception
     */
    protected static function forkOneWorker($worker)
    {
        $pid = pcntl_fork();
        // Get available worker id.
        $id = self::getId($worker->workerId, 0);
        // For master process.
        if ($pid > 0) {
            self::$_pidMap[$worker->workerId][$pid] = $pid;
            self::$_idMap[$worker->workerId][$id]   = $pid;
        } // For child processes.
        elseif (0 === $pid) {
            if ($worker->reusePort) {
                $worker->listen();
            }
            if (self::$_status === self::STATUS_STARTING) {
                self::resetStd();
            }
            self::$_pidMap  = [];
            self::$_workers = [$worker->workerId => $worker];
            Timer::delAll();
            self::setProcessTitle('WorkerMan: worker process  ' . $worker->name . ' ' . $worker->getSocketName());
            $worker->setUserAndGroup();
            $worker->id = $id;
            $worker->run();
            exit(250);
        } else {
            throw new Exception("forkOneWorker fail");
        }
    }

    /**
     * Get worker id.
     *
     * @param int $worker_id
     * @param int $pid
     */
    protected static function getId($worker_id, $pid)
    {
        $id = array_search($pid, self::$_idMap[$worker_id]);
        if ($id === false) {
            echo "getId fail\n";
        }
        return $id;
    }

    /**
     * Set unix user and group for current process.
     *
     * @return void
     */
    public function setUserAndGroup()
    {
        // Get uid.
        $user_info = posix_getpwnam($this->user);
        if (!$user_info) {
            self::log("Waring: User {$this->user} not exsits");
            return;
        }
        $uid = $user_info['uid'];
        // Get gid.
        if ($this->group) {
            $group_info = posix_getgrnam($this->group);
            if (!$group_info) {
                self::log("Waring: Group {$this->group} not exsits");
                return;
            }
            $gid = $group_info['gid'];
        } else {
            $gid = $user_info['gid'];
        }

        // Set uid and gid.
        if ($uid != posix_getuid() || $gid != posix_getgid()) {
            if (!posix_setgid($gid) || !posix_initgroups($user_info['name'], $gid) || !posix_setuid($uid)) {
                self::log("Waring: change gid or uid fail.");
            }
        }
    }

    /**
     * Set process name.
     *
     * @param string $title
     * @return void
     */
    protected static function setProcessTitle($title)
    {
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        elseif (extension_loaded('proctitle') && function_exists('setproctitle')) {
            @setproctitle($title);
        }
    }

    /**
     * Monitor all child processes.
     *
     * @return void
     */
    protected static function monitorWorkers()
    {
        self::$_status = self::STATUS_RUNNING;
        while (1) {
            // Calls signal handlers for pending signals.
            pcntl_signal_dispatch();
            // Suspends execution of the current process until a child has exited, or until a signal is delivered
            $status = 0;
            $pid    = pcntl_wait($status, WUNTRACED);
            // Calls signal handlers for pending signals again.
            pcntl_signal_dispatch();
            // If a child has already exited.
            if ($pid > 0) {
                // Find out witch worker process exited.
                foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
                    if (isset($worker_pid_array[$pid])) {
                        $worker = self::$_workers[$worker_id];
                        // Exit status.
                        if ($status !== 0) {
                            self::log("worker[" . $worker->name . ":$pid] exit with status $status");
                        }

                        // For Statistics.
                        if (!isset(self::$_globalStatistics['worker_exit_info'][$worker_id][$status])) {
                            self::$_globalStatistics['worker_exit_info'][$worker_id][$status] = 0;
                        }
                        self::$_globalStatistics['worker_exit_info'][$worker_id][$status]++;

                        // Clear process data.
                        unset(self::$_pidMap[$worker_id][$pid]);

                        // Mark id is available.
                        $id                            = self::getId($worker_id, $pid);
                        self::$_idMap[$worker_id][$id] = 0;

                        break;
                    }
                }
                // Is still running state then fork a new worker process.
                if (self::$_status !== self::STATUS_SHUTDOWN) {
                    self::forkWorkers();
                    // If reloading continue.
                    if (isset(self::$_pidsToRestart[$pid])) {
                        unset(self::$_pidsToRestart[$pid]);
                        self::reload();
                    }
                } else {
                    // If shutdown state and all child processes exited then master process exit.
                    if (!self::getAllWorkerPids()) {
                        self::exitAndClearAll();
                    }
                }
            } else {
                // If shutdown state and all child processes exited then master process exit.
                if (self::$_status === self::STATUS_SHUTDOWN && !self::getAllWorkerPids()) {
                    self::exitAndClearAll();
                }
            }
        }
    }

    /**
     * Exit current process.
     *
     * @return void
     */
    protected static function exitAndClearAll()
    {
        foreach (self::$_workers as $worker) {
            $socket_name = $worker->getSocketName();
            if ($worker->transport === 'unix' && $socket_name) {
                list(, $address) = explode(':', $socket_name, 2);
                @unlink($address);
            }
        }
        @unlink(self::$pidFile);
        self::log("Workerman[" . basename(self::$_startFile) . "] has been stopped");
        exit(0);
    }

    /**
     * Execute reload.
     *
     * @return void
     */
    protected static function reload()
    {
        // For master process.
        if (self::$_masterPid === posix_getpid()) {
            // Set reloading state.
            if (self::$_status !== self::STATUS_RELOADING && self::$_status !== self::STATUS_SHUTDOWN) {
                self::log("Workerman[" . basename(self::$_startFile) . "] reloading");
                self::$_status = self::STATUS_RELOADING;
            }

            // Send reload signal to all child processes.
            $reloadable_pid_array = [];
            foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
                $worker = self::$_workers[$worker_id];
                if ($worker->reloadable) {
                    foreach ($worker_pid_array as $pid) {
                        $reloadable_pid_array[$pid] = $pid;
                    }
                } else {
                    foreach ($worker_pid_array as $pid) {
                        // Send reload signal to a worker process which reloadable is false.
                        posix_kill($pid, SIGUSR1);
                    }
                }
            }

            // Get all pids that are waiting reload.
            self::$_pidsToRestart = array_intersect(self::$_pidsToRestart, $reloadable_pid_array);

            // Reload complete.
            if (empty(self::$_pidsToRestart)) {
                if (self::$_status !== self::STATUS_SHUTDOWN) {
                    self::$_status = self::STATUS_RUNNING;
                }
                return;
            }
            // Continue reload.
            $one_worker_pid = current(self::$_pidsToRestart);
            // Send reload signal to a worker process.
            posix_kill($one_worker_pid, SIGUSR1);
            // If the process does not exit after self::KILL_WORKER_TIMER_TIME seconds try to kill it.
            Timer::add(self::KILL_WORKER_TIMER_TIME, 'posix_kill', [$one_worker_pid, SIGKILL], false);
        } // For child processes.
        else {
            $worker = current(self::$_workers);
            // Try to emit onWorkerReload callback.
            if ($worker->onWorkerReload) {
                try {
                    call_user_func($worker->onWorkerReload, $worker);
                } catch (\Exception $e) {
                    echo $e;
                    exit(250);
                }
            }

            if ($worker->reloadable) {
                self::stopAll();
            }
        }
    }

    /**
     * Stop.
     *
     * @return void
     */
    public static function stopAll()
    {
        self::$_status = self::STATUS_SHUTDOWN;
        // For master process.
        if (self::$_masterPid === posix_getpid()) {
            self::log("Workerman[" . basename(self::$_startFile) . "] Stopping ...");
            $worker_pid_array = self::getAllWorkerPids();
            // Send stop signal to all child processes.
            foreach ($worker_pid_array as $worker_pid) {
                posix_kill($worker_pid, SIGINT);
                Timer::add(self::KILL_WORKER_TIMER_TIME, 'posix_kill', [$worker_pid, SIGKILL], false);
            }
        } // For child processes.
        else {
            // Execute exit.
            foreach (self::$_workers as $worker) {
                $worker->stop();
            }
            exit(0);
        }
    }

    /**
     * Write statistics data to disk.
     *
     * @return void
     */
    protected static function writeStatisticsToStatusFile()
    {
        // For master process.
        if (self::$_masterPid === posix_getpid()) {
            $loadavg = sys_getloadavg();
            file_put_contents(self::$_statisticsFile,
                "---------------------------------------GLOBAL STATUS--------------------------------------------\n");
            file_put_contents(self::$_statisticsFile,
                'Workerman version:' . Worker::VERSION . "          PHP version:" . PHP_VERSION . "\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile, 'start time:' . date('Y-m-d H:i:s',
                    self::$_globalStatistics['start_timestamp']) . '   run ' . floor((time() - self::$_globalStatistics['start_timestamp']) / (24 * 60 * 60)) . ' days ' . floor(((time() - self::$_globalStatistics['start_timestamp']) % (24 * 60 * 60)) / (60 * 60)) . " hours   \n",
                FILE_APPEND);
            $load_str = 'load average: ' . implode(", ", $loadavg);
            file_put_contents(self::$_statisticsFile,
                str_pad($load_str, 33) . 'event-loop:' . self::getEventLoopName() . "\n", FILE_APPEND);
            file_put_contents(self::$_statisticsFile,
                count(self::$_pidMap) . ' workers       ' . count(self::getAllWorkerPids()) . " processes\n",
                FILE_APPEND);
            file_put_contents(self::$_statisticsFile,
                str_pad('worker_name', self::$_maxWorkerNameLength) . " exit_status     exit_count\n", FILE_APPEND);
            foreach (self::$_pidMap as $worker_id => $worker_pid_array) {
                $worker = self::$_workers[$worker_id];
                if (isset(self::$_globalStatistics['worker_exit_info'][$worker_id])) {
                    foreach (self::$_globalStatistics['worker_exit_info'][$worker_id] as $worker_exit_status => $worker_exit_count) {
                        file_put_contents(self::$_statisticsFile,
                            str_pad($worker->name, self::$_maxWorkerNameLength) . " " . str_pad($worker_exit_status,
                                16) . " $worker_exit_count\n", FILE_APPEND);
                    }
                } else {
                    file_put_contents(self::$_statisticsFile,
                        str_pad($worker->name, self::$_maxWorkerNameLength) . " " . str_pad(0, 16) . " 0\n",
                        FILE_APPEND);
                }
            }
            file_put_contents(self::$_statisticsFile,
                "---------------------------------------PROCESS STATUS-------------------------------------------\n",
                FILE_APPEND);
            file_put_contents(self::$_statisticsFile,
                "pid\tmemory  " . str_pad('listening', self::$_maxSocketNameLength) . " " . str_pad('worker_name',
                    self::$_maxWorkerNameLength) . " connections " . str_pad('total_request',
                    13) . " " . str_pad('send_fail', 9) . " " . str_pad('throw_exception', 15) . "\n", FILE_APPEND);

            chmod(self::$_statisticsFile, 0722);

            foreach (self::getAllWorkerPids() as $worker_pid) {
                posix_kill($worker_pid, SIGUSR2);
            }
            return;
        }

        // For child processes.
        /** @var Worker $worker */
        $worker           = current(self::$_workers);
        $wrker_status_str = posix_getpid() . "\t" . str_pad(round(memory_get_usage(true) / (1024 * 1024), 2) . "M",
                7) . " " . str_pad($worker->getSocketName(),
                self::$_maxSocketNameLength) . " " . str_pad(($worker->name === $worker->getSocketName() ? 'none' : $worker->name),
                self::$_maxWorkerNameLength) . " ";
        $wrker_status_str .= str_pad(ConnectionInterface::$statistics['connection_count'],
                11) . " " . str_pad(ConnectionInterface::$statistics['total_request'],
                14) . " " . str_pad(ConnectionInterface::$statistics['send_fail'],
                9) . " " . str_pad(ConnectionInterface::$statistics['throw_exception'], 15) . "\n";
        file_put_contents(self::$_statisticsFile, $wrker_status_str, FILE_APPEND);
    }

    /**
     * Check errors when current process exited.
     *
     * @return void
     */
    public static function checkErrors()
    {
        if (self::STATUS_SHUTDOWN != self::$_status) {
            $error_msg = "WORKER EXIT UNEXPECTED ";
            $errors    = error_get_last();
            if ($errors && ($errors['type'] === E_ERROR ||
                    $errors['type'] === E_PARSE ||
                    $errors['type'] === E_CORE_ERROR ||
                    $errors['type'] === E_COMPILE_ERROR ||
                    $errors['type'] === E_RECOVERABLE_ERROR)
            ) {
                $error_msg .= self::getErrorType($errors['type']) . " {$errors['message']} in {$errors['file']} on line {$errors['line']}";
            }
            self::log($error_msg);
        }
    }

    /**
     * Get error message by error code.
     *
     * @param integer $type
     * @return string
     */
    protected static function getErrorType($type)
    {
        switch ($type) {
            case E_ERROR: // 1 //
                return 'E_ERROR';
            case E_WARNING: // 2 //
                return 'E_WARNING';
            case E_PARSE: // 4 //
                return 'E_PARSE';
            case E_NOTICE: // 8 //
                return 'E_NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'E_CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR: // 64 //
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING: // 128 //
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'E_USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'E_USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'E_USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'E_USER_DEPRECATED';
        }
        return "";
    }

    /**
     * Log.
     *
     * @param string $msg
     * @return void
     */
    protected static function log($msg)
    {
        $msg = $msg . "\n";
        if (!self::$daemonize) {
            echo $msg;
        }
        file_put_contents(self::$logFile, date('Y-m-d H:i:s') . " " . $msg, FILE_APPEND | LOCK_EX);
    }

    /**
     * Construct.
     *
     * @param string $socket_name
     * @param array  $context_option
     */
    public function __construct($socket_name = '', $context_option = [])
    {
        // Save all worker instances.
        $this->workerId                  = spl_object_hash($this);
        self::$_workers[$this->workerId] = $this;
        self::$_pidMap[$this->workerId]  = [];

        // Get autoload root path.
        $backrace                = debug_backtrace();
        $this->_autoloadRootPath = dirname($backrace[0]['file']);

        // Context for socket.
        if ($socket_name) {
            $this->_socketName = $socket_name;
            if (!isset($context_option['socket']['backlog'])) {
                $context_option['socket']['backlog'] = self::DEFAUL_BACKLOG;
            }
            $this->_context = stream_context_create($context_option);
        }

        // Set an empty onMessage callback.
        $this->onMessage = function () {
        };
    }

    /**
     * Listen port.
     *
     * @throws Exception
     */
    public function listen()
    {
        if (!$this->_socketName || $this->_mainSocket) {
            return;
        }

        // Autoload.
//        Autoloader::setRootPath($this->_autoloadRootPath);

        $local_socket = $this->_socketName;
        // Get the application layer communication protocol and listening address.
        list($scheme, $address) = explode(':', $this->_socketName, 2);
        // Check application layer protocol class.
        if (!isset(self::$_builtinTransports[$scheme])) {
            $scheme         = ucfirst($scheme);
            $this->protocol = '\\Protocols\\' . $scheme;
            if (!class_exists($this->protocol)) {
                $this->protocol = "\\Workerman\\Protocols\\$scheme";
                if (!class_exists($this->protocol)) {
                    throw new Exception("class \\Protocols\\$scheme not exist");
                }
            }
            $local_socket = $this->transport . ":" . $address;
        } else {
            $this->transport = self::$_builtinTransports[$scheme];
        }

        // Flag.
        $flags  = $this->transport === 'udp' ? STREAM_SERVER_BIND : STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        $errno  = 0;
        $errmsg = '';
        // SO_REUSEPORT.
        if ($this->reusePort) {
            stream_context_set_option($this->_context, 'socket', 'so_reuseport', 1);
        }
        if ($this->transport === 'unix') {
            umask(0);
            list(, $address) = explode(':', $this->_socketName, 2);
            if (!is_file($address)) {
                register_shutdown_function(function () use ($address) {
                    @unlink($address);
                });
            }
        }
        // Create an Internet or Unix domain server socket.
        $this->_mainSocket = stream_socket_server($local_socket, $errno, $errmsg, $flags, $this->_context);
        if (!$this->_mainSocket) {
            throw new Exception($errmsg);
        }

        // Try to open keepalive for tcp and disable Nagle algorithm.
        if (function_exists('socket_import_stream') && $this->transport === 'tcp') {
            $socket = socket_import_stream($this->_mainSocket);
            @socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            @socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        }

        // Non blocking.
        stream_set_blocking($this->_mainSocket, 0);

        // Register a listener to be notified when server socket is ready to read.
        if (self::$globalEvent) {
            if ($this->transport !== 'udp') {
                self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ, [$this, 'acceptConnection']);
            } else {
                self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ,
                    [$this, 'acceptUdpConnection']);
            }
        }
    }

    /**
     * Get socket name.
     *
     * @return string
     */
    public function getSocketName()
    {
        return $this->_socketName ? lcfirst($this->_socketName) : 'none';
    }

    /**
     * Run worker instance.
     *
     * @return void
     */
    public function run()
    {
        //Update process state.
        self::$_status = self::STATUS_RUNNING;

        // Eegister shutdown function for checking errors.
        register_shutdown_function(["\\Workerman\\Worker", 'checkErrors']);

        // Set autoload root path.
//        Autoloader::setRootPath($this->_autoloadRootPath);

        // Create a global event loop.
        if (!self::$globalEvent) {
            $eventLoopClass    = "\\Workerman\\Events\\" . ucfirst(self::getEventLoopName());
            self::$globalEvent = new $eventLoopClass;
            // Register a listener to be notified when server socket is ready to read.
            if ($this->_socketName) {
                if ($this->transport !== 'udp') {
                    self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ,
                        [$this, 'acceptConnection']);
                } else {
                    self::$globalEvent->add($this->_mainSocket, EventInterface::EV_READ,
                        [$this, 'acceptUdpConnection']);
                }
            }
        }

        // Reinstall signal.
        self::reinstallSignal();

        // Init Timer.
        Timer::init(self::$globalEvent);

        // Try to emit onWorkerStart callback.
        if ($this->onWorkerStart) {
            try {
                call_user_func($this->onWorkerStart, $this);
            } catch (\Exception $e) {
                echo $e;
                exit(250);
            }
        }

        // Main loop.
        self::$globalEvent->loop();
    }

    /**
     * Stop current worker instance.
     *
     * @return void
     */
    public function stop()
    {
        // Try to emit onWorkerStop callback.
        if ($this->onWorkerStop) {
            try {
                call_user_func($this->onWorkerStop, $this);
            } catch (\Exception $e) {
                echo $e;
                exit(250);
            }
        }
        // Remove listener for server socket.
        self::$globalEvent->del($this->_mainSocket, EventInterface::EV_READ);
        @fclose($this->_mainSocket);
    }

    /**
     * Accept a connection.
     *
     * @param resource $socket
     * @return void
     */
    public function acceptConnection($socket)
    {
        // Accept a connection on server socket.
        $new_socket = @stream_socket_accept($socket, 0, $remote_address);
        // Thundering herd.
        if (false === $new_socket) {
            return;
        }

        // TcpConnection.
        $connection                         = new TcpConnection($new_socket, $remote_address);
        $this->connections[$connection->id] = $connection;
        $connection->worker                 = $this;
        $connection->protocol               = $this->protocol;
        $connection->onMessage              = $this->onMessage;
        $connection->onClose                = $this->onClose;
        $connection->onError                = $this->onError;
        $connection->onBufferDrain          = $this->onBufferDrain;
        $connection->onBufferFull           = $this->onBufferFull;

        // Try to emit onConnect callback.
        if ($this->onConnect) {
            try {
                call_user_func($this->onConnect, $connection);
            } catch (\Exception $e) {
                echo $e;
                exit(250);
            }
        }
    }

    /**
     * For udp package.
     *
     * @param resource $socket
     * @return bool
     */
    public function acceptUdpConnection($socket)
    {
        $recv_buffer = stream_socket_recvfrom($socket, self::MAX_UDP_PACKAGE_SIZE, 0, $remote_address);
        if (false === $recv_buffer || empty($remote_address)) {
            return false;
        }
        // UdpConnection.
        $connection           = new UdpConnection($socket, $remote_address);
        $connection->protocol = $this->protocol;
        if ($this->onMessage) {
            if ($this->protocol) {
                $parser      = $this->protocol;
                $recv_buffer = $parser::decode($recv_buffer, $connection);
            }
            ConnectionInterface::$statistics['total_request']++;
            try {
                call_user_func($this->onMessage, $connection, $recv_buffer);
            } catch (\Exception $e) {
                echo $e;
                exit(250);
            }
        }

        return true;
    }
}
