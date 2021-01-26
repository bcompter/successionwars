<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
        echo '<info>';
            echo '<h2>'.$player->faction.' Technology Level: '.$player->tech_level.'</h2>';
            echo "<span style='border-bottom: dashed thin;' title='60% chance of no effect.  30% chance of +1 to Tech.  10% chance of +2 to Technology.'";
            echo '<h4>Technology Roll: 5M CBills</h4>';
            echo "</span> ";
                
            if ($player->tech_level > 23)
            {
                echo '<h4>Full Technology Combat Bonus</h4>';
                echo 'All your \'Mech units get +2 bonus while you have a technology level of 24 or 25.<br />';
            }
            if (($player->tech_level < 25)&&( $player->user_id == $this->ion_auth->get_user()->id ))
                // display tech roll link
                echo anchor('technology/tech_roll/'.$player->player_id,'INVEST IN RESEARCH','class="menu"');
            
            if ($player->tech_level > 6 && $player->tech_level < 24)
            {
                if ($player->tech_level > 11)
                    $allowed = 2;
                else
                    $allowed = 1;
                
                echo '<h3>Technology Combat Bonus</h3>';
                echo ''.$player->tech_bonus.' of '.$allowed.' bonus'.(($allowed>1)?'es':'').' applied this turn.<br />';
                
                if ($allowed - $player->tech_bonus > 0)
                {
                    echo 'Apply a +2 strength bonus to <select id="option">';
                    foreach( $targets as $unit )
                    {
                        echo '<option value="'.$this->config->item('base_url').'index.php/technology/tech_bonus/'.$player->player_id.'/'.$unit->combatunit_id.'">'.($unit->is_merc?'*':'').$unit->name.', '.$unit->strength.((($unit->prewar_strength>4)&&($unit->strength==4))?' ('.$unit->prewar_strength.')':'').' @ '.$unit->territory_name.'</option>';     
                    }
                    echo '</select> | ';
                    echo anchor('#',' APPLY' ,'class="dc"');
                    echo '<br />Prewar strength of rebuilt units are shown in parentheses.<br />';
                }
                
            }

for ($x=90; $x<=125; $x++)
{            
    $tech_mouse_over[$x]='';  
    $tech_mod[$x]='';
}
$tech_mouse_over[90]='\'Mech prices increase to $10 and $8, and the combat ratings of all units decrease by 2';
$tech_mouse_over[93]='JumpShip movement decreases by 1, from 3 to 2';
$tech_mouse_over[95]='Combat ratings of all units decrease by 1';
//$tech_mouse_over[100]='Players begin here';
$tech_mouse_over[107]='\'Mech unit increases combat rating by 2 once per turn';
$tech_mouse_over[110]='House \'Mech price decreases to $7 and $5 for Mercs';
$tech_mouse_over[112]='Two \'Mech units increase combat rating by 2 once per turn';
if ($player->using_elementals) $tech_mouse_over[113]='Elemental price decreases to $4';
$tech_mouse_over[115]='JumpShip movement increases by 1, from 3 to 4';
$tech_mouse_over[117]='Resource management adds $7 to tax yield';
$tech_mouse_over[120]='House \'Mech price goes down to $6';
if ($player->using_elementals) $tech_mouse_over[121]='Combat ratings of all Elemental units increase by +1';
$tech_mouse_over[124]='Combat ratings of all \'Mech units increase by 2';
$tech_mouse_over[125]='Hyperspace communications mastered. All leaders may add their Combat Ability to an attack regardless of whether the leader is present in the Region.';

$tech_mod[90]="\$10 & \$8, -2 Combat";
$tech_mod[93]="-1 JS";
$tech_mod[95]="-1 Combat";
//$tech_mod[100]="Start";
$tech_mod[107]="+2 to 1 'Mech";
$tech_mod[110]="\$7 & \$5";
$tech_mod[112]='+2 to 2 \'Mechs';
if ($player->using_elementals) $tech_mod[113]='$4 Elementals';
$tech_mod[115]="+1 JS";
$tech_mod[117]="+7 Taxes";
$tech_mod[120]="\$6";
if ($player->using_elementals) $tech_mod[121]="+1 to Elementals";
$tech_mod[124]="+2 to 'Mechs";
$tech_mod[125]="Com";

// SQL Get all the houses and their tech levels

$current_tech_at=array();
for ($x=0; $x<=35; $x++)
{            
    $current_tech_at[$x]='';  
}

foreach ($players as $pla)
{
    $current_tech_at[10+$pla->tech_level] = $current_tech_at[10+$pla->tech_level]. ($current_tech_at[10+$pla->tech_level] != '' ? ', ' : '').$pla->faction;
}
echo '<h3>Technology Scale</h3>';
echo "<table border='1'>";
	echo "<tr>";
		echo "<th>Tech</th>";
		echo "<th>Players</th>";
		echo "<th>Tech Mod</th>";
	echo "</tr>";
        
	for ($row=25; $row>=-10; $row--)
	{
		if (($tech_mod[$row+100] !='') || ($current_tech_at[$row+10] != ''))
		{
			echo "<tr>";
				echo "<td>".$row."</td>";
				echo "<td>".$current_tech_at[$row+10]."</td>";
				echo "<td><span style='border-bottom: dashed thin;' title=\"".$tech_mouse_over[$row+100]."\">".$tech_mod[$row+100]."</span></td>";
			echo "</tr>";
		}
	}
echo "</table>";
        echo '</info>';
    echo "</response>";
?>