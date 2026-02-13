<?php

namespace app\controller;

use support\Request;

class TestController
{
    public function index(Request $request)
    {
        return json(['code' => 200, 'message' => '测试成功', 'data' => ['timestamp' => time()]]);
    }
}
