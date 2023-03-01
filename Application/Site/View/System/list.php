<h1><?php echo $form_title ?></h1>

<form action="<?php echo U('Site/System/list') ?>" method="post">
    <table class="table table-bordered">
        <tr>
            <th>订单归属</th><td><input class="form-control" type="text" name="system_area" placeholder="订单归属，请以|分隔开如长沙|梅州" value="<?php echo C('system_area');?>"></td>
        </tr>    
        <tr>
            <th>品牌</th><td><input class="form-control" type="text" name="system_brand" placeholder="品牌请以|分隔，如NBA|NHL" value="<?php echo C('system_brand');?>"></td>
        </tr>
        <tr>            
            <th>域名代理商URL</th><td><input class="form-control" type="text" name="system_url" placeholder="域名代理商URL|分隔，如cp.7211.com|www.8ie.com" value="<?php echo C('system_url');?>"></td>
        </tr>
        <tr>            
            <th>第三方DNS官网</th><td><input class="form-control" type="text" name="system_thirdgw" placeholder="第三方DNS官网|分隔，如www.cloudflare.com|www.dnspod.com" value="<?php echo C('system_thirdgw');?>"></td>
        </tr>


        <tr>
            <td colspan="2" class="text-center">
<button class="btn btn-default" type="submit">保存</button></td>
        </tr>
    </table>	
</form>