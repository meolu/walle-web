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
namespace app\components\workerman\events;

/**
 * select eventloop
 */
class Select implements EventInterface
{
    /**
     * All listeners for read/write event.
     *
     * @var array
     */
    public $_allEvents = array();

    /**
     * Event listeners of signal.
     *
     * @var array
     */
    public $_signalEvents = array();

    /**
     * Fds waiting for read event.
     *
     * @var array
     */
    protected $_readFds = array();

    /**
     * Fds waiting for write event.
     *
     * @var array
     */
    protected $_writeFds = array();

    /**
     * Timer scheduler.
     * {['data':timer_id, 'priority':run_timestamp], ..}
     *
     * @var \SplPriorityQueue
     */
    protected $_scheduler = null;

    /**
     * All timer event listeners.
     * [[func, args, flag, timer_interval], ..]
     *
     * @var array
     */
    protected $_task = array();

    /**
     * Timer id.
     *
     * @var int
     */
    protected $_timerId = 1;

    /**
     * Select timeout.
     *
     * @var int
     */
    protected $_selectTimeout = 100000000;

    /**
     * Paired socket channels
     *
     * @var array
     */
    protected $channel = array();

    /**
     * Construct.
     */
    public function __construct()
    {
        // Create a pipeline and put into the collection of the read to read the descriptor to avoid empty polling.
        $this->channel = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($this->channel) {
            stream_set_blocking($this->channel[0], 0);
            $this->_readFds[0] = $this->channel[0];
        }
        // Init SplPriorityQueue.
        $this->_scheduler = new \SplPriorityQueue();
        $this->_scheduler->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
    }

    /**
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = array())
    {
        switch ($flag) {
            case self::EV_READ:
                $fd_key                           = (int)$fd;
                $this->_allEvents[$fd_key][$flag] = array($func, $fd);
                $this->_readFds[$fd_key]          = $fd;
                break;
            case self::EV_WRITE:
                $fd_key                           = (int)$fd;
                $this->_allEvents[$fd_key][$flag] = array($func, $fd);
                $this->_writeFds[$fd_key]         = $fd;
                break;
            case self::EV_SIGNAL:
                $fd_key                              = (int)$fd;
                $this->_signalEvents[$fd_key][$flag] = array($func, $fd);
                pcntl_signal($fd, array($this, 'signalHandler'));
                break;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE:
                $run_time = microtime(true) + $fd;
                $this->_scheduler->insert($this->_timerId, -$run_time);
                $this->_task[$this->_timerId] = array($func, (array)$args, $flag, $fd);
                $this->tick();
                return $this->_timerId++;
        }

        return true;
    }

    /**
     * Signal handler.
     *
     * @param int $signal
     */
    public function signalHandler($signal)
    {
        call_user_func_array($this->_signalEvents[$signal][self::EV_SIGNAL][0], array($signal));
    }

    /**
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        $fd_key = (int)$fd;
        switch ($flag) {
            case self::EV_READ:
                unset($this->_allEvents[$fd_key][$flag], $this->_readFds[$fd_key]);
                if (empty($this->_allEvents[$fd_key])) {
                    unset($this->_allEvents[$fd_key]);
                }
                return true;
            case self::EV_WRITE:
                unset($this->_allEvents[$fd_key][$flag], $this->_writeFds[$fd_key]);
                if (empty($this->_allEvents[$fd_key])) {
                    unset($this->_allEvents[$fd_key]);
                }
                return true;
            case self::EV_SIGNAL:
                unset($this->_signalEvents[$fd_key]);
                pcntl_signal($fd, SIG_IGN);
                break;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE;
                unset($this->_task[$fd_key]);
                return true;
        }
        return false;
    }

    /**
     * Tick for timer.
     *
     * @return void
     */
    protected function tick()
    {
        while (!$this->_scheduler->isEmpty()) {
            $scheduler_data = $this->_scheduler->top();
            $timer_id       = $scheduler_data['data'];
            $next_run_time  = -$scheduler_data['priority'];
            $time_now       = microtime(true);
            if ($time_now >= $next_run_time) {
                $this->_scheduler->extract();

                if (!isset($this->_task[$timer_id])) {
                    continue;
                }

                // [func, args, flag, timer_interval]
                $task_data = $this->_task[$timer_id];
                if ($task_data[2] === self::EV_TIMER) {
                    $next_run_time = $time_now + $task_data[3];
                    $this->_scheduler->insert($timer_id, -$next_run_time);
                }
                call_user_func_array($task_data[0], $task_data[1]);
                if (isset($this->_task[$timer_id]) && $task_data[2] === self::EV_TIMER_ONCE) {
                    $this->del($timer_id, self::EV_TIMER_ONCE);
                }
                continue;
            } else {
                $this->_selectTimeout = ($next_run_time - $time_now) * 1000000;
                return;
            }
        }
        $this->_selectTimeout = 100000000;
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllTimer()
    {
        $this->_scheduler = new \SplPriorityQueue();
        $this->_scheduler->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
        $this->_task = array();
    }

    /**
     * {@inheritdoc}
     */
    public function loop()
    {
        $e = null;
        while (1) {
            // Calls signal handlers for pending signals
            pcntl_signal_dispatch();

            $read  = $this->_readFds;
            $write = $this->_writeFds;
            // Waiting read/write/signal/timeout events.
            $ret = @stream_select($read, $write, $e, 0, $this->_selectTimeout);

            if (!$this->_scheduler->isEmpty()) {
                $this->tick();
            }

            if (!$ret) {
                continue;
            }

            foreach ($read as $fd) {
                $fd_key = (int)$fd;
                if (isset($this->_allEvents[$fd_key][self::EV_READ])) {
                    call_user_func_array($this->_allEvents[$fd_key][self::EV_READ][0],
                        array($this->_allEvents[$fd_key][self::EV_READ][1]));
                }
            }

            foreach ($write as $fd) {
                $fd_key = (int)$fd;
                if (isset($this->_allEvents[$fd_key][self::EV_WRITE])) {
                    call_user_func_array($this->_allEvents[$fd_key][self::EV_WRITE][0],
                        array($this->_allEvents[$fd_key][self::EV_WRITE][1]));
                }
            }
        }
    }
}
