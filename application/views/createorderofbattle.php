<h1>Succession Wars | Create an Order of Battle</h1>
<br /><br />
<?php echo form_open("orderofbattle/create");?>
    	
  <p>
    <label for="title">Name:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'name');
        echo form_input($data);
    ?>
  </p>  

  <p>
    <label for="description">Game Description:</label><br />
    <?php 
        $data = array('maxlength'=>'200', 'size'=>'20', 'name'=>'description');
        echo form_textarea($data);
    ?>
  </p>

  <p><?php echo form_submit('submit', 'Create');?></p>

      
<?php echo form_close();?>