<script type="text/javascript" <?php echo 'src="'.$this->config->item('base_url').'javascript/map_edit.js"'; ?>></script>
<div class="box1">
    <div id="playerinfo">
        <h3>Map Editor</h3>
        <table>
            
        </table>
    </div>

    <div id="gamestatus">
        <h3><?php echo $world->name; ?></h3>
        <table>
        </table>
    </div>
</div>
    
<ul class="menub">
    <li class="top"> <?php echo anchor('map/add_territory/'.$world->world_id,'<span>Add a Territory</span>', 'class="menu top_link"'); ?></li>
</ul>
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

    <div id="info"></div>
</div>