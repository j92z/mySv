<?php

namespace Ss\Http;

use Ss\Common\Func;

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

    /**
     * perhaps has value:
     * -header
     * -server
     * -request
     * -cookie
     * -get
     * -files
     * -post
     * -tmpfiles.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getRequestInfo(string $key)
    {
        if (!is_string($key) && !$key) {
            return null;
        }

        return Func::getInfoByKey($this->request, $key);
    }

    public function raw()
    {
        return $this->request->rawContent();
    }

    public function cookie(string $key = '')
    {
        if (empty($key)) {
            return $this->request->cookie;
        }

        return $this->request->cookie[$key] ?? null;
    }
}
