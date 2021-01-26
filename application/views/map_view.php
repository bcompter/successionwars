<script type="text/javascript">                                         
// Document load
$(document).ready(function() 
{
    // Map functionality
    $(function(){$("#map").draggable();});
    
});
</script>

<h1>Viewing Map: <?php echo $world->name; ?></h1>

<h3>Designer: <?php echo $world->username; ?></h3>

<?php if ($can_edit): ?>
    <h4>
        <?php echo anchor('map/edit/'.$world->world_id, ' EDIT '); ?>  |
        <?php echo anchor('map/copy_select/'.$world->world_id, ' Copy Existing ') ; ?> |
        <?php echo anchor('map/CreateJumpsForWorld/'.$world->world_id, ' Auto-Add Adjacent Jump Links '); ?>
    </h4>
<?php endif; ?>

<h4>
<?php if ($world->is_draft && $is_admin): ?>
    <?php echo anchor('map/update_status/'.$world->world_id.'/released', ' Set Status to Released '); ?>
<?php elseif (!$world->is_draft && $is_admin): ?>
    <?php echo anchor('map/update_status/'.$world->world_id.'/draft', ' Set Status to Draft '); ?>
<?php endif; ?>
</h4>

<br />

<div class="box1 fluid">
    <div id="mapcontainer">
        <div id="map">
            <?php foreach ($maps as $map): ?>
            <?php
                $name = $map->name;
                $name = str_replace(" ", "", $name);
                $name = str_replace(".", "", $name);
            ?>
            <div id="<?php echo $name; ?>" 
                 class="territory"
                 url="<?php echo $this->config->item('base_url').'index.php/map/view_territory/'.$map->map_id; ?>"
                 style="background-color:white; 
                        height:<?php echo $map->height; ?>px; 
                        width:<?php echo $map->width; ?>px; 
                        position:absolute; 
                        border:1px solid; 
                        top:<?php echo $map->top; ?>px; 
                        left:<?php echo $map->left; ?>px; 
                        color:black; 
                        cursor:crosshair;">
                <p>
                    <?php echo $map->name; ?>
                    <br />
                    <?php echo $map->default_resource; ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>