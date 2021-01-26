<h2>Vote for a New Game Owner</h2>
<p><?php echo anchor('game/view/'.$game->game_id, '<< Back to the Game View');?></p>
<p>
    In order to change the game owner you need a unanimous vote of all (other) non-eliminated players in the game.
    <br />
    Remember it is just a game!  No hard feelings, ok?
</p>
<p>
    Current Game Owner: <?php echo $owner->username; ?>
</p>
<h3>Existing Votes</h3>
<table>
    <tr>
        <th>Player</th><th>Voting For</th><th>Vote Cast On</th>
        <?php if (count($votes) == 0): ?>
            
    <tr>
        <td colspan="3">No Votes yet!</td>
    </tr>
        
        <?php else: ?>
        <?php foreach ($votes as $vote): ?>
    <tr>
        <td><?php echo $vote->username; ?></td>
        <td><?php echo $vote->vote_username; ?></td>
        <td><?php echo $vote->created_on; ?></td>
    </tr>
        <?php endforeach; ?>
        
        <?php endif; ?>
</table>

<h3>Cast (or change) Your Vote</h3>

<table>
    <tr>
        <th>Player</th><th>Cast Your Vote!</th>
    </tr>
        <?php foreach ($players as $p): ?>
        <?php if ($p->turn_order != 0): ?>
    <tr>
        <td><?php echo $p->username; ?></td>
        <td> | <?php echo anchor('player/vote_for_go/'.$game->game_id.'/'.$p->player_id, 'VOTE'); ?> </td>
    </tr>
        <?php endif; ?>
        <?php endforeach; ?>
</table>