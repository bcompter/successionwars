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

<div class="center">
<h1>Submit a New Bug or Feature</h1>
<br /><br />

<?php 

    $errors = validation_errors();
    if ($errors != '')
        echo '<div class="error">'.$errors.'</div>';
?>

<?php echo form_open("bugtracker/create");?>
    	
  <p>
    <label for="title">Title:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'title', 'value'=>set_value('title'));
        echo form_input($data);
    ?>
  </p>  

  <p>
    <label for="description">Description:</label><br />
    <textarea name="description" rows="15">
    </textarea>
  </p>    
  
  <p>
      <label for="">Bug or Feature?</label>

      <br />
      Go ahead and check this box if the issue you are describing is a real bug.
      Just to be sure, a bug is "Oh crap this doesn't work!".  A feature is a
      nice to have or wishlist item.
      <br /><br />
      <?php 
        $data = array('size'=>'40', 'name'=>'is_bug', 'value'=>'1');
        echo form_checkbox($data);
      ?>
      
  </p>

  <p><?php echo form_submit('submit', 'Create Tracker');?></p>

      
<?php echo form_close();?>
</div>