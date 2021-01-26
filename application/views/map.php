<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
        
        // TODO add a map re-center button
        // for now, here is a really large transparent packdrop to drag around
        echo '<div class="starGrab" style="background-color: none; height: 2000px; width: 2000px; position: absolute; top: -1000px; left: -1000px; "> </div>';
        
        $vx = 200.0; // Virtual x origin
        $vy = 200.0; // Virtual y origin
        $index = 0;  
        
        foreach($map as $territory)
        {
            if (!isset($strength[$index]->sum_strength))
                $strength[$index]->sum_strength = 0;
            if (!isset($capacity[$index]->sum_capacity))
                $capacity[$index]->sum_capacity = 0;
            
            $id = $territory->name;
            $id = str_replace('.', '', $id);
            $id = str_replace(' ', '', $id);
            if (strlen($territory->name) > 4 && $territory->width < 100)
                $territory->name = substr($territory->name, 0, 4).'.';
            
            // Check for unique periphery background colors
            if ($territory->is_periphery)
            {
                $bgcolor = 'grey';
                $textcolor = 'black';
            }
            else
            {
                $bgcolor = $territory->color;
                $textcolor = $territory->text_color;
            }
            
            // TODO Regions should have a flag for whether they need a background inserted.
            if ($id == "Sakhara" && $territory->world_id == 1)
                echo '<div class="blackBG" style="background-color: black; height: 50px; width: 50px; position: absolute; top: 50px; left: 300px; "></div>';

            echo '<div id="'.$id.'" class="territory" url="'.$this->config->item('base_url').'index.php/sw/location/'.$territory->territory_id.'"style="box-shadow: inset 0 0 15px '.$textcolor.';background-color:'.$bgcolor.';height:'.($territory->height-1).'px;width:'.($territory->width-1).'px;position:absolute;border:1px solid;top:'.$territory->top.'px;left:'.$territory->left.'px; border-color:Black; color:'.$textcolor.'; cursor:crosshair;">';
      
            echo '<p>';
            echo ( $territory->is_capital ? '<img src="'.base_url().'images/capital_12.png"> ' : '' );
            echo ( $territory->is_regional ? '<img src="'.base_url().'images/regional_10.png"> ' : '' );
            if (isset($territory->factory_id))
                echo ( $territory->is_damaged == 0 ? '<img src="'.base_url().'images/factory_12.png">' : '<img src="'.base_url().'images/factory_damaged_12.png">' );
            if ($territory->num_leaders > 0)
                echo ' <span class="bolder">L</span>';//.$territory->num_leaders;
            echo '<br />';
            
            echo '<span class="bolder">'.$territory->name.' '.$territory->resource.'</span>'.
                    ($strength[$index]->sum_strength > 0 ? '<br />M:'.$territory->num_units.'/'.$strength[$index]->sum_strength.'' : '').
                    ($capacity[$index]->sum_capacity > 0 ? '<br />J:'.$territory->num_jumpships.'/'.$capacity[$index]->sum_capacity.'' : '').
                    '</div>';
            $index++;
        }  // end foreach maps
        
        // And now ellis' arrows...
        echo $arrows; 
        
        echo '</info>';
    echo "</response>";
?>