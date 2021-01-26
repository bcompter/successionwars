<h1>Succession Wars | Game Deck</h1>
<p><?php echo anchor('game/view_deck/'.$game->game_id, '<< Back to the Deck View');?></p>

<h3>Add a Card to the Game <?php echo $game->title; ?> </h3>

<h3>List of Available Cards</h3>
<br />
<table>
    <tr>
        <th>Card</th>
        <th>Description</th>
        <th>Phase</th>
        <th>&nbsp</th>
    </tr>
    
    <?php foreach($cards as $card): ?>
  
        <tr>
            <td><?php echo $card->title; ?></td>
            <td><?php echo $card->text; ?></td>
            <td><?php echo $card->phase; ?></td>
            <td>
                <?php if ($is_game_owner): ?>
                    <?php echo anchor('game/add_card/'.$game->game_id.'/'.$card->type_id, 'Add'); ?>
                <?php else: ?>
                    &nbsp
                <?php endif; ?>
            </td>
        </tr>
    
    <?php endforeach; ?>
    
</table>