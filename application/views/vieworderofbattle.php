<a name="top"></a>
<h2><?php echo $orderofbattle->name; ?></h2>

<p>
    <?php echo $orderofbattle->description; ?>
</p>
<p>
    Created By: <?php echo $creator->username; ?>
</p>
<p>
    Status: <?php echo ($orderofbattle->draft ? 'Draft' : 'Released'); ?>
    
    <?php if (isset($user->id) && ($user->group == 'admin')) : ?>
    
        | <?php echo ($orderofbattle->draft ? 
                anchor('orderofbattle/set_to_released/'.$orderofbattle->orderofbattle_id, 'SET TO RELEASED') : 
                anchor('orderofbattle/set_to_draft/'.$orderofbattle->orderofbattle_id, 'SET TO DRAFT')); ?>
    
    <?php endif; ?>
    
</p>

<p>
    <?php echo anchor('orderofbattle/edit_oob/'.$orderofbattle->orderofbattle_id, 'Edit Configuration'); ?>
    <hr>
    <a href="#factions">Go to Factions</a><br>
    <a href="#cards">Go to Cards</a><br>
    <a href="#territories">Go to Territories</a><br>
    <a href="#combatunits">Go to Combat Units</a><br>
    <a href="#leaders">Go to Leaders</a><br>
    <a href="#factories">Go to Factories</a><br>
    <a href="#jumpships">Go to Jumpships</a>
</p>
<hr>
<a name="factions"></a>
<h3>Factions</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/0', 'ADD FACTION'); ?></p>
<table>
    <tr>
        <th>Faction</th><th>Turn Order</th><th>Money</th><th>Tech Level</th><th>Color</th><th>Text Color</th><th>Free Bribes</th><th>Setup Order</th><th>Elementals</th><th></th>      
    </tr>
    <?php
    foreach($data as $d)
    {
        if ($d->type == 0)
        {
            echo '<tr rowspacing="10px">';
            echo '<td>'.$d->arg0data.'</td>';
            echo '<td align="center">'.$d->arg1data.'</td>';
            echo '<td align="center">'.$d->arg2data.'</td>';
            echo '<td align="center">'.$d->arg3data.'</td>';
            echo '<td align="center">'.$d->arg4data.'</td>';
            echo '<td align="center">'.$d->arg5data.'</td>';
            echo '<td align="center">'.$d->arg6data.'</td>';
            echo '<td align="center">'.$d->arg7data.'</td>';
            echo '<td align="center">'.$d->arg8data.'</td>';
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $creator->id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/0', 'ADD FACTION'); ?></p>
<a href="#top">Go to Top</a>
<hr>
<a name="cards"></a>
<h3>Cards</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/1', 'ADD CARD'); ?></p>
<table>
    <tr>
        <th>Card Type</th><th>Card Description</th><th>Phase</th>   <th></th>  
    </tr>
    <?php
    
    foreach($cards as $c)
    {
        $cards[$c->type_id] = $c;
    }
    
    foreach($data as $d)
    {
        if ($d->type == 1)
        {
            echo '<tr>';
            echo '<td>'.$cards[$d->arg0data]->title.'</td>';
            echo '<td>'.$cards[$d->arg0data]->text.'</td>';
            echo '<td align="center">'.$cards[$d->arg0data]->phase.'</td>';
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/1', 'ADD CARD'); ?></p>
<a href="#top">Go to Top</a>
<hr>
<a name="territories"></a>
<h3>Territories</h3>
<table>
    <tr>
        <th>Name</th><th>Controlling Faction</th><th>Is Periphery?</th><th>Garrison Name</th> <th>Resource</th><th>Regional Capital?</th><th>House Capital?</th> <th>&nbsp</th> 
    </tr>
    <?php
    
    foreach($maps as $m)
    {
        $map[$m->map_id] = $m->name;
    }
    
    $num_maps = 0;
    foreach($data as $d)
    {
        if ($d->type == 2)
        {
            $num_maps++;
            echo '<tr>';
            echo '<td>'.$map[$d->arg1data].'</td>';
            echo '<td align="center">'.$d->arg0data.'</td>';
            
            echo '<td align="center">'.($d->arg2data ? 'Yes' : 'No').'</td>';
            echo '<td align="center">'.$d->arg3data.'</td>';
            
            echo '<td align="center">'.$d->arg4data.'</td>';
            echo '<td align="center">'.($d->arg5data ? 'Yes' : 'No').'</td>';
            echo '<td align="center">'.($d->arg6data ? 'Yes' : 'No').'</td>';
            
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p>
    <?php 
        if ($num_maps == 0)
        {
            foreach ($worlds as $w)
            {
                echo ' | '.anchor('orderofbattle/generate_map/'.$orderofbattle->orderofbattle_id.'/'.$w->world_id, 'Generate Map, '.$w->name);
            }
            echo ' | ';
        }
    ?>
</p>
<a href="#top">Go to Top</a>
<hr>
<a name="combatunits"></a>
<h3>Combat Units</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/3', 'ADD COMBAT UNIT'); ?></p>
<table>
    <tr>
        <th>Name</th><th>Controlling Faction</th><th>PreWar Strength</th> <th>Location</th> <th>Mercenary</th> <th>Can Rebuild?</th> <th>Conventional?</th> <th>Elemental?</th><th></th> 
    </tr>
    <?php
    
    foreach($maps as $m)
    {
        $map[$m->map_id] = $m->name;
    }
    
    foreach($data as $d)
    {
        if ($d->type == 3)
        {
            echo '<tr>';
            echo '<td>'.$d->arg0data.'</td>';
            echo '<td align="center">'.$d->arg1data.'</td>';
            echo '<td align="center">'.$d->arg2data.'</td>';
            if ($d->arg3data == 'None')
                echo '<td align="center">Not Initially Available</td>';
            else if ($d->arg3data == 'Free')
                echo '<td align="center">Free Set-up</td>';   
            else
                echo '<td align="center">'.$map[$d->arg3data].'</td>';
            echo '<td align="center">'.( $d->arg4data == '1' ? 'Yes' : 'No' ).'</td>';
            
            echo '<td align="center">'.($d->arg5data == 1 ? 'Yes' : 'No').'</td>';
            
            echo '<td align="center">'.($d->arg6column == 'is_conventional' ? 'Yes' : 'No').'</td>';
            echo '<td align="center">'.($d->arg6column == 'is_elemental' ? 'Yes' : 'No').'</td>';
            
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
            {
                echo '<td align="center">'
                    .anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '
                    .anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' ';
                if (!isset($d->arg6column))
                    echo anchor('orderofbattle/edit/'.$d->data_id, 'EDIT');
                echo '</td>';
                
            }
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/3', 'ADD COMBAT UNIT'); ?></p>
<a href="#top">Go to Top</a>
<hr>
<a name="leaders"></a>
<h3>Leaders</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/4', 'ADD LEADER'); ?></p>
<table>
    <tr>
        <th>Name</th><th>Controlling Faction</th><th>Location</th> <th>Military</th> <th>Combat</th> <th>Admin</th> <th>Loyalty</th> <th>Associated Units (mercenary)</th> <th></th> 
    </tr>
    <?php
    
    foreach($maps as $m)
    {
        $map[$m->map_id] = $m->name;
    }
    foreach($mercs as $merc)
    {
        $merclist[$merc->arg0data] = $merc->arg0data;
    }
    
    foreach($data as $d)
    {
        if ($d->type == 4)
        {
            echo '<tr>';
            echo '<td>'.$d->arg0data.'</td>';
            
            echo '<td align="center">'.(isset($d->arg1data) ? $d->arg1data : 'Neutral').'</td>';
            
            if ($d->arg2data == 'None')
                echo '<td align="center">Not Initially Available</td>';
            else if ($d->arg2data == 'Free')
                echo '<td align="center">Free Set-up</td>';   
            else
                echo '<td align="center">'.$map[$d->arg2data].'</td>';
            echo '<td align="center">'.$d->arg3data.'</td>';
            echo '<td align="center">'.$d->arg4data.'</td>';
            echo '<td align="center">'.$d->arg5data.'</td>';
            echo '<td align="center">'.$d->arg6data.'</td>';
            if ( !isset($d->arg7data) || $d->arg7data === 0 || $d->arg7data == '' )
                echo '<td>None</td>';
            else
                echo '<td>'.(isset($merclist[$d->arg7data]) ? $merclist[$d->arg7data] : 'ERROR').'</td>';
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/4', 'ADD LEADER'); ?></p>
<a href="#top">Go to Top</a>
<hr>
<a name="factories"></a>
<h3>Factories</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/5', 'ADD FACTORY'); ?></p>
<table>
    <tr>
        <th>Location</th><th></th> 
    </tr>
    <?php
    foreach($data as $d)
    {
        if ($d->type == 5)
        {
            echo '<tr>';
            echo '<td>'.$map[$d->arg0data].'</td>';
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/5', 'ADD FACTORY'); ?></p>
<a href="#top">Go to Top</a>
<hr>
<a name="jumpships"></a>
<h3>Jumpships</h3>
<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/6', 'ADD JUMPSHIP'); ?></p>
<table>
    <tr>
        <th>Controlling Faction</th><th>Location</th><th>Capacity</th> <th></th> 
    </tr>
    <?php
    
    foreach($maps as $m)
    {
        $map[$m->map_id] = $m->name;
    }
    
    foreach($data as $d)
    {
        if ($d->type == 6)
        {
            echo '<tr>';
            echo '<td>'.$d->arg0data.'</td>';
            if ($d->arg1data == null)
                echo '<td align="center">Free Set-up</td>';   
            else if (isset($map[$d->arg1data]))
                echo '<td align="center">'.$map[$d->arg1data].'</td>';
            echo '<td align="center">'.$d->arg2data.'</td>';
            if (isset($user->id) && ($user->group == 'admin' || $user->id == $orderofbattle->user_id))
                echo '<td>'.anchor('orderofbattle/delete/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'DELETE').' '.anchor('orderofbattle/copy/'.$orderofbattle->orderofbattle_id.'/'.$d->data_id, 'COPY').' '.anchor('orderofbattle/edit/'.$d->data_id, 'EDIT').'</td>';
            echo '</tr>';
        }
    }
    ?>
</table>

<p><?php echo anchor('orderofbattle/add/'.$orderofbattle->orderofbattle_id.'/6', 'ADD JUMPSHIP'); ?></p>
<a href="#top">Go to Top</a>