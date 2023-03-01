<h1>产品数据库新增/编辑</h1>
<?php // echo '<pre>'; print_r($product); die;?>
<form class="form-horizontal" action="<?php echo U()?>" method="POST">

    <?php 

    if(isset($product)) echo '<input type="hidden" name="product_id" value="'.$product['product_id'].'">';

    ?>


    <?php 
        if(!empty($product['product_images'])){
    ?>
    <div class="form-group">

        <label class="col-sm-2 control-label">主图</label>

        <div class="col-sm-10">

            <?php echo '<img src="/images/'.$product['product_images'].'" style="width:180px;height:180px;">'; ?>

        </div>

    </div>
    <?php
        }else{
        	echo '<td>图片不存在: '.'/images/'.$product['product_images'].'</td>';
        }
    ?>
    <div class="form-group">

        <label class="col-sm-2 control-label">产品型号</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="product_model" placeholder="产品型号" value="<?php echo isset($product)?$product['product_model']:''?>">

        </div>

    </div>
    <div class="form-group">

        <label class="col-sm-2 control-label">细节图</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="additional_images" placeholder="细节图" value="<?php echo $product['additional_images'] ?>">
        </div>
    </div>
    <div class="form-group">

        <label class="col-sm-2 control-label">品牌</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="product_brand" placeholder="品牌" value="<?php echo isset($product)?$product['product_brand']:''?>">

        </div>

    </div>    

    <div class="form-group">

        <label class="col-sm-2 control-label">产品图片</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="product_images" placeholder="产品图片" value="<?php echo isset($product)?$product['product_images']:''?>">

        </div>

    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">零售价</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="retail_speciel_price"  placeholder="零售价" value="<?php echo isset($product)?$product['retail_speciel_price']:''?>">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-2 control-label">产品价格</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="product_price"  placeholder="产品价格" value="<?php echo isset($product)?$product['product_price']:''?>">
        </div>
    </div>

    <div class="form-group">

        <label class="col-sm-2 control-label">产品特价</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="speciel_price"  placeholder="产品特价" value="<?php echo isset($product)?$product['speciel_price']:''?>">

        </div>

    </div>   

    <div class="form-group">

        <label class="col-sm-2 control-label">推荐分类</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="featured_category"  placeholder="推荐分类" value="<?php echo isset($product)?$product['featured_category']:''?>">

            <p class="help-block">分类格式:分类1===分类2===分类3|||分类A===分类B</p>

        </div>        

    </div>     


    <div class="form-group">

        <label class="col-sm-2 control-label">上架日期</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="date_added"  placeholder="上架日期" value="<?php echo $product['date_added']?> " >

        </div>

    </div> 

     <div class="form-group">

        <label class="col-sm-2 control-label">上传日期</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="upload_added"  placeholder="上传日期" value="<?php echo $product['upload_added']?>">

        </div>

    </div> 

     <div class="form-group">

        <label class="col-sm-2 control-label">采集SKU/model</label>

        <div class="col-sm-10">

            <input type="text" class="form-control" name="get_model"  placeholder="采集SKU/model" value="<?php echo $product['get_model']?>">

        </div>

    </div>      

<?php

if($product['detail']){

    foreach($product['detail'] as $entry){

?>

    <div class="product_lang_group">

        <fieldset>

            <legend>

                <label class="col-sm-2 control-label"><i class="glyphicon glyphicon-remove" onclick="remove_product_lang(this)"></i>语言</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="language_code[]"  placeholder="语言" value="<?php echo $entry['language_code']?>">

                </div>

            </legend>

            <div class="form-group">

                <label class="col-sm-2 control-label">产品名称</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="product_name[]"  placeholder="产品名称" value="<?php echo $entry['product_name']?>">

                </div>

            </div>    

            <div class="form-group">

                <label class="col-sm-2 control-label">产品描述</label>

                <div class="col-sm-10">

                    <textarea class="form-control" name="product_description[]"><?php echo $entry['product_description']?></textarea>

                </div>

            </div>  

            <div class="form-group">

                <label class="col-sm-2 control-label">产品选项</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="product_attribute[]"  placeholder="产品选项" value="<?php echo $entry['product_attribute']?>">

                    <p class="help-block">选项格式:选项名1==选项类型:选项值1.1,选项值1.2|||选项名2==选项类型:选项值2.1,选项值2.2</p>

                </div>

            </div>    

            <div class="form-group">
                <label class="col-sm-2 control-label">产品属性</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="attribute[]"  placeholder="产品属性" value="<?php echo $entry['attribute']?>">
                    <p class="help-block">属性格式:属性名1#属性值1;属性名2#属性值2</p>
                </div>
            </div>


              

        </fieldset>

    </div>    

<?php    

    }

}else{

?>    

    <div class="product_lang_group">

        <fieldset>

            <legend>

                <label class="col-sm-2 control-label"><i class="glyphicon glyphicon-remove" onclick="remove_product_lang(this)"></i>语言</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="language_code[]"  placeholder="语言" value="en">

                </div>

            </legend>

            <div class="form-group">

                <label class="col-sm-2 control-label">产品名称</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="product_name[]"  placeholder="产品名称">

                </div>

            </div>    

            <div class="form-group">

                <label class="col-sm-2 control-label">产品描述</label>

                <div class="col-sm-10">

                    <textarea class="form-control" name="product_description[]"></textarea>

                </div>

            </div>  

            <div class="form-group">

                <label class="col-sm-2 control-label">产品选项</label>

                <div class="col-sm-10">

                    <input type="text" class="form-control" name="product_attribute[]"  placeholder="产品选项">

                </div>

            </div>            

            <div class="form-group">
                <label class="col-sm-2 control-label">产品属性</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="attribute[]"  placeholder="产品描述">
                </div>
            </div>

        </fieldset>

    </div>    

<?php    

}

?>

    <div class="form-group">

        <div class="col-sm-offset-2 col-sm-10">

            <button type="button" class="btn btn-default" id="btn-add-lang">添加语种</button>

            <button type="submit" class="btn btn-default">提交</button>

            <a class="btn btn-default pull-right" href="<?php echo U('Product/Database/list')?>">返回</a>

        </div>

    </div>

</form>

<script>

$(document).ready(function(){
    $("input[name='date_added']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
    $("input[name='upload_added']").datetimepicker({format: 'yyyy-mm-dd', 'autoclose': true, 'startView': 2, 'minView': 'month', 'language': 'zh-CN'});
});

$('#btn-add-lang').click(function(){

    var html = $('.product_lang_group:first').html();

    var e = $('<div class="product_lang_group"></div>').html(html);

    

    $('.product_lang_group:last').after(e);

});



function remove_product_lang(o){

    var e = $(o).parent().parent().parent();

    if($('.product_lang_group').size()==1)

        alert('至少要保留一种语言!');

    else

        e.remove();

}

</script>