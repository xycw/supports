<?php

vendor('Request.Requests');

class aikuaidi {

    private $_key = 'fe84e8600e094b6aa803f98a2531adb0';
    private $_url = 'http://api.aikuaidi.cn/rest/';

    public function htmlQuery($no, $order = 'desc') {
        return $this->query($no, 'html', $order);
    }

    public function arrayQuery($no, $order = 'desc') {
        $r = $this->query($no, 'json', $order);
        if ($r !== false) {
            $data = json_decode($r, true);
            return $data;
        } else {
            return false;
        }
    }

    public function query($no, $type, $order = 'desc') {
        $express_no = $this->_parseNo($no);

        $data = array(
            'key' => $this->_key,
            'order' => $express_no[1],
            'id' => $express_no[0],
            'ord' => $order,
            'show' => $type
        );
        $url = $this->_url . '?';
        foreach ($data as $k => $v) {
            $url .= $k . '=' . $v . '&';
        }

        \Requests::register_autoloader();
        $response = \Requests::get($url);

        if ($response->success) {
            $result = $response->body;
        } else {
            $result = false;
        }

        return $result;
    }

    private function _parseNo($no) {
        $express_no = explode('-', $no, 2);
        return $express_no;
    }

    public function errCode($errCode) {
        $errTxt = '未知错误';
        switch ($errCode) {
            case 0:
                $errTxt = '无错误';
                break;
            case 1:
                $errTxt = '快递KEY无效';
                break;
            case 2:
                $errTxt = '快递代号无效';
                break;
            case 3:
                $errTxt = '访问次数达到最大额度';
                break;
            case 4:
                $errTxt = '查询服务器返回错误即返回状态码非200';
                break;
            case 5:
                $errTxt = '程序执行出错';
                break;
        }
        return $errTxt;
    }

    public function statusCode($statusCode) {
        $statusTxt = '未知状态';
        switch ($statusCode) {
            case 0:
                $statusTxt = '查询出错';
                break;
            case 1:
                $statusTxt = '暂无记录';
                break;
            case 2:
                $statusTxt = '在途中';
                break;
            case 3:
                $statusTxt = '派送中';
                break;
            case 4:
                $statusTxt = '已签收';
                break;
            case 5:
                $statusTxt = '拒收';
                break;
            case 6:
                $statusTxt = '疑难件';
                break;
            case 7:
                $statusTxt = '退回';
                break;
        }
        return $statusTxt;
    }

}
