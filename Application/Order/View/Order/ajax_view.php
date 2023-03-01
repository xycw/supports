<tr class="order-bd-<?php echo $order_info['site_id'] . '-' . $order_info['orders_id'] ?>">
    <td colspan="9">
        <table class="table table-bordered">
            <tr>
                <td colspan="4">
                    客户IP：<?php $ip = trim(substr($order_info['ip_address'], 0, strpos($order_info['ip_address'], '-')));echo $ip;?>
                    <?php
                    if(empty($order_info['ip_info'])){
                    ?>
                    <button type="button" class="btn btn-default btn-xs" id="btn_ip_query_<?php echo $order_info['site_id'] . '-' . $order_info['orders_id'] ?>">查询IP信息</button>
                    <span id="ip-info<?php echo $order_info['site_id'] . '-' . $order_info['orders_id'] ?>"></span>
                    <script>
                        $('#btn_ip_query_<?php echo $order_info['site_id'] . '-' . $order_info['orders_id'] ?>').click(function(){

                                $.getJSON("<?php echo U('Order/Order/ipQuery')?>", {'ip':'<?php echo $ip ?>'}, function(addrInfo){
                                    if(addrInfo.ipAddress){
                                        var ip_info = '国家:'+addrInfo.countryName+',省/州'+addrInfo.stateProv + ",城市:" + addrInfo.city + ",邮编:" + addrInfo.zipCode;
                                        $('#ip-info<?php echo $order_info['site_id'] . '-' . $order_info['orders_id'] ?>').text(ip_info);
                                    }else{
                                        alert('查询失败');
                                    }
                                });
           
                        });
                    </script>
                    <?php
                    }else{
                        $ip_info = json_decode($order_info['ip_info'], true);
                        echo '国家:'.$ip_info['countryName'].',省/州'.$ip_info['stateProv'].',城市:'.$ip_info['city'].',邮编:'.$ip_info['zipCode'];
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="4">
<div class="row">
	<div class="col-lg-4">
		<div class="panel panel-default">
		  <div class="panel-heading">客户信息</div>
		  <div class="panel-body">
		    <div class="row">
		    	<div class="col-xs-3">姓名:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_name']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">公司:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_company']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">街道:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_street_address']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城郊:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_suburb']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城市:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_city']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">邮编:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_postcode']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">省/州:</div>
		    	<div class="col-lg-9"><?php echo $order_info['customers_state']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">国家:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_country']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">电话:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_telephone']?></div>
		    </div>		    		    		    	    		    		    		        
		  </div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="panel panel-default">
		  <div class="panel-heading">收货信息</div>
		  <div class="panel-body">
		    <div class="row">
		    	<div class="col-xs-3">姓名:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_name']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">公司:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_company']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">街道:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_street_address']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城郊:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_suburb']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城市:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_city']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">邮编:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_postcode']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">省/州:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_state']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">国家:</div>
		    	<div class="col-xs-9"><?php echo $order_info['delivery_country']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">电话:</div>
		    	<div class="col-xs-9"><?php echo $order_info['customers_telephone']?></div>
		    </div>
		  </div>
		</div>	
	</div>
	<div class="col-lg-4">
		<div class="panel panel-default">
		  <div class="panel-heading">账单信息</div>
		  <div class="panel-body">
		    <div class="row">
		    	<div class="col-xs-3">姓名:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_name']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">公司:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_company']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">街道:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_street_address']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城郊:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_suburb']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">城市:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_city']?></div>
		    </div>	
		    <div class="row">
		    	<div class="col-xs-3">邮编:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_postcode']?></div>
		    </div>		
		    <div class="row">
		    	<div class="col-xs-3">省/州:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_state']?></div>
		    </div>
		    <div class="row">
		    	<div class="col-xs-3">国家:</div>
		    	<div class="col-lg-9"><?php echo $order_info['billing_country']?></div>
		    </div>
		  </div>
		</div>	
	</div>
</div>
                </td>
            </tr>
<?php
$n = sizeof($order_info['product']);
for($i=0;$i<$n;$i++){
    $product = $order_info['product'][$i];
    if($i%2==0) echo '<tr>';
?>
    <td class="img">
        <img class="pull-left" src="<?php echo $product['products_image'] ?>">
    </td>   
    <td class="baobei" <?php if($product['add_from_sys']==1) echo 'style="background:yellow;"' ?>>
        <?php
        echo '<a class="f09" '.($product['orders_products_remark']['remove']?'style="background: red;color: white;text-decoration: line-through;"':'').' href="' . getProductLink($order_info['site_index'], $product['products_id']) . '" target="_blank">' . $product['products_name'] . '</a><br>';
        echo '<ul>';
        echo '<li><b>Model:</b>' . $product['products_model'].'</li>';
        if (!empty($product['attribute'])) {
            foreach ($product['attribute'] as $attribute) {
                echo '<li><b>' . $attribute['products_options'] . ':</b>' . $attribute['products_options_values'] . '</li>';
            }
            
        }
        echo '</ul>';
        ?>
        <span class="pull-right">×<span style="font-weight: bold;font-size: 1.2em;"><?php echo $product['products_quantity'] ?></span></span>
    </td>    
<?php            
    if($i%2==1) echo '</tr>';
}
if($i%2==1){
    echo '<td colspan="2">&nbsp;</td></tr>';
}
?>
        </table>
    </td>
</tr>