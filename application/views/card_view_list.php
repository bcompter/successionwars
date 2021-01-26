<h1>Succession Wars | Available Cards</h1>
<p><?php echo anchor('game', '<< Back to the Dashboard');?></p>

<h3>List of Available Cards</h3>
<br />
<table class="tablenew">
    <tr>
        <th>ID</th>
        <th>Card</th>
        <th>Description</th>
        <th>Phase</th>
    </tr>
    
    <?php foreach($cards as $card): ?>
  
        <tr>
            <td><?php echo $card->type_id; ?></td>
            <td><?php echo $card->title; ?></td>
            <td><?php echo $card->text; ?></td>
            <td><?php echo $card->phase; ?></td>
        </tr>
    
    <?php endforeach; ?>
    
</table>