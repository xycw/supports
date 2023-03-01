<h1>商城程序更新</h1>
<div class="alert alert-warning">上传很方便,使用须谨慎！上传前请认真检查是否是你想要上传的压缩包和网站！文件会覆盖程序中已有的文件！</div>
<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">上传文件包</h3>
          </div>
            <div class="panel-body">
                <form action="<?php echo U('Sys/FileUpload/upload') ?>" enctype="multipart/form-data" method="post">
                 <input type="hidden" name="action" value="upload">
                 <div class="form-group">
                     <label>上传文件包</label>
                     <input type="file" name="file">
                 </div>
                 <button type="submit" class="btn btn-default">上传</button>
             </form> 
            </div>
        </div>    
        
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">上传文件包</h3>
          </div>
          <div class="panel-body">
              <ul class="list-group">
            <?php
            foreach($file_list as $entry){
            ?>
                  <li class="list-group-item"><lable><input type="radio" name="zip_file" value="<?php echo $entry['f']?>"><?php echo $entry['f']?>(<?php echo $entry['e'].'秒后文件会自动删除!'?>)</lable></li>
            <?php  
            }
            ?>
              </ul>    
              
              <button type="button" class="btn btn-default" id="btn-update">上传到选中网站</button>
              <button type="button" class="btn btn-default" id="btn-compare">压缩包的文件与网站程序比较</button>
          </div>
            <ul class="text-danger">
                <li>程序会在上传后4小时会自动删除;</li>
                <li>压缩包会上传到网站程序的根目录中，并解压;</li>
                <li>压缩包中的一些文件，名称不要用中文;</li>
                <li>程序是如何判断压缩包中的全部成功上传到网站？
                    <ul>
                        <li>判读文件是否在;</li>
                        <li>判读文件的md5值;</li>
                    </ul>
                </li>
            </ul>
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
            <th><input type="checkbox" name="checkall"></th>
            <th>网站名称</th>
            <th>结果</th>
        </tr>
        <?php
        foreach ($site_list as $entry){
        ?>
        <tr>
            <td><input type="checkbox" name="site_id[]" value="<?php echo $entry['site_id']?>"></td>
            <td><?php echo $entry['site_name']?></td>
            <td id="result<?php echo $entry['site_id']?>"></td>
        </tr>
        <?php
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
    $('#btn-compare').click(function(){
        var zip_file = $('input[name="zip_file"]:checked').val();
        if(!zip_file){
            alert("请选择要上传压缩包!");
            return ;
        }
        var data = '';
        $('input[name="site_id[]"]:checked').each(function(){
            var site_id = $(this).val();
            var fun_success = function(data){
                if(data.same){
                    $(this).prop("checked", false);
                    $('#result'+site_id).html('<div style="background:green;color:#fff;">一样</div>');
                }else{
                    $('#result'+site_id).html('<div style="background:yellow;color:#000;">不一样</div>');
                }
            }
            var fun_error = function(){
                $('#result'+site_id).html('<div style="background:red;color:#fff;">比较失败!</div>');
            }        
            var fun_beforeSend = function(){
                $('#result'+site_id).html('<div style="background:blue;color:#fff;">比较中...</div>');
            }        
            ajax_task.add('<?php echo U('Sys/FileUpload/compare')?>/zip/'+zip_file+'/site_id/'+site_id, fun_beforeSend, fun_success, fun_error);
        });
        ajax_task.run();
    });
    
    $("input[name='checkall']").click(function(){
        var checked = $(this).is(':checked');
        $("input[name='site_id[]']").prop("checked", checked);
    });
    
    $('#btn-update').click(function(){
        var patch_file = $('input[name="zip_file"]:checked').val();
        if(!patch_file){
            alert("请选择要上传的压缩包!");
            return ;
        }
        var data = '';
        $('input[name="site_id[]"]:checked').each(function(){
            var site_id = $(this).val();
            var fun_success = function(data){
                if(data.success){
                    $('#result'+site_id).html('<div style="background:green;color:#fff;">上传成功</div>');
                }else{
                    $('#result'+site_id).html('<div style="background:yellow;color:#000;">上传失败</div>');
                }
            }
            var fun_error = function(){
                $('#result'+site_id).html('<div style="background:red;color:#fff;">上传失败!</div>');
            }        
            var fun_beforeSend = function(){
                $('#result'+site_id).html('<div style="background:blue;color:#fff;">上传中...</div>');
            }   
            ajax_task.add('<?php echo U('Sys/FileUpload/transfer')?>/zip/'+patch_file+'/site_id/'+site_id, fun_beforeSend, fun_success, fun_error);
        });
        ajax_task.run();

        
        
    });
});    
</script>    