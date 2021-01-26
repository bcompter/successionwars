<h1><?php echo $map->name; ?></h1>
<h3>Resources: <?php echo $map->default_resource; ?></h3>
<h3>Type: 
    <?php 
        if ($map->default_is_regional)
            echo 'Regional Capital';
        else if ($map->default_is_capital)
            echo 'Capital';
        else
            echo 'Normal';
    ?>
</h3>
<h4>
    <?php echo anchor('map/edit_territory/'.$map->map_id, ' EDIT ', 'class="menu"'); ?>
    |
    <?php echo anchor('map/delete_territory/'.$map->map_id, ' DELETE ', 'class="delete_region" tid="'.$map->name.'"'); ?>
</h4>

<h3>Location</h3>
<table>
    <tr>
        <td colspan="2" align="center">
            <a href="<?php echo $this->config->item('base_url').'index.php/map/edit_position/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="up" tid="<?php echo $map->name; ?>">UP</a>
        </td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_position/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="left" tid="<?php echo $map->name; ?>">LEFT</a></td>
        <td><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_position/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="right" tid="<?php echo $map->name; ?>">RIGHT</a></td>
    </tr>
    <tr>
        <td colspan="2" align="center"><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_position/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="down" tid="<?php echo $map->name; ?>">DOWN</a></td>
    </tr>
</table>

<h3>Size</h3>
<table>
    <tr>
        <td colspan="2" align="center"><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_size/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="plus_h" tid="<?php echo $map->name; ?>">PLUS</a></td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_size/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="minus_w" tid="<?php echo $map->name; ?>">MINUS</a></td>
        <td><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_size/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="plus_w" tid="<?php echo $map->name; ?>">PLUS</a></td>
    </tr>
    <tr>
        <td colspan="2" align="center"><a href="<?php echo $this->config->item('base_url').'index.php/map/edit_size/'.$map->map_id.'/' ?>" class="stylebutton edit_location" name="minus_h" tid="<?php echo $map->name; ?>">MINUS</a></td>
    </tr>
</table>