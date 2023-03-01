<?php if (!defined('THINK_PATH')) exit();?><div class="container">
    <div class="row">
        <div class="col-lg-1"><img class="img-responsive" alt="zencart站群订单管理系统" src="/supportsGit/Public/Image/App/logo.png"></div>
        <div class="col-lg-5">
            <p>FORTUNE网络科技工作室 版权所有©<?php echo date('Y') ?></p>
            <p>邮箱:fortune_tech@qq.com</p>
            <p>QQ&nbsp;&nbsp;:2646739154</p>
        </div>
        <div class="col-lg-5">
            <p class="banner-txt">因为专业所以卓越!</p>
        </div>
    </div>
</div>
<div id="btn-scroll-up" style="position:fixed;bottom:100px;right:5px;opacity: 0.5;font-size: 4em;display: none;">
<span class="glyphicon glyphicon-circle-arrow-up"></span>
</div>
<script>
$(document).ready(function(){
    $('#btn-scroll-up').click(function(){
        $('html,body').animate({scrollTop: '0px'}, 300);
        return false; 
    });
    
    $(window).scroll(function(){

        if($('html').scrollTop()>200){
            $('#btn-scroll-up').show();
        }else{
            $('#btn-scroll-up').hide();
        }        
    });
});
</script>