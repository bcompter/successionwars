<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h3>Star League Jumpship: Select a Manufacturing Center to Build</h3>';
            
            if ( count($factories) == 0 )
            {
                echo 'No available centers, that\'s really odd...';
            }
            else
            {
                echo '<select id="option">';
                foreach( $factories as $factory )
                {
                    if (!$factory->is_damaged)
                        echo '<option value="'.$this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$factory->factory_id.'">'.$factory->name.'</option>';     
                }
                echo '</select>';
                echo '<br /><br />';
                echo anchor('#','BUILD' ,'class="dc"');
                
            }
        echo '</info>';
    echo "</response>";
?>