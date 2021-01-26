<h3>Add a Territory</h3>
<?php echo validation_errors(); ?>
<?php echo form_open("map/add_region/".$world->world_id);?>

  <p>
    <h4><label for="name">Name:</label></h4>
    <?php 
        $data = array('size'=>'50', 'name'=>'name');
        echo form_input($data);
    ?>
  </p>
  <p>
    <h4><label for="name">Resources:</label></h4>
    <?php 
        $data = array('size'=>'10', 'name'=>'resource', 'value'=>1);
        echo form_input($data);
    ?>
  </p>
  <p>
    <h4><label for="name">Height:</label></h4>
    <?php 
        $data = array('size'=>'10', 'name'=>'height', 'value'=>100);
        echo form_input($data);
    ?>
  </p>
  <p>
  <h4><label for="name">Width:</label></h4>
    <?php 
        $data = array('size'=>'10', 'name'=>'width', 'value'=>100);
        echo form_input($data);
    ?>
  </p>
  <p>
    <h4><label for="name">Type:</label></h4>
    <input type="radio" name="type" value="normal" checked='checked' class='inline'> Normal<br>
    <input type="radio" name="type" value="regional" class='inline'> Regional Capital<br>
    <input type="radio" name="type" value="capital" class='inline'> Capital<br>
  </p>
  
  <p>
      <input type="submit" name="submit" href="<?php echo $this->config->item('base_url').'index.php/map/add_territory/'.$world->world_id; ?>" value=" Add Territory " class="add_region" />
  </p>

<?php echo form_close();?>