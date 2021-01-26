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

<h1>Succession Wars Forums</h1>
<h2><?php echo $topic->title; ?></h2>
<h3>Add a Reply</h3>

<?php echo form_open("forums/create_post/".$topic->topic_id); ?>
    <p>     
        <textarea name="content" rows="15">
        </textarea>

        <input type="submit" value="Submit" />
    </p>
<?php echo form_close(); ?>
