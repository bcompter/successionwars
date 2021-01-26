<h3>Edit Region: <?php echo $map->name; ?></h3>
<?php echo validation_errors(); ?>
<?php echo form_open("map/edit_territory/".$map->map_id);?>

  <p>
    <h4><label for="name">Name:</label></h4>
    <?php 
        $data = array('size'=>'50', 'name'=>'name', 'value'=>$map->name);
        echo form_input($data);
    ?>
  </p>
  <p>
    <h4><label for="name">Resources:</label></h4>
    <?php 
        $data = array('size'=>'10', 'name'=>'resource', 'value'=>$map->default_resource);
        echo form_input($data);
    ?>
  </p>
  <p>
    <h4><label for="name">Type:</label></h4>
    <?php
        $is_normal = false;
        if (!$map->default_is_regional && !$map->default_is_capital)
            $is_normal = true;
    ?>
    
    <input type="radio" name="type" value="normal" <?php echo ($is_normal ? 'checked="checked"' : ''); ?> class='inline'> Normal<br>
    <input type="radio" name="type" value="regional" <?php echo ($map->default_is_regional ? 'checked="checked"' : ''); ?> class='inline'> Regional Capital<br>
    <input type="radio" name="type" value="capital" <?php echo ($map->default_is_capital ? 'checked="checked"' : ''); ?> class='inline'> Capital<br>
  </p>
  
  <p>
      <input type="submit" name="submit" tid="<?php echo $map->name; ?>" href="<?php echo $this->config->item('base_url').'index.php/map/edit_territory/'.$map->map_id; ?>" value=" Edit Territory " class="edit_region" />
  </p>

<?php echo form_close();?>