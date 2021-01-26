<h1>Update Dashboard Message</h1>

<?php echo anchor('admin', '<<< Back to the Admin page...'); ?>

<br /><br />
<?php echo form_open("admin/dashboard_message/");?>



    <p>
        Current Message: <?php echo $admin->dashboard_message; ?>
    </p>

  <p>
    <label for="status">New Message:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'message', 'value'=>$admin->dashboard_message);
        echo form_input($data);
    ?>
  </p>  

  <p><?php echo form_submit('submit', 'Update Message');?></p>

<?php echo form_close();?>