<div class="modal-header">
   <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
   <h4 class="modal-title">留言详情</h4>
 </div>
<div class="modal-body">
<div class="form-group">
    <label>网站</label>
    <div><?php echo $contact['site_name']?></div>
</div>
<div class="form-group">
    <label>客户邮箱</label>
    <div><?php echo $contact['r_email_address']?></div>
</div>
<div class="form-group">
    <label>客户名字</label>
    <div><?php echo $contact['r_full_name']?></div>
</div>
<div class="form-group">
    <label>联系电话</label>
    <div><?php echo $contact['r_telephone']?></div>
</div>
<div class="form-group">
    <label>留言内容</label>
    <div><?php echo $contact['r_message']?></div>
</div>
<div class="form-group">
    <label>留言IP</label>
    <div><?php echo $contact['r_ip']?></div>
</div>
<div class="form-group">
    <label>留言时间</label>
    <div><?php echo $contact['r_send_time']?></div>
</div>
<?php
if($contact['state']==1){
    $title = '标记未读';
    $link = U('Customers/Contact/mark', array('site_id'=>$contact['site_id'], 'contact_id'=>$contact['contact_us_records_id'], 'state'=>0));
}else{
    $title = '标记已读';
    $link = U('Customers/Contact/mark', array('site_id'=>$contact['site_id'], 'contact_id'=>$contact['contact_us_records_id'], 'state'=>1));
}
?>
<button class="btn btn-default" id="btn-mark-readed" onclick="change_statu('<?php echo $link?>');"><?php echo $title?></button>

</div>

<script>
// $('#btn-mark-readed').click(function(){
//     $.getJSON($(this).attr('href'), function(data){
//        if(data.state==1) 
//            layer.msg('标记成功!');
//        else
//            layer.msg('标记失败!');
//     });
//     return false;
// });
// 
function change_statu(obj){
    $.getJSON(obj, function(data){
       if(data.state==1){ 
           layer.msg('标记成功!');
       }else{
           layer.msg('标记失败!');
       }
    });
    window.location.reload();
    return false;
}
</script>