<h1>文章列表</h1>

<div class="form-inline" style="margin:10px 0;">
    <div class="form-group">
    <label>批量发送到:</label>
    <tagLib name="html" />
    <html:select options="options_wp_site" name="wp_site" first="--请选择要发送的网站--" style="form-control" />
    <button class="btn btn-default" id="btn-start-post" type="button">批量发送</button>
    </div>
    <div class="form-group">
        <a class="btn btn-default" href="<?php echo U('Wordpress/Article/add')?>">添加文章</a>
    </div>
</div>
<form action="<?php echo U('Wordpress/Article/list')?>" method="get" class="form-inline" style="margin:10px 0;">
    <div class="form-group">
    <label>文章类型:</label>
    <tagLib name="html" />
    <html:select options="options_articles_types" name="article_type" selected="article_type_selected" first="--不限--" style="form-control" />
    </div>
    <div class="form-group">
        <label>发送状态:</label>
        <html:radio radios="options_post_status" name="post_status" checked="post_status_selected"/>
        <html:select options="options_wp_site" name="site_id" selected="site_id_selected" style="form-control" />
    </div>    
    <div class="form-group">
        <button class="btn btn-default" id="btn-start-post" type="submit">筛选</button>
    </div>
</form>
<table class="table table-border">
    <tr>
        <th><input type="checkbox" name="check_all"></th>
        <th>ID</th>
        <th>类型</th>
        <th>标题</th>
        <th>已发布</th>
        <th>入库日期</th>
        <th>操作</th>
    </tr>
<?php
foreach($array_articles as $row){
?>    
    <tr id="item-<?php echo $row['articles_id']?>">
        <td><input type="checkbox" name="articles_id[]" value="<?php echo $row['articles_id']?>"></td>
        <td><?php echo $row['articles_id']?></td>
        <td><?php echo $row['type_name']?></td>
        <td><?php echo $row['articles_title']?></td>
        <td><?php echo $row['is_post']?'<a class="view-post" rel="'.$row['articles_id'].'"><i class="glyphicon glyphicon-ok"></i></a>':'<i class="glyphicon glyphicon-remove"></i>' ?></td>
        <td><?php echo $row['add_date']?></td>
        <td>
            <a class="btn btn-default" href="<?php echo U('Wordpress/Article/edit/id/'.$row['articles_id'])?>">编辑</a>
            <a class="btn btn-default" href="<?php echo U('Wordpress/Article/post/id/'.$row['articles_id'])?>">发布</a>
        </td>
    </tr>
<?php
}
?>
</table>

<div class="page-nav">
    <div class="row">
        <div class="col-lg-6">
            <div class="page-nav-info">每页<?php echo $num ?>条,共:<?php echo $count ?>条</div>
        </div>
        <div class="col-lg-6 right">
            <?php
            W('Common/PageNavigation/page', array('page' => $page, 'num' => $num, 'count' => $count, 'name' => 'Wordress/Article/list', $data_page));
            ?>
        </div>
    </div>
</div>

<div class="modal fade bs-example-modal-sm" id="wp_post_link">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">博客链接</h4>
            </div>         
            <div class="modal-body">
                
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
        add : function(url, fun_beforeSend, fun_success, fun_error){
            var task = new _task(url, fun_beforeSend, fun_success, fun_error);
            _queue_waiting.push(task);
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
    $('#wp_post_link').modal('hide');
    $('.view-post').click(function(){
        var articles_id = $(this).attr('rel');
        $('.modal-body', '#wp_post_link').html("");
        $.getJSON("<?php echo U('Wordpress/Article/ajax_post_link/article_id')?>/"+articles_id, function(data){
            for(var i in data){
                $('.modal-body', '#wp_post_link').append('<p><a target="_blank" href="'+data[i].site_index+'?p='+data[i].post_id+'">'+data[i].site_name+'</a></p>')
            }       
            $('#wp_post_link').modal('show');
        });        
        return false;
    });
    $('input[name="check_all"]').click(function () {
        var checked = $(this).is(':checked');
        $('input[name="articles_id[]"]').prop("checked", checked);
    }); 
    $('select[name="wp_site"]').change(function(){
        var site_id = $(this).val();
        var _this   = this;
        $('.post-category').remove();
        $.getJSON("<?php echo U('Wordpress/Article/ajax_get_categories/site_id')?>/"+site_id, function(data){
            var obj_select = $('<select class="form-control post-category" name="category['+site_id+']"></select>');
            for(var i in data){
                obj_select.append('<option value="'+data[i].id+'">'+data[i].name+'</option>');
            }
            $(_this).after(obj_select);
        });
    });
    $('#btn-start-post').click(function(){
        if($('select[name="wp_site"]').val()==''){
            alert('请选择要发送的网站');
            return false;
        }
        var site_id = $('select[name="wp_site"]').val();
        if($('select[name="category['+site_id+']"]').size()==0){
            alert('请选择要发送到分类');
            return false;
        }
        var category = $('select[name="category['+site_id+']"]').val();
        var url      = "<?php echo U('Wordpress/Article/post')?>";
        $('input[name="articles_id[]"]:checked').each(function(){
            var articles_id = $(this).val();
            var data = {'post_site[]':site_id, 'id':articles_id, 'action':'post'}
            data['category['+site_id+']'] = category;
            var fun_success = function(data){
                if(data.success==1){
                    $('#item-'+articles_id).attr('class', 'bg-success');
                }else
                    $('#item-'+articles_id).attr('class', 'bg-danger');
            }
            var fun_error = function(){
                $('#item-'+articles_id).attr('class','bg-danger');
            }
            var fun_beforeSend = function(){
                $('#item-'+articles_id).attr('class','bg-primary');
            }            
            ajax_task.add(url, data, fun_beforeSend, fun_success, fun_error);
        });
        ajax_task.run();
    });
});
</script>