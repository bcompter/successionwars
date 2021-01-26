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

<h2>Edit Help Request for your Game <?php echo $game->title; ?></h2>
<h3>Edit Description</h3>

<?php echo form_open("game/edit_help_description/".$game_help->help_id); ?>
    <p>     
        <textarea name="description" rows="15">
        </textarea>

        <input type="submit" value="Submit" />
    </p>
<?php echo form_close(); ?>
