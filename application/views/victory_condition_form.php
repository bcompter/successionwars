<div class="center">
    <h1>Add a Victory Condition(s) for <?php echo $game->title; ?></h1>
    <br /><br />

    <?php 

        $errors = validation_errors();
        if ($errors != '')
            echo '<div class="error">'.$errors.'</div>';
    ?>

    <?php echo form_open("victory_conditions/add/".$game->game_id);?>
    
    <h2>For Who?</h2>
    <select name="who">
        <option>Everyone</option>
        <?php foreach($players as $p): ?>
        <option>
            <?php echo $p->faction; ?>
        </option>
        <?php endforeach; ?>
    </select>
    
    <h2>Victory Conditions</h2>
    <table>
        <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th>Threshold</th>
            <th>Duration</th>
        </tr>
        <tr>
            <td><input type="checkbox" name="capital"></td>
            <td><strong>Capital</strong></td>
            <td><input type="text" name="capital_threshold"></td>
            <td><input type="text" name="capital_duration"></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="regional"></td>
            <td><strong>Regional</strong></td>
            <td><input type="text" name="regional_threshold"></td>
            <td><input type="text" name="regional_duration"></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="territory"></td>
            <td><strong>Territorial</strong></td>
            <td><input type="text" name="territory_threshold"></td>
            <td><input type="text" name="territory_duration"></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="military"></td>
            <td><strong>Military</strong></td>
            <td><input type="text" name="military_threshold"></td>
            <td><input type="text" name="military_duration"></td>
        </tr>
        
        <tr>
            <td><input type="checkbox" name="technology"></td>
            <td><strong>Technology</strong></td>
            <td><input type="text" name="technology_threshold"></td>
            <td><input type="text" name="technology_duration"></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="industrial"></td>
            <td><strong>Industrial</strong></td>
            <td><input type="text" name="industrial_threshold"></td>
            <td><input type="text" name="industrial_duration"></td>
        </tr>
        <tr>
            <td><input type="checkbox" name="leader"></td>
            <td><strong>Leaders</strong></td>
            <td><input type="text" name="leader_threshold"></td>
            <td><input type="text" name="leader_duration"></td>
        </tr>
        
        
    </table>
    <br />
    <p><?php echo form_submit('submit', 'Submit');?></p>
    
    <?php echo form_close();?>

</div>