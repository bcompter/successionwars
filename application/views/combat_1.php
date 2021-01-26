<?php

echo '<h1>Battle for '.$territory->name.'</h1>';
echo anchor('sw/viewcombats/'.$game->game_id, '<< Back to the List of Combats', 'class="menu"');
echo '<br /><br />';

// Organize bonuses
if (count($bonuses) > 0)
{
    foreach($bonuses as $b)
    {
        if (!isset($bonus[$b->player_id][$b->value]))
            $bonus[$b->player_id][$b->value] = $b;
    }
}

// Organize combatlogs
$casualtiesowed;        // list of all owed casualties indexed by player id
$forcesize;             // List of all attacking force sizes by player id
$use_force_size;
foreach($combatlogs as $c)
{
    $casualtiesowed[$c->player_id] = $c->casualties_owed;
    $forcesize[$c->player_id] = $c->force_size;
    $use_force_size[$c->player_id] = $c->use_force_size;
}

// Organize leaders
$sortedleaders;         // list of all leaders, sorted by faction
foreach($leaders as $leader)
{
    $sortedleaders[$leader->controlling_house_id][] = $leader;
}

// List the defenders!
// Find out who that is!
$tech_bonus; // 'Mech & Conventional tech bonus
$tech_bonus_elem; // Elemental Tech bonus
$defender;
foreach($players as $p)
{
    if ($p->player_id == $territory->player_id)
    {
        $defender = $p;
    }    
    if ( $p->tech_level <= -10 )
        $tech_bonus[$p->player_id] = -2;
    else if ( $p->tech_level <= -5 )
        $tech_bonus[$p->player_id] = -1;
    else if ( $p->tech_level < 24 )
        $tech_bonus[$p->player_id] = 0;
    else
        $tech_bonus[$p->player_id] = 2;
    
    //Elemental Tech bonus
    if ( $p->tech_level <= -10 )
        $tech_bonus_elem[$p->player_id] = -2;
    else if ( $p->tech_level <= -5 )
        $tech_bonus_elem[$p->player_id] = -1;
    else if ( $p->tech_level < 21 )
        $tech_bonus_elem[$p->player_id] = 0;
    else
        $tech_bonus_elem[$p->player_id] = 1;
}

// Comstar
if (isset($comstar->player_id))
{
    $defender = $comstar;
    $tech_bonus[0] = 0;
    $tech_bonus_elem[0] = 0;    
}

// Organize attackers
$has_units;

$displayed; // a list of displayed units indexed by combatunit_id
            // this is used to know what units to skip when displaying combined units
echo '<h2>Defender: '.$defender->faction.'</h2>';
echo '<h3>'.(isset($bonus[$defender->player_id][-2]) ? '(Interdict -2)' : '').(isset($bonus[$defender->player_id][2]) ? '(Star League +2)' : '').'</h3>';
if (isset($casualtiesowed[$defender->player_id]) && $casualtiesowed[$defender->player_id] > 0)
    echo 'Casualties Owed: '.$casualtiesowed[$defender->player_id];

echo '<table>';
echo '<tr>';
echo '<th><span style="border-bottom: dashed thin;" title="Mercenary units are marked with a *">Units</span></th><th><span style="border-bottom: dashed thin;" title="Base combat strength (Mouseover for Pre-war strength if greater than 4)">STR</span></th><th><span style="border-bottom: dashed thin;" title="Under Regional or House Interdict">INT</span></th><th><span style="border-bottom: dashed thin;" title="Star League bonus">SL</span></th><th><span style="border-bottom: dashed thin;" title="Leader bonus">L</span></th><th><span style="border-bottom: dashed thin;" title="Technology bonus">T</span></th><th><span style="border-bottom: dashed thin;" title="Expected combat strength for NEXT barrage">Final</span></th><th><span style="border-bottom: dashed thin;" title="Roll from PREVIOUS barrage">Roll</span></th><th></th>';
echo '</tr>';
foreach($units as $unit)
{
    if (!isset($has_units[$unit->owner_id]))
        $has_units[$unit->owner_id] = true;
    
    if ($unit->owner_id == $defender->player_id && !isset($displayed[$unit->combatunit_id]))
    {
        echo '<tr>';   
            $starleague = (isset($bonus[$defender->player_id][2]) ? 2 : 0);
            $interdict = (isset($bonus[$defender->player_id][-2]) || $defender->house_interdict ? -2 : 0);
            $leaderbonus = $this->combatbonusmodel->get_unit_bonus($unit->combatunit_id)->value;
            
            // Find combos...
            $combo = 0;
            if (isset($unit->combine_with))
            {
                foreach($units as $combined)
                {
                    if ($combined->combine_with == $unit->combatunit_id)
                    {
                        $combo = $combined->strength;
                        $comboleaderbonus = $this->combatbonusmodel->get_unit_bonus($combined->combatunit_id)->value;
                        break;
                    }
                }
            }
            
            $final = $unit->strength + $combo + (isset($comboleaderbonus) ? $comboleaderbonus : 0) + $starleague + $interdict + $leaderbonus
                + ($unit->is_conventional && $tech_bonus[$unit->owner_id] < 0 ? $tech_bonus[$unit->owner_id] : 0)
                + ($unit->is_elemental ? $tech_bonus_elem[$unit->owner_id] : 0)
                + (!$unit->is_elemental && !$unit->is_conventional ? $tech_bonus[$unit->owner_id] : 0);
            
            
            echo '<td>'.($unit->is_merc?'*':'').$unit->name.'</td>';
            echo '<td align="center">'.((($unit->prewar_strength>4)&&($unit->strength==4))?'<span style="border-bottom: dashed thin;" title="('.$unit->prewar_strength.')">'.$unit->strength.'</span>':$unit->strength).'</td>';
            echo '<td align="center">'.(($interdict == -2) ? '-2' : '0').'</td>';
            echo '<td align="center">'.(($starleague == 2) ? '+2' : '0').'</td>';
            echo '<td align="center">'.(isset($leaderbonus) ? $leaderbonus : '0' ).'</td>';
            echo '<td align="center">';
                if ($unit->is_conventional && $tech_bonus[$unit->owner_id] > 0) // if conventional, they don't get the positive bonus for 'Mechs
                    echo '0';
                else if ($unit->is_elemental)
                    echo $tech_bonus_elem[$unit->owner_id];
                else echo $tech_bonus[$unit->owner_id];
                echo '</td>';
            echo '<td align="center">'.$final.'</td>';
            echo '<td align="center">'.($unit->last_roll == 0 ? '' : $unit->last_roll ).'</td>';
            
            echo '<td align="center">';
            if ( $unit->die != true && isset($casualtiesowed[$defender->player_id]) && $casualtiesowed[$defender->player_id] > 0 && $player->player_id == $defender->player_id)
            {
                echo anchor('sw/kill/'.$unit->combatunit_id,'KILL', 'class="menu"');
            }
            else if ($unit->die)
            {
                echo 'KIA';
            }
            echo '</td>'; 
        echo '</tr>';
        
        // Search for a combo if found...
        if (isset($unit->combine_with))
        {
            foreach($units as $combined)
            {
                if ($combined->combine_with == $unit->combatunit_id)
                {
                    echo '<tr>';   
                    echo '<td>'.($combined->is_merc?'*':'').$combined->name.'</td>';
                    echo '<td align="center">'.((($combined->prewar_strength>4)&&($combined->strength==4))?'<span style="border-bottom: dashed thin;" title="('.$combined->prewar_strength.')">'.$combined->strength.'</span>':$combined->strength).'</td>';
                    echo '<td align="center"></td>';
                    echo '<td align="center"></td>';
                    echo '<td align="center">'.(isset($comboleaderbonus) ? $comboleaderbonus : '0' ).'</td>';
                    echo '<td align="center"></td>';
                    echo '<td align="center"></td>';
                    echo '<td align="center"></td>';
                    echo '<td align="center">';
                    if ( $combined->die != true && $casualtiesowed[$defender->player_id] > 0 && $player->player_id == $defender->player_id)
                    {
                        echo anchor('sw/kill/'.$combined->combatunit_id,'KILL', 'class="menu"');
                    }
                    else if ($combined->die)
                    {
                        echo 'KIA';
                    }
                    echo '</td>'; 
                    echo '</tr>';
                    $displayed[$combined->combatunit_id] = 1;   // to track who I should skip later
                    break;
                }
            }
        }
    }
}
echo '</table>';

if ( isset($sortedleaders[$defender->player_id]) )
{
    // If at least one leader has military or combat > 0 then list those that do as:
    // Leader Name M:1/1 C:0/3
    echo '<h4>Led By</h4>';
    foreach($sortedleaders[$defender->player_id] as $leader)
    {
        if ($leader->allegiance_to_house_id == $defender->player_id)
            echo $leader->name.' | '.anchor('leader/view/'.$leader->leader_id, 'VIEW', 'class="menu"').'<br />';
    }
}

foreach($jumpships as $js)
{
    
}

if ($defender->player_id == $player->player_id)
{
    echo '<br /><br />';
    echo anchor('sw/combinations/'.$territory->territory_id, 'Modify Combinations', 'class="menu"');
    
    echo '<br />';
    
    $nulltargets = $this->db->query('select * from combatunits where
        strength > 0 and
        target_id = null and
        owner_id='.$player->player_id)->result();

    $total_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
        FROM `combatunits`
        join territories on territories.territory_id=combatunits.location_id
        left join combatunits as enemies on combatunits.location_id=enemies.location_id
        where 
        combatunits.owner_id='.$player->player_id.' and   
        is_contested = 1 and
        enemies.die <> 1 and
        territory_id = '.$territory->territory_id.'
        group by combatunits.combatunit_id')->result();

    $valid_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
        FROM `combatunits`
        join territories on territories.territory_id=combatunits.location_id
        left join combatunits as enemies on combatunits.location_id=enemies.location_id
        where 
        combatunits.owner_id='.$player->player_id.' and   
        is_contested = 1 and
        territory_id = '.$territory->territory_id.' and
        enemies.die <> 1 and
        combatunits.target_id = enemies.owner_id
        group by combatunits.combatunit_id')->result();
    
    $num_factions = $this->db->query('select * from combatunits where
        location_id='.$territory->territory_id.' and 
        strength > 0 and 
        die <> 1
        group by owner_id')->result();
    
    if (count($nulltargets) > 0)
    {
        echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
        if ($this->debug>2) log_message('error', 'Null target');
    }
    else if (count($valid_targets) != count($total_targets))
    {
        echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
        if ($this->debug>2) log_message('error', 'Invalid target');
    }
    else if (count($num_factions) > 2)
    {
        echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
        if ($this->debug>2) log_message('error', 'Multi-faction combat.');
    }

}

echo '<br />';
echo '<h4>Attackers</h4>';

$attackerId = -1;
foreach($players as $p)
{
    if ($p->player_id != $territory->player_id && isset($has_units[$p->player_id]))
    {
        if ($attackerId != $p->player_id)
        {
            echo '<h3>'.$p->faction.' '.(isset($bonus[$p->player_id][-2]) ? '(Interdict -2)' : '').(isset($bonus[$p->player_id][2]) ? '(Star League +2)' : '').'</h3>';
            
            if (isset($casualtiesowed[$p->player_id]) && $casualtiesowed[$p->player_id] > 0)
                echo 'Casualties Owed: '.$casualtiesowed[$p->player_id];

            echo '<table>';
            echo '<tr>';
            echo '<th><span style="border-bottom: dashed thin;" title="Mercenary units are marked with a *">Units</span></th><th><span style="border-bottom: dashed thin;" title="Base combat strength (Mouseover for Pre-war strength if greater than 4)">STR</span></th><th><span style="border-bottom: dashed thin;" title="Under Regional or House Interdict">INT</span></th><th><span style="border-bottom: dashed thin;" title="Star League bonus">SL</span></th><th><span style="border-bottom: dashed thin;" title="Leader bonus">L</span></th><th><span style="border-bottom: dashed thin;" title="Technology bonus">T</span></th><th><span style="border-bottom: dashed thin;" title="Expected combat strength for NEXT barrage">Final</span></th><th><span style="border-bottom: dashed thin;" title="Roll from PREVIOUS barrage">Roll</span></th><th></th>';
            echo '</tr>';
            foreach($units as $unit)
            {
                if ($unit->owner_id == $p->player_id && !isset($displayed[$unit->combatunit_id]))
                {
                    
                    echo '<tr>';   
                        $starleague = (isset($bonus[$p->player_id][2]) ? 2 : 0);
                        $interdict = (isset($bonus[$p->player_id][-2]) || $p->house_interdict ? -2 : 0);
                        $leaderbonus = $this->combatbonusmodel->get_unit_bonus($unit->combatunit_id)->value;

                        // Find combos...
                        unset($comboleaderbonus);
                        $combo = 0;
                        if (isset($unit->combine_with))
                        {
                            foreach($units as $combined)
                            {
                                if ($combined->combine_with == $unit->combatunit_id)
                                {
                                    $combo = $combined->strength;
                                    $comboleaderbonus = $this->combatbonusmodel->get_unit_bonus($combined->combatunit_id)->value;
                                    break;
                                }
                            }
                        }

                        $final = $unit->strength + $combo + (isset($comboleaderbonus) ? $comboleaderbonus : 0) + $starleague + $interdict + $leaderbonus
                                + ($unit->is_conventional && $tech_bonus[$unit->owner_id] < 0 ? $tech_bonus[$unit->owner_id] : 0)
                                + ($unit->is_elemental ? $tech_bonus_elem[$unit->owner_id] : 0)
                                + (!$unit->is_elemental && !$unit->is_conventional ? $tech_bonus[$unit->owner_id] : 0);

                        echo '<td>'.($unit->is_merc?'*':'').$unit->name.'</td>';
                        echo '<td align="center">'.((($unit->prewar_strength>4)&&($unit->strength==4))?'<span style="border-bottom: dashed thin;" title="('.$unit->prewar_strength.')">'.$unit->strength.'</span>':$unit->strength).'</td>';
                        echo '<td align="center">'.(($interdict == -2) ? '-2' : '0').'</td>';
                        echo '<td align="center">'.(($starleague == 2) ? '+2' : '0').'</td>';
                        echo '<td align="center">'.(isset($leaderbonus) ? $leaderbonus : '0' ).'</td>';
                        echo '<td align="center">';
                            if ($unit->is_conventional && $tech_bonus[$unit->owner_id] > 0) // if conventional, they don't get the positive bonus for 'Mechs
                                echo '0';
                            else if ($unit->is_elemental)
                                echo $tech_bonus_elem[$unit->owner_id];
                            else echo $tech_bonus[$unit->owner_id];
                            echo '</td>';
                        echo '<td align="center">'.$final.'</td>';
                        echo '<td align="center">'.($unit->last_roll == 0 ? '' : $unit->last_roll ).'</td>';
                        
                        echo '<td align="center">';
                        if ( isset($casualtiesowed[$p->player_id]) && $unit->die != true && $casualtiesowed[$p->player_id] > 0 && $player->player_id == $p->player_id)
                        {
                            echo anchor('sw/kill/'.$unit->combatunit_id,'KILL', 'class="menu"');
                            //echo anchor('sw/kill/'.$unit->combatunit_id,'KILL', 'class="kill"');
                        }
                        else if ($unit->die)
                        {
                            echo 'KIA';
                        }
                        echo '</td>';
                    echo '</tr>';
                    
                    // Search for a combo if found...
                    if (isset($unit->combine_with))
                    {
                        foreach($units as $combined)
                        {
                            if ($combined->combine_with == $unit->combatunit_id)
                            {
                                echo '<tr>';   
                                echo '<td>'.($combined->is_merc?'*':'').$combined->name.'</td>';
                                echo '<td align="center">'.((($combined->prewar_strength>4)&&($combined->strength==4))?'<span style="border-bottom: dashed thin;" title="('.$combined->prewar_strength.')">'.$combined->strength.'</span>':$combined->strength).'</td>';
                                echo '<td align="center"></td>';
                                echo '<td align="center"></td>';
                                echo '<td align="center">'.(isset($comboleaderbonus) ? $comboleaderbonus : '0' ).'</td>';
                                echo '<td align="center"></td>';
                                echo '<td align="center"></td>';
                                echo '<td align="center"></td>';
                                echo '<td align="center">';
                                if ( isset($casualtiesowed[$p->player_id]) && $combined->die != true && $casualtiesowed[$p->player_id] > 0 && $player->player_id == $p->player_id)
                                {
                                    echo anchor('sw/kill/'.$combined->combatunit_id,'KILL', 'class="menu"');
                                    //echo anchor('sw/kill/'.$combined->combatunit_id,'KILL', 'class="kill"');
                                }
                                else if ($combined->die)
                                {
                                    echo 'KIA';
                                }
                                echo '</td>';
                                echo '</tr>';
                                $displayed[$combined->combatunit_id] = 1;   // to track who I should skip later
                            }
                        }
                    }
                }
            }
            echo '</table>';

            if ( isset($sortedleaders[$p->player_id]) )
            {
                echo '<h4>Led By</h4>';
                foreach($sortedleaders[$p->player_id] as $leader)
                {
                    if ($leader->allegiance_to_house_id == $p->player_id)
                        echo $leader->name.' | '.anchor('leader/view/'.$leader->leader_id, 'VIEW', 'class="menu"').'<br />';
                }
            }

            foreach($jumpships as $js)
            {
                // ... TODO
            }
            
            echo '<br /><br />';
            if ($p->player_id == $player->player_id)
            {
                // Modify combinations
                echo anchor('sw/combinations/'.$territory->territory_id, 'Modify Combinations', 'class="menu"');
                echo '<br />';
                
                // Assign Targets if required
                // Determine target validity
                $nulltargets = $this->db->query('select * from combatunits where
                    combatunits.location_id='.$territory->territory_id.' and 
                    strength > 0 and
                    target_id = null and
                    owner_id='.$player->player_id)->result();

                $total_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
                    FROM `combatunits`
                    join territories on territories.territory_id=combatunits.location_id
                    left join combatunits as enemies on combatunits.location_id=enemies.location_id
                    where 
                    combatunits.owner_id='.$player->player_id.' and   
                    is_contested = 1 and
                    enemies.die <> 1 and
                    territory_id = '.$territory->territory_id.'
                    group by combatunits.combatunit_id')->result();

                $valid_targets = $this->db->query('SELECT combatunits.name,  combatunits.target_id, enemies.owner_id as enemy
                    FROM `combatunits`
                    join territories on territories.territory_id=combatunits.location_id
                    left join combatunits as enemies on combatunits.location_id=enemies.location_id
                    where 
                    combatunits.owner_id='.$player->player_id.' and   
                    is_contested = 1 and
                    enemies.die <> 1 and
                    territory_id = '.$territory->territory_id.' and
                    combatunits.target_id = enemies.owner_id
                    group by combatunits.combatunit_id')->result();

                if ($this->debug>2) log_message('error', 'Valid targets '.count($valid_targets).' Total targets '.count($total_targets));

                $num_factions = $this->db->query('select * from combatunits where
                    location_id='.$territory->territory_id.' and 
                    strength > 0 and 
                    die <> 1
                    group by owner_id')->result();

                if (count($nulltargets) > 0)
                {
                    echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
                    if ($this->debug>2) log_message('error', 'Null target');
                }
                else if (count($valid_targets) != count($total_targets))
                {
                    echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
                    if ($this->debug>2) log_message('error', 'Invalid target');
                }
                else if (count($num_factions) > 2)
                {
                    echo anchor('sw/assign_targets/'.$territory->territory_id, 'Assign Targets', 'class="menu"').'<br />'; 
                    if ($this->debug>2) log_message('error', 'Multi-faction combat.');
                }
            }
            
            // Factory damage modifiers
            if ($has_factory)
            {
                echo 'Force Size MC Damage Modifier: +'.($use_force_size[$p->player_id] ? factory_modifier($forcesize[$p->player_id]) : '0');
                if ($p->player_id == $player->player_id && !$game->auto_factory_dmg_mod  && factory_modifier($forcesize[$p->player_id]) > 0)
                {
                    echo ' | '.anchor('sw/toggle_factory_dmg_mod/'.$territory->territory_id, 'Toggle '.($use_force_size[$p->player_id] ? 'OFF' : 'ON'), 'class="menu"');
                }
            }
        }
    }
}

?>