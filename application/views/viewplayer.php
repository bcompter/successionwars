<?php
    echo "<?xml version=\"1.0\"?>\n";
    echo "<response>\n";
    echo "<info>";
    ?>

<h3><?php echo $player->faction ?></h3>
<?php echo anchor('player/view_all/'.$game_id, '<< Back to Player List', 'class="menu"'); ?>

<table>
    <tr><td>Played By:</td><td><?php echo $playeruser->username; ?></td></tr>
    <tr><td>Capitals:</td><td><?php echo (isset($num_caps) ? $num_caps : '0'); ?></td></tr>
    <tr><td>Regional Capitals:</td><td><?php echo (isset($num_regionals) ? $num_regionals : '0'); ?></td></tr>
    <tr><td>Factories:</td><td><?php echo (isset($num_factories) ? $num_factories : '0'); ?></td></tr>
    <tr><td>CBills (MM):</td><td><?php echo $player->money; ?></td></tr>
    <tr><td>Technology:</td><td><?php echo $player->tech_level; ?></td></tr>
    <tr><td>Cards in Hand:</td><td><?php echo $player->cards; ?></td></tr>
    <tr><td>Military Strength:</td><td><?php echo ($military+$total_leader_combat).' (M:'.$total_mech_strength.', C:'.$total_conventional_strength.', L:'.$total_leader_combat.')'; ?></td></tr>
    <tr><td>Merc/Non-Merc:</td><td><?php echo $total_merc_strength.'/'.$total_nmerc_strength; ?></td></tr>
    <tr><td>Jumpship Capacity:</td><td><?php echo $capacity.' (JS5:'.$js5.', JS3:'.$js3.', JS2:'.$js2.', JS1:'.$js1.')'; ?></td></tr>
    <tr><td>Taxable Revenue:</td><td><?php echo ($taxes+$total_leader_admin+($player->tech_level>=17?7:0)).' (R:'.$taxes.', '
        .'<span style="border-bottom: dashed thin;" title="'.($negative_leader_admin<0?'Bribed leader(s) are contributing: '.$negative_leader_admin.'!!! ':'')
            .'Leaders in enemy territories (i.e. leading an attack) are not included.'
            .'">L: '
            .$total_leader_admin
        .'</span>'
        .($player->tech_level>=17?', T:7':'').')'; ?></td></tr>
   
    </table>

<?php
    
    if ($player->free_bribes > 0)
        echo 'Free Bribe'.($player->free_bribes>1?'s':'').' Remaining: '.$player->free_bribes.'<br />';
    if (isset($player->official_capital_territory))
        echo 'Official Capital: '.$player->official_capital_territory.'<br />';
    
    if ($player->house_interdict > 0) 
        echo 'Under House Interdict for '.$player->house_interdict.' more turn'.($player->house_interdict>0?'s':'').'';
    
    if ($player->user_id != $user->id && $player->turn_order > 0)
    {
        echo anchor('player/trade_cbills/'.$player->player_id, 'Give CBills', 'class="menu"');
        echo '<br />';
        echo anchor('cards/trade_cards/'.$player->player_id, 'Give a Card', 'class="menu"');
        echo '<br />';
    }
    
    echo anchor('player/military/'.$player->player_id, 'View Military Unit List', 'class="menu"');
?>
    
<h3>Leaders</h3>
<table>
    <tr>
        <th>Name</th>
        <th>M</th>
        <th>C</th>
        <th>A</th>
        <th>L</th>
        <th>Location</th>
        <th>Status</th>
    </tr>
<?php foreach($leaders as $leader): 
            $territory_id = $leader->location;
            $territory_id = str_replace('.', '', $territory_id);
            $territory_id = str_replace(' ', '', $territory_id);?>
    <tr rowspacing="10px">
        <td align="center"><?php echo (isset($leader->associated_units) ? '* ' : '').anchor('leader/view/'.$leader->leader_id,$leader->name,'class="menu"'); ?></td>
        <td align="center"><?php echo ($leader->military > 0 && $leader->military_used > 0 ? '<span style="border-bottom: dashed thin;" title="'.$leader->military_used.' of '.$leader->military.' unit combinations made.">'.$leader->military.'</span>' : $leader->military); ?></td>
        <td align="center"><?php echo ($leader->combat_used > 0 ? '<span style="border-bottom: dashed thin;" title="Combat bonus has been used.">'.$leader->combat.'</span>' : $leader->combat); ?></td>
        <td align="center"><?php echo $leader->admin; ?></td>
        <td align="center"><?php echo $leader->loyalty; ?></td>
        <td align="center"><?php echo anchor('sw/location/'.$leader->location_id,$leader->location,'class="menu hoverlink" hoverid="'.'#'.$territory_id.'"'); ?></td>
        <td align="center">
            TBD
        </td>
    </tr>
<?php endforeach; ?>
</table>

<?php if (count($conditions) > 0): ?>
    <h3>Victory Conditions</h3>
    <table>
        <tr>
            <th>Type</th>
            <th>Threshold</th>
            <th>Duration</th>
            <th>Current Duration</th>
        </tr>
    <?php foreach($conditions as $c): ?>
        <tr rowspacing="10px">
            <td align="center"><?php echo $c->type; ?></td>
            <td align="center"><?php echo $c->threshold; ?></td>
            <td align="center"><?php echo $c->duration; ?></td>
            <td align="center"><?php echo $c->current_duration; ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php
        echo '</info>';
    echo "</response>";
?>