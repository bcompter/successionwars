<script type="text/javascript" <?php echo 'src="'.base_url().'tinymce/js/tinymce/tinymce.min.js"'; ?>></script>

<script type="text/javascript">
tinymce.init({
        selector: 'textarea',
        width: "80%",
        theme: "modern",
        menubar:false,
        statusbar: false
});
</script>

<div id="contentwrapper">
<div id="contentcolumn">
    <?php echo form_open("blog/save_post/".$post->post_id); ?>
    <p>
        <input type="text" name="title" value=<?php echo '"'.$post->title.'"'; ?>/>
    </p>
    <p>     
        <textarea name="content" cols="50" rows="15">
            <?php echo $post->text; ?>
        </textarea>

        <input type="submit" value="Save" />
    </p>
    
    <?php echo form_close(); ?>
</div>
</div>

<?php $this->load->view('blog_sidebars'); ?>