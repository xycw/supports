<h1>SQL批量执行</h1>
<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label>SQL</label>
            <textarea class="form-control" name="sql" rows="15"></textarea>
            <ul class="text-danger">
                <li>SQL中的表名请不要用前缀，都用大写且表名两端加__。比如表名 demo_products,表前缀是demo_,SQL中用 __PRODUCTS__来代替</li>
            </ul>
        </div>
        <button class="btn btn-default" id="btn-sql-exe">执行</button>
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
      
    function _task(url, post_data, fun_beforeSend, fun_success, fun_error){
        this.url = url;
        this.isfinish = false;
        
        this.run = function(){
            var _this = this;
            $.ajax({
                url		: this.url, 
                dataType	: 'json',
                async           : true,
                data            : post_data,
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
    $("input[name='checkall']").click(function(){
        var checked = $(this).is(':checked');
        $("input[name='site_id[]']").prop("checked", checked);
    });
    $('#btn-sql-exe').click(function(){
        var sql = $('textarea[name="sql"]').val();
        $('input[name="site_id[]"]:checked').each(function(){
            var site_id = $(this).val();
            var fun_success = function(data){
                if(data.success){
                    $(this).prop("checked", false);
                    $('#result'+site_id).html('<div style="background:green;color:#fff;">完成</div>');
                }else{
                    $('#result'+site_id).html('<div style="background:yellow;color:#000;">出错</div>');
                }
            }
            var fun_error = function(){
                $('#result'+site_id).html('<div style="background:red;color:#fff;">出错</div>');
            }        
            var fun_beforeSend = function(){
                $('#result'+site_id).html('<div style="background:blue;color:#fff;">准备执行...</div>');
            }        
            ajax_task.add('<?php echo U('Sys/SqlExe/run')?>', {'sql':sql, 'site_id':site_id}, fun_beforeSend, fun_success, fun_error);
        });
        ajax_task.run();
    });
});    
</script>    