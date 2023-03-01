<?php 
if($action=='edit'){
?>
<form action="<?php echo U('Order/Order/edit')?>" enctype="multipart/form-data" method="post">
<input type="hidden" name="site_id" value="<?php echo $order_info['site_id']?>">
<input type="hidden" name="order_id" value="<?php echo $order_info['orders_id']?>">
<input type="hidden" name="orders_remark_id" value="<?php echo $order_info['orders_remark_id']?>">
<input type="hidden" name="order_no" value="<?php echo $order_info['order_no']?>">
<?php
}
?>
<h1>订单详情(<?php echo $order_info['site_name'].'-'.$order_info['customers_email_address']?>)</h1>
<div class="panel panel-default">
  <div class="panel-heading">
    <div class="row">
                <div class="col-lg-1 right">订单号</div>
                <div class="col-lg-1"><?php echo (empty($order_info['order_no'])?$order_info['order_no_prefix'].$order_info['orders_id']:$order_info['order_no'])?></div>    
        <div class="col-lg-1 right">下单(BJ)时间</div>
                <div class="col-lg-1" data-toggle="tooltip" data-placement="top" title="<?php echo $order_info['date_purchased']?>"><?php echo date('Y-m-d', strtotime($order_info['date_purchased']))?></div>  
        <div class="col-lg-1 right"><span class="f08">客户需要时间:</span></div>  
        <div class="col-lg-1"><?php if($action=='view') echo $order_info['date_require']; elseif ($action=='edit') echo '<input class="form-control" type="text" name="date_require" value="'.$order_info['date_require'].'">'?></div>
        <div class="col-lg-1 right"><span class="f08">是否急单</span></div>
        <div class="col-lg-2">
                    <?php 
                    if($action=='view') 
                        echo $order_info['is_rush_order']=='1'?'<span style="background:red;color:#fff;font-weight:bold;">急单</span>':'非急单'; 
                    elseif ($action=='edit') {
                    ?>
                    <tagLib name="html" />
                    <html:select options="options_rush_order" name="is_rush_order" selected="is_rush_order" style="form-control" />
                    <?php
                    }
                    ?>
                </div>
        <div class="col-lg-1 right"><span class="f08">备注状态</span></div>
        <div class="col-lg-2">
                <?php 
                if($action=='view') {
                    $tip = '';
                    $num_email = 0;
                    if(sizeof($email_history)>0){
                        foreach ($email_history as $entry){
                            $tip .= '<div style=\'text-align:left;\'>'.$entry['time'].$entry['email_template_name']."</div>";
                            $num_email++;
                        }
                    }else{
                        $tip = '无邮件发送记录';
                    }
                    
                    echo $order_info['order_status_remark']==''?'未知状态':'<a id="order_status_remark" data-toggle="modal" data-target="#email-dialog" href="'.U('Order/Order/email/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id']).'">'.$order_info['order_status_remark'].'</a><span class="badge" data-toggle="tooltip" data-placement="bottom" data-html="true" title="'.$tip.'">'.$num_email.'</span>'; 
                }elseif ($action=='edit') {
                ?>
                <tagLib name="html" />
                <html:select options="order_status_remark" name="order_status_remark" selected="order_status_remark_checked" style="form-control" first="--未知状态--" />
                <?php    
                }
                ?>
                </div>      
    </div>
  </div>    
  <div class="panel-body">
    <?php if($action == 'edit') {?>
    <div class="form-horizontal">
        <div class="form-group">
            <label class="col-sm-2 control-label">取消全部产品：</label>
            <div class="col-sm-2">
                <select class="form-control" name="all_products_remove">
                    <option value="1" <?php if($order_info['all_products_remove']) echo ' selected'; ?>>是</option>
                    <option value="0" <?php if(!$order_info['all_products_remove']) echo ' selected'; ?>>否</option>
                </select>
            </div>
        </div>
    </div>
    <?php }?>
    <table class="table table-border">
<?php
$n = sizeof($order_info['product']);
for($i=0;$i<$n;$i++){
    $product = $order_info['product'][$i];
    if($i%2==0) echo '<tr>';
?>
    <td class="img" <?php if($product['add_from_sys']==1) echo 'style="background:yellow;"'  ?>>
        <img class="pull-left" src="<?php echo trim($product['products_image']); ?>">
        <?php
        if($action=='view') {
            if($product['orders_products_remark']['remove']==1) 
                echo '<p style="padding:5px;color:#fff;background:red;">取消此项目</p>';
        }else{
        ?>
        <select class="form-control" name="orders_products_remark[remove][<?php echo empty($product['orders_products_remark']['orders_products_remark_id'])?$product['site_id'].'-'.$product['orders_products_id']:$product['orders_products_remark']['orders_products_remark_id'] ?>]">
            <option value="0" <?php if($product['orders_products_remark']['remove']==0) echo ' selected'; ?>>正常</option>
            <option value="1" <?php if($product['orders_products_remark']['remove']==1) echo ' selected'; ?>>取消此项目</option>
        </select>
        <?php
        }
        ?>
    </td>   
    <td class="baobei" style="position:relative;<?php if($product['add_from_sys']==1) echo 'background:yellow;'  ?>">
        <?php
        echo '<a class="f09" href="' . getProductLink($order_info['site_index'], $product['products_id']) . '" target="_blank">' . $product['products_name'] . '</a><br>';
        echo '<ul>';
        echo '<li><b>Model:</b>' . $product['products_model'].'</li>';
        if (!empty($product['attribute'])) {
            foreach ($product['attribute'] as $attribute) {
                echo '<li><b>' . $attribute['products_options'] . ':</b>' . $attribute['products_options_values'] . '</li>';
            }
            
        }
        echo '</ul>';
        ?>
        <span class="pull-right" style="position:absolute;top:5px;right:5px;">×<span style="font-weight: bold;font-size: 2.5em;"><?php echo $product['products_quantity'] ?></span></span>
        <label>备注:</label>
        <?php
        if($action=='view') {
            echo $product['orders_products_remark']['remark'];
        }else{
        ?>        
        <textarea class="form-control" name="orders_products_remark[remark][<?php echo empty($product['orders_products_remark']['orders_products_remark_id'])?$product['site_id'].'-'.$product['orders_products_id']:$product['orders_products_remark']['orders_products_remark_id'] ?>]"><?php echo $product['orders_products_remark']['remark'] ?></textarea>
        <?php
        }
        ?>
        <?php if($action == 'edit'){?>
        <p>
            <span>供应商：<?php echo !isset($product['orders_products_remark']['supplier_name']) || empty($product['orders_products_remark']['supplier_name']) ? '<span style="background:yellow;">待确定供应商</span>' : $product['orders_products_remark']['supplier_name'];?></span>
            <span style="margin-left: 5px;">订货单价：<?php echo isset($product['orders_products_remark']['purchase_price']) ? number_format($product['orders_products_remark']['purchase_price'], 2) : '';?></span>
            <br>
            <span>订货日期：<?php echo isset($product['orders_products_remark']['date_process']) ? $product['orders_products_remark']['date_process'] : '';?></span>
            <span style="margin-left: 5px;">订货状态：<?php echo isset($product['orders_products_remark']['item_status']) ? $product['orders_products_remark']['item_status'] : '';?></span>
        </p>
        <?php }?>
    </td>    
<?php            
    if($i%2==1) echo '</tr>';
}
if($i%2==1){
    echo '<td colspan="2">&nbsp;</td></tr>';
}
?>
      <?php
      if($action=='edit') {
?>
<tr>
    <td colspan="4" style="font-weight:bold;text-align: center;font-size:1.5em;background:#ddd;">新增项目</td>
</tr>
<tr>
    <td colspan="2">
        <table class="table table-borderd">
            <tr>
            <th>产号</th>
            <td>
                <table class="table table-borderd">
                    <tr>
                        <td>Sku:</td><td><input class="form-control" type="input" name="new_product[model][0]"></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center;">--或者(如果新加的产品不是在产品库中,请直接填写产品名称,上传产品图片)--</td>
                    </tr>
                    <tr>
                        <td>产品名称:</td><td><input class="form-control" type="input" name="new_product[product_name][0]"></td>
                    </tr>
                    <tr>
                        <td>产品图片:</td><td><input class="form-control" type="input" name="new_product[product_image][0]" readonly><button type="button" class="btn btn-default btn-block btn-product-image">图片选择</button></td>
                    </tr>                    
                </table>
                
            </td>
            </tr>
            <tr>
            <th>数量</th>
            <td><input class="form-control" type="input" name="new_product[qty][0]"></td>
            </tr>
            <tr>
            <th>属性</th>
            <td>
                <table>
                    <tr>
                        <th>属性名</th>
                        <th>属性值</th>
                    </tr>
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_value][]"></th>
                    </tr>  
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_value][]"></th>
                    </tr>  
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_value][]"></th>
                    </tr>   
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][0][option_value][]"></th>
                    </tr>            
                    <tr><th colspan="2"><button class="btn btn-default btn-block btn-add-attr" type="button">+</button></th></tr>
                </table>
            </td>
            </tr>            
        </table>
    </td>
    <td colspan="2">
        <table class="table table-borderd">
            <tr>
            <th>型号</th>
            <td>
                <table class="table table-borderd">
                    <tr>
                        <td>Sku:</td><td><input class="form-control" type="input" name="new_product[model][1]"></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:center;">--或者(如果新加的产品不是在产品库中,请直接填写产品名称,上传产品图片)--</td>
                    </tr>
                    <tr>
                        <td>产品名称:</td><td><input class="form-control" type="input" name="new_product[product_name][1]"></td>
                    </tr>
                    <tr>
                        <td>产品图片:</td><td><input class="form-control" type="input" name="new_product[product_image][1]" readonly><button type="button" class="btn btn-default btn-block btn-product-image">图片选择</button></td>
                    </tr>                    
                </table>           
            </td>
            </tr>
            <tr>
            <th>数量</th>
            <td><input class="form-control" type="input" name="new_product[qty][1]"></td>
            </tr>
            <tr>
            <th>属性</th>
            <td>
                <table>
                    <tr>
                        <th>属性名</th>
                        <th>属性值</th>
                    </tr>
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_value][]"></th>
                    </tr>  
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_value][]"></th>
                    </tr>  
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_value][]"></th>
                    </tr>  
                    <tr>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_name][]"></th>
                        <th><input class="form-control" type="input" name="new_product[attr][1][option_value][]"></th>
                    </tr>   
                    <tr><th colspan="2"><button class="btn btn-default btn-block btn-add-attr" type="button">+</button></th></tr>
                </table>
            </td>
            </tr>            
        </table>
    </td>
</tr>
<?php
      }
      ?>
    </table>
    <h2>订单历史状态(<?php echo $order_info['orders_status_name']?>)</h2>
    <?php 
    if (!empty($order_info['history'])) {
            echo '<table class="table table-striped">';
            echo '<tr>';
            echo '<th width="10%">Status</th>';
            echo '<th width="70%">Commons</th>';
            echo '<th width="5%">Notify</th>';
            echo '<th width="15%">Time</th>';
            echo '</tr>';
        foreach ($order_info['history'] as $h_entry){
    ?>
        <tr>
            <td><?php echo $h_entry['orders_status_name']?></td>
            <td><?php echo $h_entry['comments']?></td>
            <td><?php echo $h_entry['customer_notified']=='1'?'<span class="glyphicon glyphicon-ok">':'<span class="glyphicon glyphicon-remove">'?></span></td>
            <td><?php echo $h_entry['date_added']?></td>
        </tr>
    <?php       
        }  
        echo '</table>';
    }
    ?>
    <a class="pull-right btn btn-default btn-xs" id="btn-update-order-history" href="javascript:void(0);">编辑历史状态</a>
        <h2>发货物流情况</h2>
        <table class="table table-striped">
            <tr>
                <?php
                if($action=='edit'){
                ?>
                <th><span class="glyphicon glyphicon-trash"></span></th>
                <?php
                }
                ?>
                <th width="90px">订单产品数<br>(不含赠品)</th>
                <th width="90px">赠品数量</th>
                <th width="90px">重量(Kg)</th>
                <th width="100px">货运方式</th>
                <th>转运单号</th>
                <th>货运单号</th>
                <th>其它备注</th>
                <th>物流状态</th>
                <th width="120px">发货日期</th>
                <th>添加时间</th>
            </tr>
            <?php
            if($action=='edit'){
                foreach ($order_info['delivery'] as $entry){
                    $delivery_type_selected = $entry['delivery_type'];
                    $delivery_status_selected = $entry['delivery_status'];
            ?>
            <tr>
                <td><input type="checkbox" name="del_delivery[]" value="<?php echo $entry['orders_delivery_id']?>"></td>
                <td><input class="form-control" type="hidden" name="delivery_id[]" value="<?php echo $entry['orders_delivery_id']?>">
                    <input class="form-control" type="text" name="delivery_quanlity[]" value="<?php echo $entry['delivery_quanlity']?>"></td>
                <td><input class="form-control" type="text" name="delivery_gift_quanlity[]" value="<?php echo $entry['delivery_gift_quanlity']?>"></td>
                <td><input class="form-control" type="text" name="delivery_weight[]" value="<?php echo $entry['delivery_weight']?>"></td>
                <td><html:select options="data_delivery_type" name="delivery_type[]" style="form-control" selected="delivery_type_selected"/></td>
                <td><input class="form-control" type="text" name="delivery_forward_no[]" value="<?php echo $entry['delivery_forward_no']?>"></td>                
                <td><input class="form-control" type="text" name="delivery_tracking_no[]" value="<?php echo $entry['delivery_tracking_no']?>"></td>
                <td><input class="form-control" type="text" name="delivery_remark[]" value="<?php echo $entry['delivery_remark']?>"></td>
                <td><html:select options="data_shipping_status" name="delivery_status[]" selected="delivery_status_selected" style="form-control" first="--未知状态--" /></td>
                <td><input class="form-control" type="text" name="delivery_date[]" value="<?php echo $entry['delivery_date']?>"></td>
                <td><?php echo $entry['add_time'] ?></td>
            </tr>
            <?php  
                }
            ?>
            <tr>
                <td></td>
                <td>
                    <input class="form-control" type="hidden" name="delivery_id[]" value="">
                    <input class="form-control" type="text" name="delivery_quanlity[]">
                </td>
                <td><input class="form-control" type="text" name="delivery_gift_quanlity[]"></td>
                <td><input class="form-control" type="text" name="delivery_weight[]"></td>
                <td><html:select options="data_delivery_type" name="delivery_type[]" style="form-control" /></td>
                <td><input class="form-control" type="text" name="delivery_forward_no[]"></td>                
                <td><input class="form-control" type="text" name="delivery_tracking_no[]"></td>
                <td><input class="form-control" type="text" name="delivery_remark[]"></td>
                <td>
                    <html:select options="data_shipping_status" name="delivery_status[]" style="form-control" first="--未知状态--" />
                </td>
                <td><input class="form-control" type="text" name="delivery_date[]" value=""></td>
                <td></td>
 
            </tr>
            <tr><td colspan="11"><button class="btn btn-default" type="button" id="btn_add_delivery">新增行</button></td></tr>
            <?php
            }else{
                foreach ($order_info['delivery'] as $entry){
            ?>
            <tr>
                <td><?php echo $entry['delivery_quanlity'] ?></td>
                <td><?php echo $entry['delivery_gift_quanlity'] ?></td>
                <td><?php echo $entry['delivery_weight'] ?></td>
                <td><?php echo $entry['delivery_type'] ?></td>
                <td>
                    <a href=".." target="_blank"><?php echo $entry['delivery_forward_no'] ?></a>
                    <?php if(!empty($entry['delivery_forward_no'])){ ?>

                        >>> <a href="https://t.17track.net/en#nums=<?php echo $entry['delivery_forward_no'] ;?>" target="_blank" title="">17track</a>
                    <?php
                            $entry['delivery_tracking_no']='<del>'.$entry['delivery_tracking_no'].'</del>'; //原货运单号 添加中划线显示
                            $aikuaidi_tracking_no_url='#'; //货运单号aikuaidi查询链接 为#
                        }else{
                            $aikuaidi_tracking_no_url=U('Order/ExpressDelivery/logistics',array('orders_delivery_id'=>$entry['orders_delivery_id'])); //货运单号aikuaidi查询链接
                        }
                    ?>

                </td>
                <td>
                    <a href="<?php echo $aikuaidi_tracking_no_url;?>" target="_blank"><?php echo $entry['delivery_tracking_no'] ?></a>   

                    <?php 
                        if(empty($entry['delivery_forward_no'])){ ?>
                            >>> <a href="https://t.17track.net/en#nums=<?php echo $entry['delivery_tracking_no'] ;?>" target="_blank" title="">17track</a>
                    <?php 
                        } 
                    ?>
                    
                </td>
                <td><?php echo $entry['delivery_remark'] ?></td>
                <td><?php echo $entry['delivery_status'] ?></td>
                <td><?php echo $entry['delivery_date']?></td>
                <td><?php echo $entry['add_time'] ?></td>
            </tr>
            <?php        
                }
            }
            ?>
        </table>    
  </div>
  <div class="panel-footer form-horizontal">  
    <div class="form-group">
        <label class="col-lg-2">本地备注:</label>
        <div class="col-lg-10">
        <?php 
        if($action=='view'){
                echo nl2br($order_info['order_remark']);
            }elseif ($action=='edit'){
                echo '<textarea class="form-control" name="order_remark" rows="5">'.($order_info['order_remark']).'</textarea>';    
            }
        ?>  
        </div>
    </div> 
    <div class="form-group">
        <label class="col-lg-2">附件:</label>
        <div class="col-lg-10">
            <?php 
            foreach ($attachemnt as $e){
                echo '<div><a'.($action=='edit'?' class="del-image"':'').' href="'.$e['link'].'" target="_blank">'.$e['text'].'</a></div>';
            }
            ?>
            <?php 
            if ($action=='edit') {
            ?>
            <div class="row">
                <div class="col-lg-6 input-file-wrapper"><input type="file" name="attachment[]"></div>
                <div class="col-lg-3"><button type="button" id="add-attachment">添加附件</button></div>
            </div>      
            <?php
            }
            ?>
        </div>
    </div>  
    <div class="form-group">
        <label class="col-lg-2">客户反馈:</label>
        <div class="col-lg-10">
        <?php 
        if($action=='view'){
                echo $order_info['customer_feedback'];
            }elseif ($action=='edit'){
            ?>
            <html:radio radios="customer_feedback" name="customer_feedback" checked="customer_feedback_checked"/>
            <?php
            }
        ?>  
        </div>
    </div>  
  </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="panel panel-default">
          <div class="panel-heading">客户信息</div>
          <div class="panel-body">
            <div class="row">
                <div class="col-lg-2">Customer:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_name']?></div>
            </div>
            <div class="row">
                <div class="col-lg-2">Company:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_company']?></div>
            </div>      
            <div class="row">
                <div class="col-lg-2">Street:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_street_address']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-2">Address:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_suburb']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-2">City:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_city']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-2">ZIP:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_postcode']?></div>
            </div>      
            <div class="row">
                <div class="col-lg-2">State:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_state']?></div>
            </div>
            <div class="row">
                <div class="col-lg-2">Country:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_country']?></div>
            </div>
            <div class="row">
                <div class="col-lg-2">Telephone:</div>
                <div class="col-lg-10"><?php echo $order_info['customers_telephone']?></div>
            </div>                                                                                  
          </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel panel-default" id="delivery-panel">
          <div class="panel-heading">发货地址<?php if($action=='edit'){ ?><button class="btn btn-primary btn-xs" type="button" id="btn-modify-address">编辑</button><?php } ?></div>
          <div class="panel-body">
            <div class="row">
                <div class="col-lg-3">收货人:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_name" value="<?php echo $order_info['delivery_name']?>" placeholder="收货人" readonly /></div>
            </div>
            <div class="row">
                <div class="col-lg-3">公司:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_company" value="<?php echo $order_info['delivery_company']?>" placeholder="公司" readonly /></div>
            </div>      
            <div class="row">
                <div class="col-lg-3">街道1:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_street_address" value="<?php echo $order_info['delivery_street_address']?>" placeholder="街道1" readonly /></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">街道2:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_suburb" value="<?php echo $order_info['delivery_suburb']?>" placeholder="街道2" readonly /></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">城市:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_city" value="<?php echo $order_info['delivery_city']?>" placeholder="城市" readonly /></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">邮编:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_postcode" value="<?php echo $order_info['delivery_postcode']?>" placeholder="邮编" readonly /></div>
            </div>      
            <div class="row">
                <div class="col-lg-3">省(州):</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_state" value="<?php echo $order_info['delivery_state']?>" placeholder="省(州)" readonly /></div>
            </div>
            <div class="row">
                <div class="col-lg-3">国家:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="delivery_country" value="<?php echo $order_info['delivery_country']?>" placeholder="国家" readonly /></div>
            </div>
            <div class="row">
                <div class="col-lg-3">电话:</div>
                <div class="col-lg-9"><input class="form-control" type="text" name="customers_telephone" value="<?php echo $order_info['customers_telephone']?>" placeholder="电话" readonly /></div>
            </div>
          </div>
        </div>  
    </div>
    <div class="col-lg-4">
        <div class="panel panel-default">
          <div class="panel-heading">账单信息</div>
          <div class="panel-body">
            <div class="row">
                <div class="col-lg-3">Customer:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_name']?></div>
            </div>
            <div class="row">
                <div class="col-lg-3">Company:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_company']?></div>
            </div>      
            <div class="row">
                <div class="col-lg-3">Street:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_street_address']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">Address:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_suburb']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">City:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_city']?></div>
            </div>  
            <div class="row">
                <div class="col-lg-3">ZIP:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_postcode']?></div>
            </div>      
            <div class="row">
                <div class="col-lg-3">State:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_state']?></div>
            </div>
            <div class="row">
                <div class="col-lg-3">Country:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_country']?></div>
            </div>
            <div class="row">
                <div class="col-lg-3">Telephone:</div>
                <div class="col-lg-9"><?php echo $order_info['billing_telephone']?></div>
            </div>
            <?php 
            if('Credit Card Payment'==$order_info['payment_method'] && !empty($order_info['cc_number'])){
            ?>
            <div class="row">
                <div class="col-lg-3">卡号:</div>
                <div class="col-lg-9"><?php echo $order_info['cc_number']?></div>
            </div>
            <div class="row">
                <div class="col-lg-3">有效期:</div>
                <div class="col-lg-9"><?php echo $order_info['cc_expires']?></div>
            </div>
            <div class="row">
                <div class="col-lg-3">签名:</div>
                <div class="col-lg-9"><?php echo $order_info['cc_cvv']?></div>
            </div>
            <?php   
            }
            ?>
          </div>
        </div>  
    </div>
</div>
<div class="center">
<?php 
if($action=='view'){
?>
<a class="pull-right" id="btn-update-order" href="javascript:void(0);">更新订单</a>
<a class="pull-right mr5" href="<?php echo U('Order/Order/order_doc/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>">导出word格式订单表格</a>
<a class="btn btn-default" href="<?php echo U('Order/Order/edit',array('site_id'=>$order_info['site_id'],'order_id'=>$order_info['orders_id']))?>">编辑订单</a>
<?php 
}elseif ($action=='edit'){
?>
<a class="btn btn-default" href="<?php echo U('Order/Order/view',array('site_id'=>$order_info['site_id'],'order_id'=>$order_info['orders_id']))?>">返回</a>
<button class="btn btn-default" type="submit">保存</button>
<?php
}
?>
</div>

<?php 
if($action=='edit'){
?>
</form>
<?php
}
?>
<div class="modal fade" id="history-status-dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" action="<?php echo U('Order/Order/orderHistory/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>" method="post">
        <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">订单历史状态</h4>
        </div>
        <div class="modal-body">
                <div class="form-group">
                        <label class="col-sm-2">状态</label>
                        <div class="col-sm-10" id="order-status"></div>
                </div>
                <div class="form-group">
                        <label class="col-sm-2">备注</label>
                        <div class="col-sm-10"><textarea class="form-control" name="comments"></textarea></div>
                </div>  
        </div>
        <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary">保存</button>
        </div>
        </form>        
    </div>
  </div>
</div>

<div class="modal fade" id="email-dialog">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content" style="padding:10px;">
    </div>
  </div>
</div>
<script type="text/javascript" src="__PUBLIC__/Js/ckfinder/ckfinder.js"></script>
<script>
$(document).ready(function(){
    $.ajaxSetup({
        beforeSend: function(){
            layer.load(1);
        },
        complete  : function(){
            layer.closeAll('loading');
        }
    });
    $('.btn-add-attr').click(function(){
       var new_tr = $(this).parent().parent().prev().clone(); 
       $(this).parent().parent().before(new_tr);
    });
    $("input[name='date_require']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
    $("input[name='date_expected_supplier_send']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
    $("input[name='delivery_date[]']").datetimepicker({format: 'yyyy-mm-dd','autoclose': true,'startView':2,'minView':'month','language':'zh-CN'});
    $('[data-toggle="tooltip"]').tooltip();
    $("a.fancybox").fancybox({
            'transitionIn'  :   'elastic',
            'transitionOut' :   'elastic',
            'speedIn'       :   600, 
            'speedOut'      :   200, 
            'overlayShow'   :   false
    });
    $(".add-image").click(function(){
            var rel = $(this).attr("rel");
            $('<input type="file" name="product_image_'+rel+'[]">').appendTo(".baobei-name"+rel);
    });
    $("#add-attachment").click(function(){
            $('<input type="file" name="attachment[]">').appendTo(".input-file-wrapper");
    });
    $("a.del-image").click(function(){
            var href = $(this).attr("href");
            var _this = this;
            if(window.confirm("你确定要删除这个文件?")){
                    $.post("<?php echo U('Order/Order/delOrderFile')?>",{'link':href},function(data){
                            if(data=='success'){
                                    $(_this).remove();
                            }else{
                                    alert(data);
                            }
                    });
            }
            return false;
    });
    $('#btn-update-order-history').click(function(){
        if($('select[name="orders_status_id"]', '#order-status').size()==0){
            $.ajax({
               url : '<?php echo U('Order/Order/orderHistory/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>',
               dataType : 'json',
               method:'get',
               cache :true,
               success:function(data){
                   if(data[1]){
                        var select = $('<select class="form-control" name="orders_status_id"></select>').appendTo('#order-status');
                        for(var i in data){
                            select.append('<option value="'+i+'">'+data[i]+'</option>');
                        }
                        $('#history-status-dialog').modal('show');
                   }else{
                       layer.msg('获取状态信息失败,请重试!');
                   }
               }
            });
        }else{
            $('#history-status-dialog').modal('show');
        }
    });
    $('form', '#history-status-dialog').submit(function(){
        var data = $( this ).serializeArray();
        var post_data = {}
        for(var i in data){
            if(data[i].name=='comments' || data[i].name=='orders_status_id'){
                post_data[data[i].name] = data[i].value;
            }
        }
        var url = $(this).attr('action');
        //console.log(post_data);
        //return false;
        $.ajax({
           url : url,
           dataType : 'text',
           method:'post',
           data:post_data,
           success:function(data){
               if(data=='1'){
                    $.ajax({
                        url     : "<?php echo U('Order/Data/orderData')?>/site_id/<?php echo $order_info['site_id']?>/order_id/<?php echo $order_info['orders_id']?>", 
                        dataType    : 'json',
                        async           : true,
                        timeout     : 60000,//请求时间
                        success : function(data){
                            if(data.status==1){
                                layer.msg('更新成功！');
                                location.reload();
                            }    
                        }
                    });
               }
           }
        });
        return false;
    });
    $('#btn-update-order').click(function(){
        $.ajax({
            url     : "<?php echo U('Order/Data/orderData')?>/site_id/<?php echo $order_info['site_id']?>/order_id/<?php echo $order_info['orders_id']?>", 
            dataType    : 'json',
            async           : true,
            timeout     : 60000,//请求时间
            success : function(data){
                if(data.status==1){
                    layer.msg('更新成功！');
                    location.reload();
                }    
            }
        });
        return false;
    });
    $('.btn-product-image').click(function(){
        var obj_product_image = $(this).prev();
    	CKFinder.popup( {
    		chooseFiles: true,
    		width: 800,
    		height: 600,
    		onInit: function( finder ) {
    			finder.on( 'files:choose', function( evt ) {
    				var file = evt.data.files.first();
    				obj_product_image.val(file.getUrl());
    			} );
    
    			finder.on( 'file:choose:resizedImage', function( evt ) {
    				obj_product_image.val(evt.data.resizedUrl);
    			} );
    		}
    	} );
    });
    $('#btn_add_delivery').click(function(){
        var last_tr = $(this).parent().parent();
        last_tr.prev('tr').clone(false).insertBefore(last_tr);
    });
    $('#btn-modify-address').click(function(){
        $('#delivery-panel input').attr('readonly', false);
    });
});
</script>