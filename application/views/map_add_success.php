<?php echo "<?xml version=\"1.0\"?>\n"; ?>
<response>
    <info>
        <![CDATA[
            <div class="notice">New region added successfully!</div>
        ]]>
    </info>
    <map>
        <![CDATA[
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
        ]]>
    </map>
</response>
    

