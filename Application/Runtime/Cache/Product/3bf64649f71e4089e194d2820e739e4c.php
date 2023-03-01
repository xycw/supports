<?php if (!defined('THINK_PATH')) exit(); echo R('Common/Html/html_start');?> 
<header id="header"><?php echo R('Common/Layout/menu');?></header>
<div class="container" id="content">
<h1>产品数据库</h1>
<form class="form-horizontal" action="<?php echo U('Product/Database/list')?>" method="GET" id="form1">
    <div class="form-group">
        <label class="col-lg-1 control-label">语言</label>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="language_code" value="<?php echo I('language_code')?>">
        </div>
        <label class="col-lg-1 control-label">上架日期</label>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="date_added" value="<?php echo I('date_added')?>">
        </div> 
        <label class="col-lg-1 control-label">产品SKU</label>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="products_sku" value="<?php echo I('products_sku')?>">
        </div> 
        <label class="col-lg-1 control-label">上传日期</label>
        <div class="col-lg-2">
            <input type="text" class="form-control" name="upload_added" value="<?php echo I('upload_added')?>">
        </div> 
        <div class="col-lg-1">
        	 <button type="submit" class="btn btn-default">筛选</button>
        </div>     
        <div class="col-lg-6">
           
            <button type="button" data-toggle="modal" data-backdrop="static" data-target="#dialog-product-upload" class="btn btn-default" id="btn-product-upload2">按条件上传到网站</button>
            <button type="button" data-toggle="modal" data-backdrop="static" data-target="#dialog-product-upload" class="btn btn-default" id="btn-product-upload">批量上传到网站</button>
            <a class="btn btn-default" href="<?php echo U('Product/Database/add')?>">添加产品</a>
            <button type="button" data-toggle="modal" data-target="#dialog-csv-upload" class="btn btn-default">产品导入</button>
            <input type="hidden" name="export_csv" />
            <input type="hidden" name="export_saas" />
            <button type="button" data-toggle="modal" id="csv-export" class="btn btn-default">产品导出(必须选择.上传日期)</button>
            <button type="button" data-toggle="modal" id="saas-export" class="btn btn-default">商城产品导出(必须选择上传日期)</button>
        </div>
        <div class="col-lg-2 control-label">
            上传日期(历史记录)
        </div>
         <div class="col-lg-2">
             <select class="form-control">
                <?php foreach ($upload_time_new as $key => $value) { ?>
                 <option><?php echo $value;?></option>
                 <?php }?>
             </select>
         </div>
    </div>    
</form>
<div class="page-nav">
    <div class="row">
        <div class="col-lg-6">
            <div class="page-nav-info">
                <label>每页</label>
                <?php echo $list_row ?>
                <label>条(当前总数:</label> <?php echo $total ?><label>条)</label>
            </div>
        </div>
        <div class="col-lg-6 right">
            <?php
 W('Common/PageNavigation/page', array('page' => $page, 'num' => $list_row, 'count' => $total, 'name' => 'Product/Database/list', $page_data)); ?>
        </div>
    </div>
</div>
<table class="order-list mt5">
    <colgroup>
        <col width="90px">
        <col width="50px">
        <col width="100px">
        <col width="150px">
        <col width="270px">
        <col width="60px">
        <col width="68px">
        <col width="68px">
        <col width="60px">
        <col width="75px">
        <col width="85px">
        <col width="115px">
        <col width="115px">
        <col width="auto">
    </colgroup>
    <thead>
    <tr>
        <th><input type="checkbox" name="check_all">编号</th>
        <th>语言</th>
        <th>主图</th>
        <!-- <th>品牌</th> -->
        <th>型号<br>product_id#</th>
        <th>产品名称</th>
        <th>产品<br>描述</th>
        <th>产品<br>选项</th>
        <th>产品<br>属性</th>
        <th>图片</th>
        <th>零售价<br>特价<br>原价</th>
        <th>分类</th>
        <th>上架日期</th>
        <th>上传日期</th>
        <th>操作</th>
    </tr>
    </thead>
</table>

<?php
if(sizeof($products)){ $n = $total-$list_row*($page-1); ?>
<table class="order-list">
    <colgroup>
        <col width="90px">
        <col width="50px">
        <col width="100px">
        <col width="150px">
        <col width="270px">
        <col width="60px">
        <col width="68px">
        <col width="68px">
        <col width="60px">
        <col width="75px">
        <col width="85px">
        <col width="115px">
        <col width="115px">
        <col width="auto">
    </colgroup>
    <tbody>
<?php
 foreach ($products as $product){ ?>
    <tr class="sep-row"><td colspan="10"></td></tr>    
<?php  foreach ($product['detail'] as $products_detail){ ?>
    <tr class="order-hd" id="tr<?php echo $product['product_id'].'_'.$products_detail['language_code'] ?>">
        <td><input type="checkbox" name="product[]" value="<?php echo $product['product_id'].'_'.$products_detail['language_code'] ?>"><?php echo $n ?></td>
        <td><?php echo $products_detail['language_code'] ?></td>
       <!--  <td><?php ?></td> -->
       <?php if(!empty($product['product_images'])){ ?>

       	<td><?php echo '<a href="/images/'.$product['product_images'].'" target="__blank" title="点击放大600X600"><img src="/images/'.$product['product_images'].'" style="width:60px;height:60px;"></a>' ?></td>
       <?php	 }else{ echo '<td>'.'图片不存在'.'</td>'; }?>
        <td><?php echo $product['product_model'] ?>
        <?php
 if(!empty($product['get_model'])){ echo '<br/><span style="font-size:12px;color:#ff0000"><b>采集SKU: </b>'.$product['get_model'].'</span>'; } ?>

        <?php echo '<br>'.'ID#'.$product['product_id'] ?>
        <?php  switch ($product['orders_products_categories_id']) { case '1': $orders_products_categories_name='NBA现货'; break; case '2': $orders_products_categories_name='NFL'; break; case '3': $orders_products_categories_name='冰球NHL'; break; case '4': $orders_products_categories_name='MLB'; break; case '5': $orders_products_categories_name='NCAA'; break; case '6': $orders_products_categories_name='足球服'; break; case '7': $orders_products_categories_name='T卫衣杂款'; break; case '8': $orders_products_categories_name='帽子'; break; case '9': $orders_products_categories_name='定制款'; break; case '10': $orders_products_categories_name='待归类'; break; default: $orders_products_categories_name='待归类'; break; } echo '<br><b style="color:#ff0000">订货分类:'.$orders_products_categories_name.'</b>'; ?>
        </td>

        
        
        <td><?php echo $products_detail['product_name'].'<br><b style="color:#ff0000">网站分类:</b>'.$product['featured_category'] ?>
        	
        </td>
        <td class="center"><?php echo empty($products_detail['product_description'])?'空':'<a class="field_content" rel="product_description" href="'.U('Product/Database/view', array('product_id'=>$product['product_id'], 'lang'=>$products_detail['language_code'], 'field'=>'product_description')).'"><i class="glyphicon glyphicon-eye-open"></i></a>' ?></td>

        <td class="center"><?php echo empty($products_detail['product_attribute'])?'空':'<a class="field_content" rel="product_attribute" href="'.U('Product/Database/view', array('product_id'=>$product['product_id'], 'lang'=>$products_detail['language_code'], 'field'=>'product_attribute')).'"><i class="glyphicon glyphicon-eye-open"></i></a>' ?> <label title="<?php echo $products_detail['product_attribute'] ?>">值</label></td>
        <td class="center"><?php echo empty($products_detail['attribute'])?'空':'<a class="field_content" rel="attribute" href="'.U('Product/Database/view', array('product_id'=>$product['product_id'], 'lang'=>$products_detail['language_code'], 'field'=>'attribute')).'"><i class="glyphicon glyphicon-eye-open"></i></a>' ?> <label title="<?php echo $products_detail['attribute'] ?>">值</label></td>
        
        <td class="center">
        <?php
 if(empty($product['product_images'])){ echo '空'; }else{ $images = explode(',', $product['product_images']); echo '<a class="field_content" rel="product_images" href="'.U('Product/Database/view', array('product_id'=>$product['product_id'], 'lang'=>$products_detail['language_code'], 'field'=>'product_images')).'">'.sizeof($images).'张</a>'; } if(!empty($product['additional_images'])){ echo '<span title="'. $product['additional_images'] .'">有细节图</span>'; }else{ } ?>        
        </td>
        <td class="center"><?php if($product['retail_speciel_price']>0){ echo round($product['retail_speciel_price'],2); echo '<br>';} echo round($product['speciel_price'], 2).'<br>'.($product['speciel_price']==0?'':'<del>').round($product['product_price'], 2).($product['speciel_price']==0?'':'<del>'); ?></td>
        <?php  if(preg_match('/\|\|\|/',$product['featured_category'])){ $bigSize='大码'; }else{ $bigSize='标码'; } ?>

        <td class="center"><?php echo empty($product['featured_category'])?'空':'<a class="field_content" rel="featured_category" href="'.U('Product/Database/view', array('product_id'=>$product['product_id'], 'lang'=>$products_detail['language_code'], 'field'=>'featured_category')).'"><i class="glyphicon glyphicon-eye-open"></i></a>' ?> <label title="<?php echo $product['featured_category'] ?>"><?php echo $bigSize;?></label></td>
        <td><?php echo $product['date_added'] ?></td>
        <td><?php echo $product['upload_added'] ?></td>
        <td>
        <a class="btn btn-default btn-xs" href="<?php echo U('Product/Database/edit', array('product_id'=>$product['product_id']))?>">编辑</a>
        <br/>
        <a class="btn btn-default btn-xs" href="<?php echo U('Product/Database/del', array('product_id'=>$product['product_id']))?>" onclick='if (window.confirm("你确定要删除此产品吗?"))
                            return true;
                        else
                            return false;'>删除</a>
        </td>
    </tr>
<?php
 } $n--; } ?>
    </tbody>
</table>
<?php
} ?>  
<div class="page-nav">
    <div class="row">
        <div class="col-lg-6">
            <div class="page-nav-info">
                <label>每页</label>
                <?php echo $list_row ?>
                <label>条(当前总数:</label> <?php echo $total ?><label>条)</label>
            </div>
        </div>
        <div class="col-lg-6 right">
            <?php
 W('Common/PageNavigation/page', array('page' => $page, 'num' => $list_row, 'count' => $total, 'name' => 'Product/Database/list', $page_data)); ?>
        </div>
    </div>
</div>


<div class="modal fade" id="dialog-csv-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo U('Product/Database/csvUpload')?>" enctype="multipart/form-data" method="POST">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">CSV产品文件导入</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>CSV文件</label>
                    <input name="file_csv_products" type="file">
                    <p class="help-block"><a href="/supports/Public/example/products.csv" target="__blank">csv模板下载</a></p>
                </div>      
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="submit" class="btn btn-primary">导入</button>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dialog-product-upload">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">产品上传</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                <label>1、选择要上传到网站</label>
                <select class="form-control" name="site_product_upload">
                    <?php
 $xuhao=0; foreach($sites as $entry){ $web_site='xxx'; switch ($entry['is_sale']) { case '-1': $web_site = '不清缓存'; case '0': $web_site = '零售'; break; case '1': $web_site = '批发'; break; case '2': $web_site = 'B站'; break; case '3': $web_site = '帽子'; break; case '4': $web_site = '面罩'; break; case '5': $web_site = '袜子'; break; case '6': $web_site = 'anti-virus'; break; case '7': $web_site = '游戏手柄'; break; case '8': $web_site = '测试'; break; case '9': $web_site = 'Other'; break; default: break; } switch ($entry['site_name']) { case 'footballgreatjersey.com': $beizhu = ' <span style="color:#ff0000">jersey+10</span>~'; break; case 'footballonlinejerseys.com': $beizhu = ' <span style="color:#ff0000">jersey+20</span>~'; break; case 'onlinejerseysell.com': $beizhu = ' <span style="color:#ff0000">jersey+2</span>~'; break; case 'topstitchedgears.com': $beizhu = ' <span style="color:#ff0000">jersey+10</span>~'; break; default: $beizhu = ''; break; } if($entry['system_proupdate']=='True'){ $xuhao=$xuhao+1; echo '<option value="'.$entry['site_id'].'">'.'(序号:'.$xuhao.'==ID:'.$entry['site_id'].'# 品牌:'.$entry['system_brand'].') '.$entry['site_name'].'('.$web_site.','.$beizhu.$zwb.$rxh.$entry['system_cms'].')'.'</option>'; } } ?>
                </select>

                    <div class="form-group">
                        <label>①<a class="clear_cash" href="#" target="_blank">访问网站</a></label>
                     
                    </div>
                    <div class="form-group">
                        <label>②<a class="clear_cash" href="#" target="_blank">执行SQL（上传产品不用执行SQL）</a></label>
                        <textarea class="form-control" name="sql"></textarea>
                        <button type="button" class="btn btn-block btn-default btn-xs" id="btn-sql-exe">执行以上SQL</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>2、选择要上传到分类(<input type="checkbox" name="checkbox_get_category">获取在线分类)</label>
                    <div id="site_category_wrapper">
                        <input class="form-control" name="category_product_upload" placeholder="输入分类名">
                    </div>
                </div>    
                <div class="form-group">
                    <label>3、操作</label>
                    <div>
                        <select class="form-control" name="action_product_upload"><option value="1">上架</option><option value="0">下架</option></select>
                    </div>
                </div>
                <div class="result"></div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="type" value="1">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="btn-start-product-upload">开始上传</button>
            </div>
        </div>
    </div>
</div>

<script>

var ajax_task = (function(){
    var _num_max_runing  = 1;//最大同时ajax的数量
    var _queue_runing  = new Array();//运行下载的队列
    var _queue_waiting = new Array();//等待下载的队列
    
    function _task(url, data, fun_beforeSend, fun_success, fun_error){
        this.url = url;
        this.isfinish = false;
        
        this.run = function(){
            var _this = this;
            $.ajax({
                url		: this.url, 
                dataType	: 'json',
                async           : true,
                timeout 	: 60000,//请求时间
                'data'          : data,
                type            : "post",
                beforeSend    : function(){
                    fun_beforeSend();
                },
                success	: function(data){
                    this.isfinish = true;
                    fun_success(data);
                },
                error	: function(jqXHR, textStatus, errorThrown){
                    _this.isfinish = true;
                    fun_error();
                },
                complete 	: function(){
                    _this.isfinish = true;                                
                }
            }); 
        }
    }  
    
    var _loading = new (function(){
        var _status = false;
        return {
            start:function(){
                if(_status==false){
                    _status = true;
                    layer.load(1);
                }
            },
            end:function(){
                if(_status==true){
                    _status = false;
                    layer.closeAll('loading');
                }
            },
            listen_end:function(condition){
                if(eval(condition)){
                    _loading.end();
                }else{
                    setTimeout("ajax_task._loading().listen_end("+condition+")", 1200);
                }
            }
        }
    })();
    
    return {
        _loading:function(){
            return _loading;
        },
        idle:function(){
            return (_queue_waiting.length==0 && _queue_runing.length==0);
        },        
        add : function(url, data, fun_beforeSend, fun_success, fun_error){
            var task = new _task(url, data, fun_beforeSend, fun_success, fun_error);
            _queue_waiting.push(task);
        },
        stop : function(){
            if(_queue_runing.length>0){
                for(var i in _queue_runing){
                    _queue_runing.splice(0,_queue_runing.length);
                }
            }      
            if(_queue_waiting.length>0){
                _queue_waiting.splice(0,_queue_waiting.length);
            }            
        },
        run : function(){
            _loading.start();
            if(_queue_runing.length>0){
                //将已完成的任务从队列移除
                for(var i in _queue_runing){
                    if(_queue_runing[i].isfinish){
                        _queue_runing.splice(i,1);
                    }
                }
            }
            if(_queue_waiting.length>0){
                if(_queue_runing.length<_num_max_runing){
                    do{
                        var task = _queue_waiting.shift();
                        task.run();
                        _queue_runing.push(task);
                    }while(_queue_runing.length<_num_max_runing && _queue_waiting.length>0);
                }
            }
            
            if(_queue_runing.length>0 || _queue_waiting.length>0){
                setTimeout("ajax_task.run()", 1000);
            }
            _loading.listen_end('ajax_task.idle()');
        }
    };
    
})();

$(document).ready(function(){
    $("input[name='date_added']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $("input[name='upload_added']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $.ajaxSetup({
        beforeSend: function () {
            layer.load(1);
        },
        complete: function () {
            layer.closeAll('loading');
        }
    });    
    $('a.field_content').click(function(){
        var url = $(this).attr('href');
        var rel = $(this).attr('rel');
        $.getJSON(url, function(data){
            layer.msg(data[rel]);
        })
        
        return false;
    });
    $('input[name="check_all"]').click(function(){
        $('input[name="product[]"]').prop('checked', $(this).is(':checked'));
    });
    $('select[name="site_product_upload"]').change(function(){
        $('#site_category_wrapper').html('<input class="form-control" name="category_product_upload" placeholder="输入分类名">');
        $('input[name="checkbox_get_category"]').prop('checked', false);
    });
    $('#btn-product-upload2').click(function(){
        $('input[name="type"]').val(2);
    });
    $('#btn-product-upload').click(function(){
        $('input[name="type"]').val(1);
    });    
    $('input[name="checkbox_get_category"]').click(function(){
        var checked = $(this).is(':checked');
        var site_id = $('select[name="site_product_upload"]').val();
        if(checked){
            $.getJSON("<?php echo U('Product/Data/getCategory').'/site_id/'?>"+site_id, function(data){
                if(data.status){
                    var html = '<select class="form-control" name="category_product_upload">';
                    for(var i in data.category){
                        html += '<option value="'+data.category[i].categories_id+'">'+data.category[i].categories_name+'</option>';
                    }
                    html += '</select>';
                    $('#site_category_wrapper').html(html);
                }else{
                    layer.msg(data.error);
                }
            });            
        }else{
            $('#site_category_wrapper').html('<input class="form-control" name="category_product_upload" placeholder="输入分类名">');
        }
    });
    
    $('#btn-start-product-upload').click(function(){
        var site_id = $('select[name="site_product_upload"]').val();
        var category = $('select[name="category_product_upload"]').val();
        var action_product_upload = $('select[name="action_product_upload"]').val();
        if(!category) category=$.trim($('input[name="category_product_upload"]').val());

        if($('input[name="type"]').val()=='1'){
            if($('input[name="product[]"]:checked').size()==0){
                alert('请勾选要上传的产品!');
                return;
            }

            var i = 0;
            var n = 100;
            var product = new Array();
            $('input[name="product[]"]:checked').each(function(){
                i++;
                product.push($(this).val());
                if(i%n==0){
                    var _product = [].concat(product);
                    var fun_success = function(data){
                        if(data.status==1){
                            for(var i in data.result){
                                if(data.result[i].error){
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code+' td').css('background-color','#f2dede');
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code).tooltip({title:data.result[i].error});
                                }else{
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code+' td').css('background-color','#dff0d8');
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code).tooltip('destroy');
                                }
                            }
                        }else{
                            for(var i in _product){
                                $('#tr'+_product[i]+' td').css('background-color','#f2dede');
                            }
                        }
                    }
                    var fun_error = function(){
                        for(var i in _product){
                            $('#tr'+_product[i]+' td').css('background-color','#f2dede');
                        }
                    }
                    var fun_beforeSend = function(){
                        for(var i in _product){
                            $('#tr'+_product[i]+' td').css('background-color','#d9edf7');
                        }
                    }                
                    ajax_task.add("<?php echo U('Product/Data/upload')?>", {'site_id':site_id, 'category':category, 'action_product_upload':action_product_upload, 'product[]':product}, fun_beforeSend, fun_success, fun_error);
                    product = new Array();
                }
            });
            if(i%n!=0){
                var _product = [].concat(product);
                    var fun_success = function(data){
                        if(data.status==1){
                            for(var i in data.result){
                                if(data.result[i].error){
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code+' td').css('background-color','#f2dede');
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code).tooltip({title:data.result[i].error});
                                }else{
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code+' td').css('background-color','#dff0d8');
                                    $('#tr'+data.result[i].product_id+'_'+data.result[i].language_code).tooltip('destroy');
                                }
                            }
                        }else{
                            for(var i in _product){
                                $('#tr'+_product[i]+' td').css('background-color','#f2dede');
                            }
                        }
                    }
                var fun_error = function(){
                    for(var i in _product){
                        $('#tr'+_product[i]+' td').css('background-color','#f2dede');
                    }
                }
                var fun_beforeSend = function(){
                    for(var i in _product){
                        $('#tr'+_product[i]+' td').css('background-color','#d9edf7');
                    }
                }         
                ajax_task.add("<?php echo U('Product/Data/upload')?>", {'site_id':site_id, 'category':category, 'action_product_upload':action_product_upload, 'product[]':product}, fun_beforeSend, fun_success, fun_error);
            }
            ajax_task.run();
        }else{
            var language_code = $('input[name="language_code"]').val();
            var date_added    = $('input[name="date_added"]').val();
            var upload_added  = $('input[name="upload_added"]').val();
            var products_sku  = $('input[name="products_sku"]').val();
            
            $('.result', '#dialog-product-upload').html('<p class="bg-success">计算需要上传的产品数量</p>');
            
            $.ajax({
                url         : "<?php echo U('Product/Data/upload2')?>",
                data        : {'language_code':language_code, 'date_added':date_added,'upload_added':upload_added, 'products_sku':products_sku,'action': 'count'},
                type         : 'post',
                dataType     : 'json',
                success      : function(data){
                    $('.result', '#dialog-product-upload').append('<p class="bg-success">'+data.tip+'</p>');
                    if(data.num_page){
                        for(var i = 1;i<=data.num_page;i++){
                            $('.result', '#dialog-product-upload').append('<p class="bg-info" id="page'+i+'">第'+i+'批准备上传.</p>');
                            var fun_success = function(data){
                                if(data.success)
                                    $('#page'+data.cur_page, '#dialog-product-upload').attr('class', 'bg-success');
                                else
                                    $('#page'+data.cur_page, '#dialog-product-upload').attr('class', 'bg-danger');
                                    
                                $('#page'+data.cur_page, '#dialog-product-upload').text(data.tip);
                            }    
                            var fun_beforeSend = function(){}
                            var fun_error = function(){}                            
                            ajax_task.add("<?php echo U('Product/Data/upload2')?>", {'language_code':language_code, 'date_added':date_added, 'upload_added':upload_added, 'products_sku':products_sku,'page': i, 'site_id':site_id, 'action_product_upload':action_product_upload,'category':category}, fun_beforeSend, fun_success, fun_error);
                        }

                        
                        ajax_task.run();
                    }
                }
            });
        }
    });
    $('#dialog-product-upload').on('hidden.bs.modal', function (e) {
        ajax_task.stop();
        $('.result', '#dialog-product-upload').html('');
    })

    $(document).ready(function(){
        urls=$("select[name='site_product_upload']").find("option:selected").text();
        p = /\(.*?\)/g;
        urls = urls.replace(p, '');
        urls= urls.trim();
          //alert(urls);
       // urlsz='http://www.'+urls+'/clear_cache.php';
        urlsz='http://'+urls;
        $(".clear_cash").attr('href',urlsz);
    });

    $("select[name='site_product_upload']").on('change',function(){
        urlss=$("select[name='site_product_upload']").find("option:selected").text();
       // p = /\(.*?\)/g;
       // alert(p);
        urlss = urlss.replace(p, '');
        urlss= urlss.trim();
       // urlssz='http://www.'+urlss+'/clear_cache.php';
        urlssz='http://'+urlss;
        $(".clear_cash").attr('href',urlssz);
    });

    $('#btn-sql-exe').click(function(){
        var sql = $('textarea[name="sql"]').val();
        var site_id = $('select[name="site_product_upload"]').val();
        if(sql=='') 
            layer.msg('SQL语句为空!');
        else{
            var fun_success = function(data){
                if(data.success){
                    layer.msg('SQL执行完成!');
                }else{
                    layer.msg('出错!'+data.error);
                }
            }
            var fun_error = function(){
                layer.msg('出错!');
            }        
            var fun_beforeSend = function(){
                layer.msg('执行中..');
            }        
            ajax_task.add('<?php echo U('Product/Database/sql_exe')?>', {'sql':sql, 'site_id':site_id}, fun_beforeSend, fun_success, fun_error);
            ajax_task.run();
        }
    });
    $('#csv-export').click(function(){
        $('#form1').attr('target', '_blank');
        var action = $('#form1').attr('action');
        $('input[name="export_csv"]').val("1");
        $('#form1').submit();
        $('#form1').attr('target', '');
    });
    $('#saas-export').click(function(){
        $('#form1').attr('target', '_blank');
        var action = $('#form1').attr('action');
        $('input[name="export_saas"]').val("1");
        $('#form1').submit();
        $('input[name="export_saas"]').val("");
        $('#form1').attr('target', '');
    });
});
</script>
</div>
<footer id="footer"><?php echo R('Common/Layout/footer');?></footer>
<?php echo R('Common/Html/html_end');?>