<load href="__PUBLIC__/Js/bootstrap-select/css/bootstrap-select.css" />
<load href="__PUBLIC__/Js/bootstrap-select/js/bootstrap-select.js" />
<h1><?php echo $form_title ?></h1>



<form action="<?php echo U('Site/Site/edit') ?>" method="post">

    <input type="hidden" name="site_id" value="<?php echo isset($site_info['site_id'])?$site_info['site_id']:0; ?>">

    <table class="table table-bordered">

        <tr>

            <th>网站类型</th>

            <td>

                <select name="type" class="form-control">

                    <option value="1"<?php if($site_info['type']==1) echo ' selected' ?>>独立站</option>

                    <option value="10"<?php if($site_info['type']==10) echo ' selected' ?>>平台站</option>

                    <option value="2"<?php if($site_info['type']==2) echo ' selected' ?>>B站</option>

                </select>

            </td>

        </tr>            

			<tr>
				<th>新商城:</th>
				<td>
					<label>
						<input type="radio" name="new_saas" value="1"<?php echo $site_info['new_saas']==1 ? ' checked="checked"' : '';?>/>
						<span>是</span>
					</label>
					&nbsp;&nbsp;
					<label>
						<input type="radio" name="new_saas" value="0"<?php echo $site_info['new_saas']==0 ? ' checked="checked"' : '';?>/>
						<span>否</span>
					</label>
				</td>
			</tr>

        <tr>

            <th>域名</th><td><input class="form-control" type="text" name="site_name" placeholder="网站名称" value="<?php echo isset($site_info['site_name'])?$site_info['site_name']:''; ?>"></td>

        </tr>    

        <tr>

            <th>订单前缀</th><td><input class="form-control" type="text" name="order_no_prefix" placeholder="请用纯英文字符" value="<?php echo isset($site_info['order_no_prefix'])?$site_info['order_no_prefix']:''; ?>"></td>

        </tr>

        <tr>            

            <th>网站首页</th><td><input class="form-control" type="text" name="site_index" placeholder="http://www.demo.com" value="<?php echo isset($site_info['site_index'])?$site_info['site_index']:''; ?>"></td>

        </tr>

        <tr>

            <th>备用域名</th><td><input class="form-control" type="text" name="site_index_spare" placeholder="http://www.demo2.com" value="<?php echo isset($site_info['site_index_spare'])?$site_info['site_index_spare']:''; ?>"></td>

        </tr>     

        <tr>            

            <th>图片URL</th><td><input class="form-control" type="text" name="img_url" placeholder="http://www.demo.com/images/" value="<?php echo isset($site_info['img_url'])?$site_info['img_url']:''; ?>"></td>

        </tr>

        <tr>            

            <th>接口</th><td><input class="form-control" type="text" name="site_interface" placeholder="http://www.demo.com/interface-name/" value="<?php echo isset($site_info['site_interface'])?$site_info['site_interface']:''; ?>"></td>

        </tr>

        <tr>            

            <th>备注</th><td><textarea class="form-control" name="remark"><?php echo isset($site_info['remark'])?$site_info['remark']:''; ?></textarea></td>

        </tr>

        <tr>

             <th>域名过期</th><td><input class="form-control" type="text" name="date_expired" placeholder="域名过期日期" value="<?php echo isset($site_info['date_expired'])?$site_info['date_expired']:''; ?>"></td>

        </tr>

         <tr>

             <th>SSL过期</th><td><input class="form-control" type="text" name="ssl_expired" placeholder="SSL过期日期" value="<?php echo isset($site_info['ssl_expired'])?$site_info['ssl_expired']:''; ?>"></td>

        </tr>

        <tr>

             <th>邮箱</th>

            <td>

                <table class="table table-bordered">

                    <?php

                    if(isset($email_data) && sizeof($email_data)){

                        foreach($email_data as $entry){

                    ?>

                    <tr>
                        <td>
                            <select class="form-control selectpicker" data-live-search="true" name="email_address[]">
                                <option value="">请选择邮箱</option>
                                <?php foreach ($customer_service_array as $v){?>
                                <option value="<?php echo $v;?>"<?php if(isset($entry['address']) && $entry['address'] == $v){?> selected="selected"<?php }?>><?php echo $v;?></option>
                                <?php }?>
                            </select>
                        </td>
                    </tr>

                    <?php

                        }

                    }

                    ?>

                    <tr>
                        <td>
                            <select class="form-control selectpicker" data-live-search="true" name="email_address[]">
                                <option value="">请选择邮箱</option>
                                <?php foreach ($customer_service_array as $v){?>
                                <option value="<?php echo $v;?>"><?php echo $v;?></option>
                                <?php }?>
                            </select>
                        </td>
                    </tr>

                </table>

            </td>

        </tr>

      <tr>

             <th>商品数据:</th><td>

             <label>

              <input type="radio" name="is_sale" value="0" <?php echo (($site_info['is_sale']?:0)=='0') ? 'checked="checked"' : ''; ?>/>

              <span>零售</span>

              </label>

              &nbsp;&nbsp;

              <label>

              <input type="radio" name="is_sale" value="1" <?php echo (($site_info['is_sale']?:0)=='1') ? 'checked="checked"' : '';?>/>

              <span>批发</span>

              </label>

              &nbsp;&nbsp;

              <label>

              <input type="radio" name="is_sale" value="2" <?php echo (($site_info['is_sale']?:0)=='2') ? 'checked="checked"' : '';?>/>

              <span>B站</span>

              </label>

              &nbsp;&nbsp;

              <label>

              <input type="radio" name="is_sale" value="3" <?php echo (($site_info['is_sale']?:0)=='3') ? 'checked="checked"' : '';?>/>

              <span>帽子</span>

              </label>

              &nbsp;&nbsp;

              <label>

              <input type="radio" name="is_sale" value="8" <?php echo (($site_info['is_sale']?:0)=='8') ? 'checked="checked"' : '';?>/>

              <span>测试</span>

              </label>

              &nbsp;&nbsp;

              <label>

              <input type="radio" name="is_sale" value="9" <?php echo (($site_info['is_sale']?:0)=='9') ? 'checked="checked"' : '';?>/>

              <span>Other</span>

              </label>

              &nbsp;&nbsp;
               <label>

              <input type="radio" name="is_sale" value="10" <?php echo (($site_info['is_sale']?:0)=='10') ? 'checked="checked"' : '';?>/>

              <span>定制</span>

              </label>

             </td>

        </tr>

             <tr>

             <th>商品更新列表</th><td>

        

              <select class="form-control" name="system_proupdate">

                 <?php echo $system_proupdate;?>

              </select>

             </td>

        </tr>

         <tr>

             <th>后台路径</th><td><input class="form-control" type="text" name="system_weburl" placeholder="后台路径" value="<?php echo isset($site_info['system_weburl']) ? $site_info['system_weburl'] : ''; ?>"></td>

        </tr>

         <tr>

             <th>后台账号</th><td><input class="form-control" type="text" name="system_weblogin" placeholder="后台账号" value="<?php echo isset($site_info['system_weblogin']) ? $site_info['system_weblogin'] : ''; ?>"></td>

        </tr>

        <tr>

            <th>后台密码</th><td><input class="form-control" type="text" name="system_webpass" placeholder="后台密码" value="<?php echo isset($site_info['system_webpass'])?$site_info['system_webpass']:''; ?>"></td>

        </tr>

       <!-- <tr>

             <th>ZWB[中外宝]</th><td>

        

             	<select class="form-control" name="system_zwb">

             		 <?php //echo $system_zwb;?>

             	</select>

             </td>

        </tr>

        <tr>

             <th>RXH[融信汇]</th><td>

        

              <select class="form-control" name="system_rxh">

                 <?php //echo $system_rxh;?>

              </select>

             </td>

        </tr> -->

       <tr>

             <th>CMS[网站]</th><td>

        

             	<select class="form-control" name="system_cms">

             		 <?php echo $system_cms;?>

             	</select>

             </td>

        </tr>

      <tr>

             <th>订单归属</th><td>

        

             	<select class="form-control" name="system_area">

             		<?php echo $system_area;?>

             	</select>

             </td>

        </tr>

      <tr>

             <th>品牌</th><td>

        

             	<select class="form-control" name="system_brand">

             		<?php echo $system_brand;?>

             	</select>

             </td>

        </tr>

      	<tr>

             <th>部门归属</th><td>

        

             	<select class="form-control" name="system_depart">
					<option value="0">无</option>
					<?php foreach ($system_depart as $department_id => $department_name){?>
					<option value="<?php echo $department_id;?>"<?php if(isset($site_info['system_depart']) && $site_info['system_depart'] == $department_id){?> selected="selected"<?php }?>><?php echo $department_name;?></option>
					<?php }?>
             	</select>

             </td>

        </tr>

      	<tr>

             <th>推广员</th><td>

                <!-- <tagLib name="html" />

                <html:select options="data_user_tuiguang" name="tuiguang_user_id" selected="tuiguang_user_id" style="form-control" first="--推广员--" /> -->

                <select class="form-control selectpicker" data-live-search="true" name="system_tuiguangy">
					<option value="0">无</option>
					<?php foreach ($system_tuiguangy as $user_id => $chinese_name){?>
					<option value="<?php echo $user_id;?>"<?php if(isset($site_info['system_tuiguangy']) && $site_info['system_tuiguangy'] == $user_id){?> selected="selected"<?php }?>><?php echo $chinese_name;?></option>
					<?php }?>
              </select>

             </td>

        </tr>

      	<tr>

             <th>第三方DNS官网</th>

            <td>

                <table class="table table-bordered">

                    <tr><th>第三方DNS官网</th><th>第三方DNS</th><th>第三方DNS管理账号</th><th>第三方DNS管理邮箱</th></tr>



                    <tr>

                        <td>

                        	 <select class="form-control" name="system_thirdgw">

			             		<?php echo $system_thirdgw;?>

			             	</select>

                        </td>

                        <td>

                        	<table>

                        		<tr><th>dns1</th><td><input type="text" class="form-control" name="email_dns[]" value="<?php echo $system_dns_block['email_dns'][0];?>"></td></tr>

                        		<tr><th>dns2</th><td><input type="text" class="form-control" name="email_dns[]" value="<?php echo $system_dns_block['email_dns'][1];?>"></td></tr>

                        		<tr><th>dns3</th><td><input type="text" class="form-control" name="email_dns[]" value="<?php echo $system_dns_block['email_dns'][2];?>"></td></tr>

                        		<tr><th>dns4</th><td><input type="text" class="form-control" name="email_dns[]" value="<?php echo $system_dns_block['email_dns'][3];?>"></td></tr>

                        	</table>



                        </td>

                        <td><input type="text" class="form-control" name="system_dns_username" value="<?php echo $system_dns_block['system_dns_username'];?>"></td>

                        <td><input type="text" class="form-control" name="system_dns_email" value="<?php echo $system_dns_block['system_dns_email'];?>"></td>



                    </tr>

                </table>

            </td>



      

        </tr>

        <tr>

            <td colspan="2" class="text-center"><a class="btn btn-default" href="<?php echo U('Site/Site/list') ?>">返回</a>&nbsp;&nbsp;

<button class="btn btn-default" type="submit">保存</button></td>

        </tr>

    </table>	

</form>





<script>

    $("input[name='date_expired']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});

    $("input[name='ssl_expired']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});

</script>