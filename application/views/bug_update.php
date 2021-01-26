<h1>Update Bug Status</h1>
<br /><br />
<?php echo form_open("bugtracker/update_status/".$bug->bug_id);?>
    	
    <p>
        Current Status: <?php echo $bug->status; ?>
    </p>

  <p>
    <label for="status">New Status:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'status');
        echo form_input($data);
    ?>
  </p>  

  <p><?php echo form_submit('submit', 'Update Bug');?></p>

<?php echo form_close();?>