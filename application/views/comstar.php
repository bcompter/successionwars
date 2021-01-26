<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            
        echo '<h3>Comstar</h3>'; 
        
        echo '<p>';
        echo 'Comstar can be bribed to interdict enemy communications within a territory.  Interdicted units suffer -2 to all combat rolls.  ';
        echo 'Each bribe attempt costs 5 MM CBills.  Be careful as Comstar may choose to interdict your forces instead!';
        echo '</p>';
        
        echo '<span style="border-bottom: dashed thin;" title="60% chance Comstars will interdict your intended target.  30% chance they will interdict you instead.  10% chance they will interdict you both.">Bribe ComStar to interdict</span> <select id="option">';
        echo '<option value="'.$this->config->item('base_url').'index.php/comstar/bribe/'.$player->player_id.'/0">- Player -</option>';
        foreach($players as $play)
        {
            if ( $player->player_id != $play->player_id )
                echo '<option value="'.$this->config->item('base_url').'index.php/comstar/bribe/'.$player->player_id.'/'.$play->player_id.'">'.$play->faction.'</option>';
        }
        echo '</select><br /><br />';
        
        echo ' in <select id="option2">';
        echo '<option value="0">-Region-</option>';
        foreach($territories as $territory)
        {
            echo '<option value="/'.$territory->territory_id.'">'.$territory->name.'</option>';
        }
        echo '</select><br /><br />';
        
        echo anchor('#','BRIBE' ,'class="doubleoption"');
        
        echo '</info>';
    echo "</response>";
?>