<?php
    $nomcs;
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>Manufacturing Centers</h3>';        
        echo '<ul>';
        if ( count($factories) < 1 )
            echo '<li>You do not control any manufacturing centers.</li>';
        else
        {
            foreach($factories as $factory)
            {
                $id = $factory->name;
                $id = str_replace('.', '', $id);
                $id = str_replace(' ', '', $id);
                
                echo '<li>';
                echo anchor('production/view/'.$factory->factory_id,($factory->name.($factory->is_damaged ? ' (Damaged)' : '')),'class="menu hoverlink" hoverid="'.'#'.$id.'"');
                echo ($factory->being_built ? ' [Under Construction]' : '');
                echo '</li>';

                // Setup an array to catch factories later
                $nomcs[$factory->location_id] = true;
            }
        }
            
        echo '</ul>';
        
        echo 'Build a New Manufacturing Center (40M CBills)';
        echo '<select id="territory">';
        foreach($territories as $territory)
        {
            if ( !isset( $nomcs[$territory->territory_id] ) )
            {
                echo '<option value="'.$this->config->item('base_url').'index.php/production/mc/'.$territory->territory_id.'">'.$territory->name.'</option>';
            }
        }
        echo '</select> ';
        echo anchor('#','BUILD' ,'class="mc"');
        
        echo '</info>';
    echo "</response>";
?>