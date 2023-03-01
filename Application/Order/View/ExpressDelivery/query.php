<?php
if(IS_AJAX==false){
?>
<h1>物流查询(<?php echo $express_no?>)</h1>
<?php
}
?>
<style>
table.aikuaidi{width: 100%;}
table.aikuaidi tr{height:25px;}
</style>
<?php 
echo $result;
?>