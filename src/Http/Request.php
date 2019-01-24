<?php

namespace Ss\Http;

class Request
{
    private $request;
    private $cookie_map;

    public function __construct(\swoole_http_request $request)
    {
        $this->request = $request;
        // $this->initCookie();
    }

    // private function initCookie()
    // {
    //     if (!is_null($this->request->cookie) && !empty($this->request->cookie)) {
    //         foreach ($this->request->cookie as $key => $value) {
    //             $this->cookie_map[$key] = new Cookie($key, $value);
    //         }
    //     }
    // }

    public function request()
    {
        return $this->request;
    }

    public function cookie(string $key = '')
    {
        if (empty($key)) {
            return $this->request->cookie;
        }
        return $this->request->cookie[$key] ?? null;
    }
}
