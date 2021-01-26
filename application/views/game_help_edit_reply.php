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

<h2>Edit Help Reply for the Game <?php echo $game->title; ?></h2>
<h3>Edit Reply</h3>

<?php echo form_open("game/edit_help_reply/".$game_help->help_id); ?>
    <p>     
        <textarea name="reply" rows="15">
        </textarea>

        <input type="submit" value="Submit" />
    </p>
<?php echo form_close(); ?>