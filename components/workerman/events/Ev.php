<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author  有个鬼<42765633@qq.com>
 * @link    http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace app\components\workerman\events;

/**
 * ev eventloop
 */
class Ev implements EventInterface
{
    /**
     * All listeners for read/write event.
     *
     * @var array
     */
    protected $_allEvents = array();

    /**
     * Event listeners of signal.
     *
     * @var array
     */
    protected $_eventSignal = array();

    /**
     * All timer event listeners.
     * [func, args, event, flag, time_interval]
     *
     * @var array
     */
    protected $_eventTimer = array();

    /**
     * Timer id.
     *
     * @var int
     */
    protected static $_timerId = 1;

    /**
     * Add a timer.
     * {@inheritdoc}
     */
    public function add($fd, $flag, $func, $args = null)
    {
        $callback = function ($event, $socket) use ($fd, $func) {
            try {
                call_user_func($func, $fd);
            } catch (\Exception $e) {
                echo $e;
                exit(250);
            }
        };

        switch ($flag) {
            case self::EV_SIGNAL:
                $event                   = new \EvSignal($fd, $callback);
                $this->_eventSignal[$fd] = $event;
                return true;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE:
                $repeat                             = $flag == self::EV_TIMER_ONCE ? 0 : $fd;
                $param                              = array($func, (array)$args, $flag, $fd, self::$_timerId);
                $event                              = new \EvTimer($fd, $repeat, array($this, 'timerCallback'), $param);
                $this->_eventTimer[self::$_timerId] = $event;
                return self::$_timerId++;
            default :
                $fd_key                           = (int)$fd;
                $real_flag                        = $flag === self::EV_READ ? \Ev::READ : \Ev::WRITE;
                $event                            = new \EvIo($fd, $real_flag, $callback);
                $this->_allEvents[$fd_key][$flag] = $event;
                return true;
        }

    }

    /**
     * Remove a timer.
     * {@inheritdoc}
     */
    public function del($fd, $flag)
    {
        switch ($flag) {
            case self::EV_READ:
            case self::EV_WRITE:
                $fd_key = (int)$fd;
                if (isset($this->_allEvents[$fd_key][$flag])) {
                    $this->_allEvents[$fd_key][$flag]->stop();
                    unset($this->_allEvents[$fd_key][$flag]);
                }
                if (empty($this->_allEvents[$fd_key])) {
                    unset($this->_allEvents[$fd_key]);
                }
                break;
            case  self::EV_SIGNAL:
                $fd_key = (int)$fd;
                if (isset($this->_eventSignal[$fd_key])) {
                    $this->_allEvents[$fd_key][$flag]->stop();
                    unset($this->_eventSignal[$fd_key]);
                }
                break;
            case self::EV_TIMER:
            case self::EV_TIMER_ONCE:
                if (isset($this->_eventTimer[$fd])) {
                    $this->_eventTimer[$fd]->stop();
                    unset($this->_eventTimer[$fd]);
                }
                break;
        }
        return true;
    }

    /**
     * Timer callback.
     *
     * @param \EvWatcher $event
     */
    public function timerCallback($event)
    {
        $param    = $event->data;
        $timer_id = $param[4];
        if ($param[2] === self::EV_TIMER_ONCE) {
            $this->_eventTimer[$timer_id]->stop();
            unset($this->_eventTimer[$timer_id]);
        }
        try {
            call_user_func_array($param[0], $param[1]);
        } catch (\Exception $e) {
            echo $e;
            exit(250);
        }
    }

    /**
     * Remove all timers.
     *
     * @return void
     */
    public function clearAllTimer()
    {
        foreach ($this->_eventTimer as $event) {
            $event->stop();
        }
        $this->_eventTimer = array();
    }

    /**
     * Main loop.
     *
     * @see EventInterface::loop()
     */
    public function loop()
    {
        \Ev::run();
    }
}
