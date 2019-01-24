<?php

namespace App\Controller;

use Ss\MVC\Model;
use Ss\MVC\Cache;

class Index extends \Ss\MVC\Controller
{
    public function index()
    {
        $this->response()->write('index page'.json_encode($this->request()->get));
    }

    public function ping()
    {
        return 'ping page';
    }

    public function view()
    {
        $cookie = $this->cookie('name');
        $this->assign('cookie', $cookie);
        $this->assign('name', 'jack');
        $this->assign('age', 21);
        $this->fetch('ok/test.html');
        $str = random_int(1, 9);
        $this->cookie('name', $cookie.$str, time() + 888);
        // $this->render('ok/test', ['name' => 'jack', 'age' => 21]);
    }

    public function usemysql()
    {
        $data = Model::usemysql()->select('article_detail', ['id', 'title', 'article', 'view_count']);
        // $data = $this->mysql()->select('article_detail', ['id', 'title', 'article', 'view_count']);
        $this->json($data);
    }

    public function usemssql()
    {
        // $data = Model::useMssql()->select('WorkTask', '*');
        $data = $this->mssql()->select('WorkTask', '*');
        $this->json($data);
    }

    public function useredis()
    {
        $data['name'] = Cache::useRedis()->get('name');
        $data['sex'] = Cache::useRedis()->get('sex');
        // $data['name'] = $this->redis()->get('name');
        // $data['sex'] = $this->redis()->get('sex');
        $this->json($data);
    }

    public function testcookie()
    {
        $this->response()->cookie('name', 'sasdfasdadf', time() * 2);
    }

    public function testsession()
    {
        $session = $this->session('ok');
        $this->cookie('sex', 'nv', time() + 588);
        $str = random_int(1, 9);
        $this->session('ok', $str.$session);
    }
}
