<h1>Succession Wars | Game Deck</h1>

<p><?php echo anchor('game/view/'.$game->game_id, '<< Back to the Game View');?></p>

<h3>Game Deck for <?php echo $game->title; ?></h3>

<?php if ($is_game_owner && $game->phase == 'Setup'): ?>
    <h4>Add a New Card</h4>

    <?php echo anchor('game/card_list/'.$game->game_id, 'View Card List'); ?>
    
<?php endif; ?>

<table>
    <th>Card</th>
    <th>Description</th>
    <th>&nbsp</th>
    
    <?php foreach ($cards as $card): ?>
    <tr>
        <td>
            <?php echo $card->title; ?>
        </td>
        <td>
            <?php echo $card->text; ?>
        </td>
        <td>
            <?php if ($is_game_owner && $game->phase == 'Setup'): ?>
                <?php 
                    echo ' | '.anchor('game/add_card/'.$game->game_id.'/'.$card->type_id, 'COPY').' | ';
                    echo anchor('game/remove_card/'.$game->game_id.'/'.$card->card_id, 'DELETE');
                ?>
            <?php else: ?>
                &nbsp
            <?php endif; ?>

        </td>
    </tr>
    <?php endforeach; ?>
    
</table>