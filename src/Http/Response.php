<?php

namespace Ss\Http;

use Ss\Means\Stream;

class Response
{
    private $response;
    private $status = 200;
    private $header;
    private $body;
    private $cookie;
    private $domain;

    const NOT_END = 0; //结束响应
    const IS_END = 1; //未结束响应

    private $endLogicNumber = self::NOT_END;

    public function __construct(\swoole_http_response $response)
    {
        $this->response = $response;
    }

    public function setDomain(string $domain = ''): void
    {
        $this->domain = $domain;
    }

    public function status($code = 200)
    {
        if (!$this->endLogicNumber) {
            $this->status = $code;
        }
    }

    private function body()
    {
        if (!isset($this->body)) {
            $this->body = new Stream();
        }

        return $this->body;
    }

    public function header(string $key, $value = '')
    {
        if (!$this->endLogicNumber) {
            if (is_null($value)) {
                unset($this->header[$key]);
            } else {
                $this->header[$key] = $value;
            }
        }
    }

    public function write($content = '')
    {
        if (!$this->endLogicNumber) {
            return  $this->body()->write($content);
        } else {
            return false;
        }
    }

    public function cookie(string $key = '', $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        if (!$this->endLogicNumber) {
            $this->cookie[$key] = new Cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
        } else {
            return false;
        }
    }

    public function end()
    {
        if (!$this->endLogicNumber) {
            $this->endLogicNumber = self::IS_END;
            $this->response->end();
        }
    }

    public function __res()
    {
        if (!$this->endLogicNumber) {
            //结束处理
            $this->response->status($this->status);
            if (!empty($this->header)) {
                foreach ($this->header as $header => $val) {
                    if (is_array($val)) {
                        $header_string = '';
                        foreach ($val as $sub) {
                            if (!empty($sub)) {
                                $header_string = $sub.';';
                            }
                        }
                        $this->response->header($header, rtrim($sub, ';'));
                    } else {
                        $this->response->header($header, $val);
                    }
                }
            }
            if (!empty($this->cookie)) {
                foreach ($this->cookie as $cookie) {
                    $this->response->cookie($cookie->key, $cookie->value, $cookie->expire, $cookie->path, $cookie->domain, $cookie->secure, $cookie->httponly);
                }
            }

            $write = $this->body()->__toString();
            if ($write !== '') {
                $this->response->write($write);
            }
            $this->end();
        }
    }
}
