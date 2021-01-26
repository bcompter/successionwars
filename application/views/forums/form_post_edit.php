<script type="text/javascript" <?php echo 'src="'.base_url().'tinymce/js/tinymce/tinymce.min.js"'; ?>></script>

<script type="text/javascript"> var post_text = <?php echo json_encode($post->text); ?>;</script>

<script type="text/javascript">
    $( document ).ready(function() {
        $('#post_edit').html(post_text);
    });
   
    tinymce.init({
        selector: 'textarea',
        width: "80%",
        theme: "modern",
        menubar:false,
        statusbar: false
});
</script>

<h1>Succession Wars Forums</h1>
<h2><?php echo $topic->title; ?></h2>
<h3>Edit a Post</h3>

<?php echo form_open("forums/edit_post/".$post->post_id); ?>
    <p>     
        <textarea name="content" rows="15" id="post_edit">
        </textarea>

        <input type="submit" value="Update" />
    </p>
<?php echo form_close(); ?>
