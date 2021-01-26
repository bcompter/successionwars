<?php echo "<?xml version=\"1.0\"?>\n"; ?>
<response>
    <info>
        <![CDATA[
            <div class="notice">Region updated successfully!</div>
        ]]>
    </info>
    <map>
        <![CDATA[
        <div id="<?php echo $map->name; ?>" 
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
    

