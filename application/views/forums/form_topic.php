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
<h2><?php echo $section->title; ?></h2>
<h3>Add a New Topic</h3>

<?php echo form_open("forums/create_topic/".$section->section_id); ?>
    <p>
        <input type="text" name="title" size="75" />
    </p>
    <p>     
        <textarea name="content" rows="15">
        </textarea>

        <input type="submit" value="Submit" />
    </p>
<?php echo form_close(); ?>
