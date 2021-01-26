<h1>Succession Wars | Create Map</h1>
<br /><br />
<?php echo form_open("map/create");?>
    	
  <p>
    <label for="title">Map Name:</label>
    <?php 
        $data = array('size'=>'50', 'name'=>'name');
        echo form_input($data);
    ?>
  </p>  

  <p><?php echo form_submit('submit', 'Create New Map');?></p>

<?php echo form_close();?>