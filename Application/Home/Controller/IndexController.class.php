<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function indexAction(){
        $this->show('<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} body{ background: #fff; font-family: "微软雅黑"; color: #333;font-size:24px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.8em; font-size: 36px } a,a:hover,{color:blue;}</style><div style="padding: 24px 48px;"> <h1>:)</h1><p>欢迎使用 <b>ThinkPHP</b>！</p><br/>版本 V{$Think.version}</div><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_55e75dfae343f5a1"></thinkad><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script>','utf-8');
    }
    
    public function testAction(){
        vendor('Request.Requests');
        \Requests::register_autoloader();

        $url = 'https://opencart.mrphper.cn/pay/notify';
        $string = '{"mc_gross":"42.49","auth_exp":"00:56:56 Aug 01, 2020 PDT","protection_eligibility":"Eligible","address_status":"confirmed","payer_id":"3FWWWDWPK5PP4","address_street":"303 Edgewood AvenueBelton","payment_date":"00:56:56 Jul 03, 2020 PDT","payment_status":"Pending","charset":"gb2312","address_zip":"29627","first_name":"John","transaction_entity":"auth","address_country_code":"US","address_name":"Heather Xie","notify_version":"3.9","custom":"","payer_status":"verified","business":"sb-8ve6b1522195@business.example.com","address_country":"United States","address_city":"Belton","quantity":"1","verify_sign":"AyENnMOt5AQyZyvNVDygKH1cCYFOA3VRYo2jIWov3lMtZFjdepOebq3t","payer_email":"sb-vl6jc1514551@personal.example.com","parent_txn_id":"","txn_id":"7YE42471270692203","payment_type":"instant","remaining_settle":"50","auth_id":"7YE42471270692203","last_name":"Doe","address_state":"SC","receiver_email":"sb-8ve6b1522195@business.example.com","auth_amount":"42.49","shipping_discount":"0.00","insurance_amount":"0.00","receiver_id":"HKVRGYXVMCT7S","pending_reason":"authorization","txn_type":"web_accept","item_name":"1593762833305561627","discount":"0.00","mc_currency":"USD","item_number":"166ba72094fa451f8d1416ba18d325a29T2Vdw9wLIBWKveJ4X","residence_country":"CN","test_ipn":"1","shipping_method":"Default","transaction_subject":"","payment_gross":"42.49","auth_status":"Pending","ipn_track_id":"12377df33596b"}';
        $post_data = json_decode($string, true);
        
        $response = \Requests::post($url, array(), $post_data, array('timeout'=>60, ''));
        echo $response->status_code.":".$response->body;
        exit;


    }
}