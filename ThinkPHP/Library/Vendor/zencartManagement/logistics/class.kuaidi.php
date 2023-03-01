<?php

vendor('Request.Requests');

class kuaidi {

    private $_key = '8159e923140d8dbe85e721a6efcb2aa6';
    private $_url = 'http://highapi.kuaidi.com/openapi-querycountordernumber.html';
    
    private $_no;
    public  $data;//物流记录信息
    public  $status;//当前状态
    public  $is_received;//是否签收
    
    public function kuaidi($no=''){
        $this->_no = $no;
        $this->query($no);
    }
    
    //返回数组
    /*
     * array('data'=>array(array('物流信息')),'status'=>'当前状态')
     */
    public function query($no, $order = 'desc') {
        $express_no = $this->_parseNo($no);

        $data = array(
            'key' => $this->_key,
            'com' => $express_no[1],
            'nu' => $express_no[0],
            'order' => $order,
            'show' => 0,//返回json
            'muti' => 0,//返回多行完整的信息
        );
        $url = $this->_url . '?';
        foreach ($data as $k => $v) {
            $url .= $k . '=' . $v . '&';
        }

        \Requests::register_autoloader();
        $response = \Requests::get($url);

        if ($response->success) {
            $result = $response->body;
            $data = json_decode($result, true);
            $this->data = $data['data'];
            $this->status = $this->statusCode($data['status']);
            $this->is_received = ($data['status']==6);
        } else {
            $this->data = array();
            $this->status = false;
            $this->is_received = 0;
        }
    }

    private function _parseNo($no) {
        $express_no = explode('-', $no, 2);
        return $express_no;
    }
    
    
    public function statusCode($statusCode) {
        $statusTxt = '未知状态(Status NO.'.$statusCode.')';      
        switch ($errCode) {
            case 0:
                $statusTxt = '物流单号暂无结果';
                break;
            case 3:
                $statusTxt = '在途，快递处于运输过程中';
                break;
            case 4:
                $statusTxt = '揽件，快递已被快递公司揽收并产生了第一条信息';
                break;
            case 5:
                $statusTxt = '疑难，快递邮寄过程中出现问题';
                break;
            case 6:
                $statusTxt = '签收，收件人已签收';
                break;
            case 7:
                $statusTxt = '退签，快递因用户拒签、超区等原因退回，而且发件人已经签收';
                break;
            case 8:
                $statusTxt = '派件，快递员正在同城派件';
                break;
            case 9:
                $statusTxt = '退回，货物处于退回发件人途中';
                break;            
        }
        return $statusTxt;
    }
}
