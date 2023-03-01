<h1>用户信息</h1>
<form class="form-horizontal" action="<?php echo U('User/Administration/edit')?>" method="post">
    <input type="hidden" name="user_id" value="<?php echo isset($user_info['user_id'])?$user_info['user_id']:'0' ?>">
    <div class="form-group">
        <label class="col-sm-1 control-label">登录名</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" placeholder="用户名" name="username" value="<?php echo isset($user_info['username'])?$user_info['username']:'' ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">英文名</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" placeholder="英文名" name="english_name" value="<?php echo isset($user_info['english_name'])?$user_info['english_name']:'' ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">中文名</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" placeholder="中文名" name="chinese_name" value="<?php echo isset($user_info['chinese_name'])?$user_info['chinese_name']:'' ?>">
        </div>
    </div>    
    <div class="form-group">
        <label class="col-sm-1 control-label">密码</label>
        <div class="col-sm-4">
            <input type="password" class="form-control" name="password" placeholder="不修改密码请留空">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">邮箱</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" placeholder="能时刻接收到邮件的常用邮箱，如QQ邮箱" name="email" value="<?php echo isset($user_info['email']) ? $user_info['email'] : ''; ?>"><a href="javascript:;" class="btn btn-default" id="send-email">发送测试邮件</a>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">角色</label>
        <div class="col-sm-4">
            <tagLib name="html" />
            <html:select options="option_profile" name="profile" selected="option_profile_selected" style="form-control" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">邮件模板变量</label>
        <div class="col-sm-4">
            <table class="table table-bordered" id="table-mail-params">
                <th>变量名</th>
                <th>变量值</th>
                <th>变量说明</th>
        <?php
        if(empty($user_info['mail_template_params']))
            $mail_template_params = array();
        else{
            $mail_template_params = json_decode($user_info['mail_template_params'], true);
        }
        if(sizeof($mail_template_params)){
        foreach($mail_template_params as $entry){
        ?>
        <tr>
            <td><input class="form-control" type="text" name="mail_template_params[key][]" value="<?php echo $entry['key'] ?>"></td>
            <td><input class="form-control" type="text" name="mail_template_params[value][]" value="<?php echo $entry['value'] ?>"></td>
            <td><input class="form-control" type="text" name="mail_template_params[remark][]" value="<?php echo $entry['remark'] ?>"></td>
        </tr>
        <?php
        }
        }
        ?>
        <tr class="tr-mail-params">
            <td><input type="text" name="mail_template_params[key][]" value=""></td>
            <td><input type="text" name="mail_template_params[value][]" value=""></td>
            <td><input type="text" name="mail_template_params[remark][]" value=""></td>
        </tr>    
        <tr>
            <td colspan="3"><button type="button" class="btn btn-default btn-block" id="btn-add-mail-params">添加变量</button></td>
        </tr>
            </table>
        </div>
    </div>    
    <div class="form-group">
        <label class="col-sm-1 control-label">状态</label>
        <div class="col-sm-4">
            <tagLib name="html" />
            <html:select options="option_status" name="status" selected="option_status_selected" style="form-control" />
        </div>
    </div>    
    <?php if(session(C('USER_INFO') . '.user_id') == 1){?>
    <div class="form-group">
        <label class="col-sm-1 control-label">商城账号</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="saas_username">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-1 control-label">商城密码</label>
        <div class="col-sm-4">
            <input type="text" class="form-control" id="saas_password">
        </div>
    </div>
    <a href="javascript:;" class="btn btn-default" id="send-notice-email">发送商城账号信息通知邮件</a>
    <?php }?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">保存</button>
        </div>
    </div>
</form>
<script>
$('#send-email').click(function(){
    var name = $('input[name="chinese_name"]').val(),
        email = $('input[name="email"]').val();
    if(name == '') name = $('input[name="english_name"]').val();
    if(email == ''){
        layer.msg('请输入邮箱!');
        return false;
    }
    $.ajax({
        url : "<?php echo U('User/Administration/send_mail')?>",
        data : {'name':name, 'email':email},
        type : 'post',
        dataType : 'json',
        success : function(data){
            if(data.status == 1){
                layer.msg('发送成功!');
            }else{
                layer.msg('发送失败!' + data.info);
            }
        }
    });
});
$('#btn-add-mail-params').click(function(){
    var tr = $('.tr-mail-params').clone();
    tr.find('input').val("");
    tr.insertBefore($(this).parent().parent());
});
$('#send-notice-email').click(function(){
    var name = $('input[name="chinese_name"]').val(),
        email = $('input[name="email"]').val();
    if(name == '') name = $('input[name="english_name"]').val();
    if(email == ''){
        layer.msg('请输入邮箱!');
        return false;
    }
    var saas_username = $('#saas_username').val(),
        saas_password = $('#saas_password').val();
    if(saas_username == ''){
        layer.msg('请输入商城账号！');
        return false;
    }
    if(saas_password == ''){
        layer.msg('请输入商城密码！');
        return false;
    }
    $.ajax({
        url : "<?php echo U('User/Administration/send_notice_mail')?>",
        data : {'name':name, 'email':email,'saas_username':saas_username, 'saas_password':saas_password},
        type : 'post',
        dataType : 'json',
        success : function(data){
            if(data.status == 1){
                layer.msg('发送成功!');
            }else{
                layer.msg('发送失败!' + data.info);
            }
        }
    });
});
</script>