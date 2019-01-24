<?php

namespace Ss\Http;

class Cookie
{
    public $key;
    public $value;
    public $expire;
    public $path;
    public $domain;
    public $secure;
    public $httponly;

    public function __construct(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        $this->key = $key;
        $this->value = $value;
        $this->expire = $expire;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httponly = $httponly;
    }
}
