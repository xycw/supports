<h1>网站接口更新</h1>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">上传升级包</h3>
          </div>
            <div class="panel-body">
                <form action="<?php echo U('Sys/Upgrade/upload') ?>" enctype="multipart/form-data" method="post">
                 <input type="hidden" name="action" value="upload">
                 <div class="form-group">
                     <label>上传更新包</label>
                     <input type="file" name="file">
                 </div>
                 <button type="submit" class="btn btn-default">上传</button>
             </form> 
            </div>
        </div>    
        
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">升级包</h3>
          </div>
          <div class="panel-body">
              <ul class="list-group">
            <?php
            foreach($patch_list as $entry){
            ?>
                  <li class="list-group-item"><lable><input type="radio" name="patch_file" value="<?php echo $entry?>"><?php echo $entry?></lable></li>
            <?php  
            }
            ?>
              </ul>    
              
              <button type="button" class="btn btn-default" id="btn-update">更新到选中网站</button>
          </div>
        </div>
    </div>
    <div class="col-lg-6">
        
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title">网站列表</h3>
  </div>
  <div class="panel-body">
    <table class="table table-bordered">
        <tr>
            <th><input type="checkbox" name="checkall" checked></th>
            <th>网站名称</th>
            <th>接口版本</th>
            <th><button class="btn btn-default btn-xs" id="get_all_version">获取所有网站接口版本</button></th>
        </tr>
        <?php
        foreach ($site_list as $entry){
            if($entry['status']=1){
        ?>
        <tr>
            <td><input type="checkbox" name="site_id[]" value="<?php echo $entry['site_id']?>" checked></td>
            <td><?php echo $entry['site_id'].'# '.$entry['site_name']?></td>
            <td style="position:relative;" id="td_version<?php echo $entry['site_id']?>">
                <span id="tip<?php echo $entry['site_id']?>" style="position:absolute;top:0;left:0;"></span>
                <input type="text" class="form-control" name="version[<?php echo $entry['site_id']?>]" id="input_version<?php echo $entry['site_id']?>" readonly="readonly">
            </td>
            <td><span class="glyphicon glyphicon-refresh" id="refresh<?php echo $entry['site_id']?>"></span></td>
        </tr>
        <?php
          }
        }
        ?>
    </table>  
  </div>
</div>  
    </div>
</div>


<script>
var ajax_task = (function(){
    var _num_max_runing  = 2;//最大同时ajax的数量
    var _queue_runing  = new Array();//运行下载的队列
    var _queue_waiting = new Array();//等待下载的队列
      
    function _task(url, fun_beforeSend, fun_success, fun_error){
        this.url = url;
        this.isfinish = false;
        
        this.run = function(){
            var _this = this;
            $.ajax({
                url		: this.url, 
                dataType	: 'json',
                async           : true,
                timeout 	: 60000,//请求时间
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
    $('#get_all_version').click(function(){
        $('input[name^="version"]').each(function(){
            var site_id = $(this).attr('name').replace(/[^\d]/g,'');
            var link = "<?php echo U('Sys/Upgrade/interface_version/site_id')?>/"+site_id;

            var fun_success = new Function('json',"$('#tip'+"+site_id+").text('');$('#input_version"+site_id+"').val(json.version);");
            var fun_error = new Function("$('#tip'+"+site_id+").css({'background':'yellow','color':'#000'}).text('获取接口版本失败')");
            var fun_beforeSend = new Function("$('#tip'+"+site_id+").css({'background':'red','color':'#fff'}).text('获取接口版本...')");
            ajax_task.add(link, fun_beforeSend, fun_success, fun_error);
        }); 
        ajax_task.run();
    });

    
    $(".glyphicon-refresh").click(function(){
        var site_id = $(this).attr("id").replace(/[^\d]/g, '');
        $('input[name="version['+site_id+']"]').val("");
        var link = "<?php echo U('Sys/Upgrade/interface_version/site_id')?>/"+site_id;
        var fun_success = new Function('json',"$('#tip'+"+site_id+").text('');$('#input_version"+site_id+"').val(json.version);");
        var fun_error = new Function("$('#tip'+"+site_id+").css({'background':'yellow','color':'#000'}).text('获取接口版本失败')");
        var fun_beforeSend = new Function("$('#tip'+"+site_id+").css({'background':'red','color':'#fff'}).text('获取接口版本...')");
        ajax_task.add(link, fun_beforeSend, fun_success, fun_error);
        ajax_task.run();
    });
    
    $("input[name='checkall']").click(function(){
        var checked = $(this).is(':checked');
        $("input[name='site_id[]']").prop("checked", checked);
    });
    
    $('#btn-update').click(function(){
        var patch_file = $('input[name="patch_file"]:checked').val();
        if(!patch_file){
            alert("请选择升级包!");
            return ;
        }
        var data = '';
        $('input[name="site_id[]"]:checked').each(function(){
            var site_id = $(this).val();
            var fun_success = new Function('data',"if(data.success){$('#tip'+"+site_id+").css({'background':'green','color':'#fff'}).text('升级成功')}else{$('#tip'+"+site_id+").css('background','yellow').text(data.error)}");
            var fun_error = new Function("$('#tip'+"+site_id+").css({'background':'yellow','color':'#000'}).text('升级失败')");
            var fun_beforeSend = new Function("$('#tip'+"+site_id+").css({'background':'red','color':'#fff'}).text('接口升级中...')");
            ajax_task.add('<?php echo U('Sys/Upgrade/upgrade')?>/patch_file/'+patch_file+'/site_id/'+site_id, fun_beforeSend, fun_success, fun_error);
        });
        ajax_task.run();

        
        
    });
});    
</script>    