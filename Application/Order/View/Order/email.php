<?php
if(IS_AJAX){
?>
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title" id="exampleModalLabel">订单状态邮件通知</h4>
</div>
<?php
}else{
?>
<h1>订单状态邮件通知</h1>
<?php
}
?>

<form id="form-email" action="<?php echo U('Order/Order/email/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>" method="post">
    <input type="hidden" name="action" value="send">
  <div class="form-group">
    <label>客户邮箱</label>
    <input type="email" class="form-control" value="<?php echo $order_info['customers_email_address']?>" readonly="readonly">
  </div>
  <div class="form-group">
    <label>邮件主题</label>
    <input type="text" class="form-control" name="email_title" placeholder="邮件主题" value="" readonly="">
  </div>
  <div class="form-group">
    <label>邮件模板</label>
    <tagLib name="html" />
    <html:select options="options_email_templates" name="email_templates" style="form-control" first="--模板选择--" />
  </div>
  <div class="form-group">
    <label>邮件内容</label>
    <textarea class="form-control" name="email_content" ></textarea>
  </div>    
    <div class="clearfix"><a class="btn btn-default pull-left" href="<?php echo U('Order/Order/view/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>">返回</a><button type="submit" class="btn btn-default pull-right">Submit</button></div>
    <br>
  <div class="form-group">
      <label>发送邮件历史记录</label>
      <?php
      if(empty($email_history)==false){
          echo '<table class="table table-bordered">';
          echo '<tr><th>订单状态</th><th>模板名</th><th>发送时间</th></tr>';
          foreach ($email_history as $_order_status=>$history){
              foreach($history as $entry){
                  echo '<tr>';
      ?>
            <td><?php echo $_order_status ?></td>
            <td><?php echo $entry['email_template_name'] ?></td>
            <td><?php echo $entry['time'] ?></td>
      <?php
                echo '</tr>';
              }  
          }
          echo '</table>';
      }else{
          echo '<p>无发送邮件记录!</p>';
      }
      ?>
      
  </div>   
</form>
<load href="__PUBLIC__/Js/ckeditor/ckeditor.js" />
<script>
upload_url="{:U('Order/uploadUrl')}";   
$(document).ready(function(){
    $('select[name="email_templates"]').change(function(){
        var email_templates_id = $(this).val();
        $.ajax({
            type: "POST",
            url: "<?php echo U('Order/Order/email/site_id/'.$order_info['site_id'].'/order_id/'.$order_info['orders_id'])?>",
            data:{'email_templates_id':email_templates_id, 'action':'get_template'},
            dataType: 'json',
            beforeSend: function () {layer.load(1);},
            success:function(data){
                $('input[name="email_title"]').val(data.email_template_title);
                CKEDITOR.instances.email_content.setData( data.email_template_content );
                layer.closeAll('loading');
            }
        });
    });    
    
    CKEDITOR.replace( 'email_content' );
    
    $('#form-email').submit(function(){
        var post_data = {
            action:'send',
            email_title:$('input[name="email_title"]').val(),
            email_content:CKEDITOR.instances.email_content.getData(),
            email_templates:$('select[name="email_templates"]').val()
        };
        var post_url  = $(this).attr("action");

        $.ajax({
            type: "POST",
            url: post_url,
            data:post_data,
            dataType: 'json',
            beforeSend: function () {layer.load(1);},
            success:function(data){
                layer.msg(data.info);
                layer.closeAll('loading');
            }
        });
        return false;
    });    
});

</script>    