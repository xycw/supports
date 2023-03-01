<?php
$a =file_get_contents('https://www.topfactorygears.com/api_v1_customers/get?Token=c3f52b3f144071548c3ffe17aca8326d17wQKaiS&Sign=098ec3f84e14e2a3efb85212d84d8220&lastDate=2019-09-27');
echo $a;
$b = json_decode($a);
$msg = $b->msg;
if($msg=='Success'){
	foreach($b->data as $v){
		$data .= '<br>customer_email: '.$v->email.'  '.$v->context."\r\n";
	}

	echo $data;	
}else{
	echo $msg.' '.$b->code;
}



?>


