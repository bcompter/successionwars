<h1>Succession Wars | Create Game</h1>
<br /><br />
<?php echo form_open("game/create");?>
    	
  <p>
    <label for="title">Game Title:</label>
    <?php 
        $data = array('size'=>'40', 'name'=>'title');
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
  
  <p>
      <label for="orderofbattle">Order of Battle:</label><br />
      <select name="orderofbattle">
          <?php 
          foreach($oobs as $oob)
          {
              if (!$oob->draft)
              echo '<option value="'.$oob->orderofbattle_id.'">'.$oob->name.'</option>';
          }
          ?>
      </select>
  </p>

  <p>
    <label for="password">Optional Password:</label>
    <?php echo form_input($password);?>
  </p>     

  <p><?php echo form_submit('submit', 'Create Game');?></p>

      
<?php echo form_close();?>