<?php

namespace Ss\Http;

use Ss\Means\FileStream;
use Ss\MVC\Cache;

class SessionHandler implements \SessionHandlerInterface
{
    private $sessionName = null;
    private $savePath = null;
    private $fp;

    const FILE = 1;
    const REDIS = 0;
    private $mode = self::REDIS;
    private $sessionExpire = 86400;

    public function __construct($mode = self::REDIS)
    {
        if ($mode != self::REDIS) {
            $this->mode = self::FILE;
        }
    }

    public function close()
    {
        // TODO: Implement close() method.
        if ($this->mode) {
            $this->fp->unlock();
            $this->fp->close();
            $this->fp = null;
        } else {
            Cache::recycleCacheSource();
        }
    }

    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
        if ($this->mode) {
            $this->fp->truncate(0);
            $this->fp->seek(0);
        }
    }

    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
        //后续实现。
    }

    public function open($save_path, $name, int $expire = 0)
    {
        // TODO: Implement open() method.
        $this->sessionName = $name;
        if ($this->mode) {
            if ($save_path) {
                $this->savePath = $save_path;
            } else {
                $this->savePath = rtrim(sys_get_temp_dir(), '/');
            }

            return is_dir($this->savePath);
        } else {
            if ($expire > 0) {
                $this->sessionExpire = $expire;
            }
            $this->savePath = $name;

            return true;
        }
    }

    public function read($session_id)
    {
        // TODO: Implement read() method.
        if ($this->mode) {
            $this->fp = new FileStream("{$this->savePath}/{$this->sessionName}_{$session_id}");
            $this->fp->lock();

            return $this->fp->__toString();
        } else {
            $this->fp = Cache::useRedis();

            return (string) $this->fp->get("{$this->savePath}:{$session_id}");
        }
    }

    public function write($session_id, $session_data)
    {
        // TODO: Implement write() method.
        if ($this->mode) {
            $this->fp->truncate(0);
            $this->fp->seek(0);

            return (bool) $this->fp->write($session_data);
        } else {
            if (empty(\unserialize($session_data))) {
                return (bool) $this->fp->del("{$this->savePath}:{$session_id}");
            } else {
                return (bool) $this->fp->setex("{$this->savePath}:{$session_id}", $this->sessionExpire, $session_data);
            }
        }
    }
}
