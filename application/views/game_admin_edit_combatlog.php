<h1>Succession Wars | Edit Combat Log</h1>
<br /><br />
<?php echo form_open("game/edit_combat_log/".$log->combatlog_id);?>
    	
  <p>
    <label for="casualties_owed">Casualties Owed:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'casualties_owed');
        echo form_input($data);
    ?>
  </p>

  <p><?php echo form_submit('submit', 'Edit');?></p>

      
<?php echo form_close();?>