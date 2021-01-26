<script type="text/javascript">
tinymce.init({
        selector: 'textarea',
        width: "80%",
        theme: "modern",
        menubar:false,
        statusbar: false
});
</script>

<h1> <?php echo $label; ?> an Order of Battle</h1>
<br /><br />

<?php echo validation_errors(); ?>

<?php 
    if ($label == 'Create')
        echo form_open("orderofbattle/create");
    else
        echo form_open("orderofbattle/edit_oob/".$oob->orderofbattle_id);
    ?>
	
  <p>
    <label for="title">Name:</label>
    <input type="text" name="name" value="<?php if (isset($oob->name)) echo $oob->name;?>" size="50" />
  </p>  

  <p>
    <label for="description">Game Description:</label><br />
    <textarea name="description" rows="15" cols="75"><?php if (isset($oob->description)) echo $oob->description; ?></textarea>
  </p>
  
  <p>
    <label for="title">Year:</label>
    <input type="text" name="year" value="<?php if (isset($oob->year)) echo $oob->year; ?>" size="20" />
  </p> 
  
  <p>
    <label for="title">Capitals to Win:</label>
    <input type="text" name="capitals_to_win" value="<?php if (isset($oob->capitals_to_win)) echo $oob->capitals_to_win; ?>" size="20" />
  </p> 

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
      <tr><td>NO </td><td><?php echo form_radio('destroy_jumpships', '0', !$oob->destroy_jumpships);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('destroy_jumpships', '1', $oob->destroy_jumpships);?></td></tr>
      </table>
    </p>     

    <h3>Allow Factory Damage Modifier Cancellation</h3>
    <p>
        Selecting this option enables attackers to choose not to add their force size
        modifier to the factory damage roll.
    </p>
    <p>
      <label for="auto_factory_dmg_mod">Factory Damage:</label>
      <table cellspacing="10px">
      <tr><td>NO </td><td><?php echo form_radio('auto_factory_dmg_mod', '1', $oob->auto_factory_dmg_mod);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('auto_factory_dmg_mod', '0', !$oob->auto_factory_dmg_mod);?></td></tr>
      </table>
    </p>  

    <h3>Mercenary Phase</h3>
    <p>
        Selecting this option enables the Mercenary Phase.  At the start of each turn before production, a random mercenary unit 
        will be put up for bidding.  This option is used in the First Succession War game.
    </p>
    <p>
      <label for="use_merc_phase">Mercenary Phase:</label>
      <table cellspacing="10px">
      <tr><td>NO </td><td><?php echo form_radio('use_merc_phase', '0', !$oob->use_merc_phase);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('use_merc_phase', '1', $oob->use_merc_phase);?></td></tr>
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
      <tr><td>NO </td><td><?php echo form_radio('use_comstar', '0', !$oob->use_comstar);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('use_comstar', '1', $oob->use_comstar);?></td></tr>
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
      <tr><td>NO </td><td><?php echo form_radio('use_terra_interdict', '0', !$oob->use_terra_interdict);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('use_terra_interdict', '1', $oob->use_terra_interdict);?></td></tr>
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
      <tr><td>NO </td><td><?php echo form_radio('use_terra_loot', '0', !$oob->use_terra_loot);?></td></tr>
      <tr><td>YES </td><td><?php echo form_radio('use_terra_loot', '1', $oob->use_terra_loot);?></td></tr>
      </table>
    </p>
    
  <p><?php echo form_submit('submit', $label);?></p>

      
<?php echo form_close();?>