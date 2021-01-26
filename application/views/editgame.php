<h1>Succession Wars | Edit Game Settings</h1>
<br /><br />
<?php echo form_open("game/edit/".$game->game_id);?>
    	
  <p>
    <label for="title">Game Title:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'title', 'value'=>$game->title);
        echo form_input($data);
    ?>
  </p>  

  <p>
    <label for="description">Game Description:</label><br />
    <?php 
        $data = array('maxlength'=>'200', 'size'=>'20', 'name'=>'description', 'value'=>$game->description);
        echo form_textarea($data);
    ?>
  </p>

  <p>
    <label for="password">Optional Password:</label>
    <?php
        $data = array('maxlength'=>'20', 'size'=>'20', 'name'=>'password', 'value'=>$game->password);
        echo form_input($data);
    ?>
  </p>      

  <p><?php echo form_submit('submit', 'Save Settings');?></p>
      
<?php echo form_close();?>