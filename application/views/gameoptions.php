<h2> <?php echo $game->title; ?> | Game Options</h2>
<?php echo anchor('game/view/'.$game->game_id,'<< Back to the Game'); ?>
<br /><br />
<?php echo form_open("game/options/".$game->game_id);?>

<h3>Destroy Jumpships</h3>
<p>
    Selecting this option cause all jumpships caught by enemies in battle to be 
    destroyed instead of captured.  This option is traditionally used in the 
    first succession wars game to reflect the fact that military leaders didn't 
    much care for saving technology or resources.
</p>
<p>
  <label for="destroy_jumpships">Destroy Jumpships:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('destroy_jumpships', '0', !$game->destroy_jumpships);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('destroy_jumpships', '1', $game->destroy_jumpships);?></td></tr>
  </table>
</p>     
  
<h3>Allow Factory Damage Modifier Cancellation</h3>
<p>
    Selecting this option enables attackers to choose not to add their force size
    modifier to the factory damage roll.
</p>
<p>
  <label for="factory_damage">Factory Damage:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('factory_damage', '1', $game->auto_factory_dmg_mod);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('factory_damage', '0', !$game->auto_factory_dmg_mod);?></td></tr>
  </table>
</p>  

<h3>Mercenary Phase</h3>
<p>
    Selecting this option enables the Mercenary Phase.  At the start of each turn before production, a random mercenary unit 
    will be put up for bidding.  This option is used in the First Succession War game.
</p>
<p>
  <label for="merc_phase">Mercenary Phase:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('merc_phase', '0', !$game->use_merc_phase);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('merc_phase', '1', $game->use_merc_phase);?></td></tr>
  </table>
</p> 

<h3>Capitals to Win</h3>
<p>
    You can select how many enemy capitals are required to win the game.  A smaller number 
    will make the game shorter while a larger value will demand total annihilation.  
</p>
<p>
  <label for="capitals_to_win">Capitals to Win:</label>
  <table cellspacing="10px">
  <tr>
      <td>
          <select name="capitals_to_win">
              <?php 
                for ($index = 2; $index < count($players) + 1; $index++): 
              ?>
              
              <option value=
                <?php echo '"'.$index.'"'; 
                echo ($game->capitals_to_win == $index ? 'selected' : '');?>
              ><?php echo $index; ?></option>
              
              <?php endfor; ?>
          </select>
      </td>
  </tr>
  </table>
</p> 

<h3>Comstar</h3>
<p>
    Sometimes you feel like Comstar and sometimes you don't.  This option enables or disables
    the Comstar bribing functionality in your game.
</p>
<p>
  <label for="use_comstar">Use Comstar:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('use_comstar', '0', !$game->use_comstar);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('use_comstar', '1', $game->use_comstar);?></td></tr>
  </table>
</p> 

<h3>Terra Interdict</h3>
<p>
    This option enables or disables
    the House Interdict associated with the capture of Terra in your game.  Cause Comstar doesn't have to be jerks about you parking your BattleMechs on their home turf.
</p>
<p>
  <label for="use_terra_interdict">Use Comstar:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('use_terra_interdict', '0', !$game->use_terra_interdict);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('use_terra_interdict', '1', $game->use_terra_interdict);?></td></tr>
  </table>
</p>

<h3>Terra Loot</h3>
<p>
    This option enables or disables
    the loot associated with being the first or subsequent owner of Terra.
</p>
<p>
  <label for="use_terra_loot">Use Comstar:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('use_terra_loot', '0', !$game->use_terra_loot);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('use_terra_loot', '1', $game->use_terra_loot);?></td></tr>
  </table>
</p>

<h3>Expanded Jumpships</h3>
<p>
    This option enables or disables
    the availability of expanded jumpship sizes of 7, 9, and 12.
</p>
<p>
  <label for="use_extd_jumpships">Use Expanded Jumpships:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('extd_jumpships', '0', !$game->use_extd_jumpships);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('extd_jumpships', '1', $game->use_extd_jumpships);?></td></tr>
  </table>
</p>

<h3>Alternate Victory Conditions</h3>
<p>
    This option enables or disables Alternate Victory Conditions.  Tired of the same old Capital grab?  Fight over factories, economic resources, technology, or other unique victory conditions.
    If activated you will find a new link on the game view to add and edit the victory conditions in your game.
</p>
<p>
  <label for="alt_victory">Use Alternate Victory Conditions:</label>
  <table cellspacing="10px">
  <tr><td>NO </td><td><?php echo form_radio('alt_victory', '0', !$game->alt_victory);?></td></tr>
  <tr><td>YES </td><td><?php echo form_radio('alt_victory', '1', $game->alt_victory);?></td></tr>
  </table>
</p>

  <p><?php echo form_submit('submit', 'Save');?></p>
      
<?php echo form_close();?>