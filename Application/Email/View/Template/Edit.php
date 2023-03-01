<h1>模板编辑</h1>

<form action="<?php echo U('Email/Template/Edit');?>" method="post">
  <?php
    if($action=='edit'){
        echo '<input type="hidden" name="id" value="'.$data['email_template_id'].'">';
    }
  ?>
  <div class="form-group">
    <label for="title">模板标题</label>
    <input type="text" name="title" class="form-control" id="title" placeholder="模板标题" value="<?php echo isset($data['email_template_title'])?$data['email_template_title']:'' ?>">
  </div>
  <div class="form-group">
    <label for="content">模板内容</label>
    <textarea name="content" class="form-control"><?php echo isset($data['email_template_content'])?$data['email_template_content']:'' ?></textarea>
  </div>
  <div class="form-group">
    <label for="status">状态</label>
    <select class="form-control" name="status">
        <option value="1"<?php if(isset($data['email_template_title']) && $data['email_template_title']=='1') echo ' selected' ?>>开启</option>
        <option value="0"<?php if(isset($data['email_template_title']) && $data['email_template_title']=='0') echo ' selected' ?>>关闭</option>
    </select>
  </div>
  <button type="submit" class="btn btn-default">保存</button>
</form>

<script src="__PUBLIC__/Js/tinymce/tinymce.min.js" type="text/javascript"></script>
<script type="text/javascript" >
    $(document).ready(function(){
        tinymce.init({
            selector: 'textarea',
            height: 500,
            menubar: false,
            plugins: [
              'advlist autolink lists link image charmap print preview anchor',
              'searchreplace visualblocks code fullscreen',
              'insertdatetime media table contextmenu paste code'
            ],
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
            content_css: '//www.tinymce.com/css/codepen.min.css'
          });
    });
</script>