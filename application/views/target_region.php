<h3><?php echo $title; ?></h3>

Pay 5 CBills. Increase the resources of a target Region by two if it is below four resources.
<h4>Select Target</h4>
<table>
    <thead>
        <tr>
            <th>Region</th><th>Resource</th><th>&nbsp;</th>
        </tr>
    </thead>
    <?php foreach($regions as $region): ?>

    <tr>
        <td>
            <?php
                echo anchor('sw/location/'.$region->territory_id,$region->name,'class="menu hoverlink" hoverid="'.'#'.str_replace('.', '', str_replace(' ', '', $region->name)).'"');
            ?>
        </td>
        <td>
            <?php echo $region->resource." -> ".($region->resource+2) ; ?>
        </td>
        <td>
            <?php echo anchor($this->config->item('base_url').'index.php/cards/play/'.$card->card_id.'/'.$region->territory_id, 'TARGET', 'class="menu"'); ?>
        </td>
    </tr>
    
    <?php endforeach; ?>
</table>