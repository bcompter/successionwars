<h3>Game Check for <?php echo $game->title; ?></h3>

<table>
    
    <tr>
        <th>&nbsp</th><th>Order of Battle Count</th><th>Game Count</th>
    </tr>
    <tr>
        <td>Cards</td>
        <td align="center"><?php echo $oob_cards; ?></td>
        <td align="center"><?php echo $game_cards; ?></td>
    </tr>
    <tr>
        <td>Territories</td>
        <td align="center"><?php echo $oob_territories; ?></td>
        <td align="center"><?php echo $game_territories; ?></td>
    </tr>
    <tr>
        <td>Combat Units</td>
        <td align="center"><?php echo $oob_combatunits; ?></td>
        <td align="center"><?php echo $game_combatunits; ?></td>
    </tr>
    <tr>
        <td>Leaders</td>
        <td align="center"><?php echo $oob_leaders; ?></td>
        <td align="center"><?php echo $game_leaders; ?></td>
    </tr>
    <tr>
        <td>Factories</td>
        <td align="center"><?php echo $oob_factories; ?></td>
        <td align="center"><?php echo $game_factories; ?></td>
    </tr>
    <tr>
        <td>Jumpships</td>
        <td align="center"><?php echo $oob_jumpships; ?></td>
        <td align="center"><?php echo $game_jumpships; ?></td>
    </tr>
</table>