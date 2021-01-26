<h3><?php echo $title; ?></h3>

<h4>Select a Target</h4>
Target Jumpship +1 Movement
<table>
    <thead>
        <tr>
            <th>Jumpship</th><th align="center"><span style="border-bottom: dashed thin;" title="Number of jumps completed this turn">Jumped</span></th><th>Location</th><th>&nbsp;</th>
        </tr>
    </thead>
    <?php foreach($jumpships as $j): ?>

    <tr>
        <td>
            JS<?php echo $j->capacity; ?>, (<?php echo anchor('jumpship/view/'.$j->jumpship_id,($j->jumpship_name != "" ? $j->jumpship_name : 'unnamed'),'class="menu hoverlink" hoverid="'.'#'.$j->jumpship_id.'"'); ?>)
        </td>
        <td>
            <?php echo $j->moves_this_turn; ?>
        </td>
        <td>
            <?php echo anchor('sw/location/'.$j->location_id,$j->territory_name,'class="menu hoverlink" hoverid="'.'#'.str_replace('.', '', str_replace(' ', '', $j->territory_name)).'"'); ?>
        </td>
        <td>
            <?php echo anchor($this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$j->jumpship_id, 'SELECT', 'class="menu"'); ?>
        </td>
    </tr>
    
    <?php endforeach; ?>
</table>