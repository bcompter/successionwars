<?php

class Production extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }  

    /**
     * View all manufacturing centers in the game belonging to a player
     * 
     * @param type $game_id 
     */
    function view_all($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'No such game!.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is playing in the game
        // and also be the current player's turn
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {

                $page['is_playing'] = true;
                $page['player'] = $player;

            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'You are not playing in that game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch all territories owned by this player
        $this->load->model('territorymodel');
        $this->db->order_by('name');
        $page['territories'] = $this->territorymodel->get_by_player($page['player']->player_id);
        
        // Fetch all factories currently owned
        $this->load->model('factorymodel');
        $this->db->order_by('name');
        $page['factories'] = $this->factorymodel->get_by_player($page['player']->player_id);
        
        $page['content'] = 'factories';
        $this->load->view('templatexml', $page);
    }
    
    /**
     * View a manufacturing center
     * 
     * @param type $mc_id 
     */
    function view($mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $mc_id == 0 )
        {
            $page['error'] = 'No such manufacuring center.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the MC
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['factory']->location_id);
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['territory']->game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( isset($player->user_id) && $player->user_id == $this->ion_auth->get_user()->id )
            {

                $page['is_playing'] = true;
                $page['player'] = $player;

            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'You are not playing in that game.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must own the factory
        if ( $page['player']->player_id != $page['territory']->player_id )
        {
            $page['error'] = 'You do not own that manufacturing center.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['territory']->game_id);
        
        // Fetch all combat units capable of being constructed
        $this->load->model('combatunitmodel');
        $page['combatunits'] = $this->combatunitmodel->get_by_killed($page['player']->player_id);
        
        // Fetch all upgradable units
        $page['upgradable'] = $this->combatunitmodel->get_upgradable($page['territory']->territory_id);
        
        // Fetch the production line
        $this->db->where('location_id', $page['factory']->location_id);
        $page['units_being_built'] = $this->combatunitmodel->get_under_construction($page['player']->player_id);
        
        $this->load->model('jumpshipmodel');
        $this->db->where('location_id', $page['factory']->location_id);
        $page['ships_being_built'] = $this->jumpshipmodel->get_under_construction($page['player']->player_id);
        
        // Load the view
        $page['content'] = 'factory';
        $this->load->view('templatexml', $page);  
    }
    
    /**
     * Build a manufacturing center
     * 
     */
    function mc($territory_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if($territory_id==0)
        {
            $page['error'] = 'No such territory.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the territory, must exist
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($territory_id);
        if ( !isset($territory->territory_id) )
        {
            $page['error'] = 'No such territory.';
            $this->load->view('templatexml', $page);
            return;
        }
  
        $page = $this->check($territory->game_id);  // get rid of!!!
        $page['territory'] = $territory;
      
        // Make sure the territory does not already have a manufacturing center
        $this->load->model('factorymodel');
        $factories = $this->factorymodel->get_by_location($territory->territory_id);
        if ( isset($factories->factory_id) )
        {
            $this->page['error'] = 'There already is a manufacturing center in '.$territory->name;
            $this->view_all($page['game']->game_id);
            return;
        }       
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
       
        // Start construction!
        $this->db->trans_start();
        
        // Player must be able to afford it
        $page['player'] = $this->playermodel->get_by_id($page['territory']->player_id);
        if ( $page['player']->money < 40 )
        {
            $this->db->trans_complete();
            $this->page['error'] = 'You can\'t afford it!';
            $this->view_all($page['game']->game_id);
            return;
        }
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $page['player']->player_id;
        $playerupdate->money = $page['player']->money - 40;
        $this->playermodel->update($page['player']->player_id, $playerupdate);
        
        $mc = new stdClass();
        $mc->location_id = $territory_id;
        $mc->is_damaged = false;
        $mc->being_built = true;
        $this->factorymodel->create($mc);
        
        game_message($page['game']->game_id, $page['player']->faction.' begins construction on a manufacturing center in '.$territory->name.'.');
        $this->page['notice'] = 'Building Manufacturing Center at '.$territory->name.'.';
        $this->db->trans_complete();
        
        // Reload the MC view
        $this->view_all($page['game']->game_id);
    }
    
    /**
     * Build a combat unit
     * 
     * @param type $combatunit_id
     * @param type $mc_id 
     */
    function combatunit($combatunit_id=0, $mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $combatunit_id == 0 || $mc_id == 0 )
        {
            $page['error'] = 'No such territory.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the combat unit
        $this->load->model('combatunitmodel');
        $page['combatunit'] = $this->combatunitmodel->get_by_id($combatunit_id);
        
        // Fetch the factory
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        if (!isset($page['factory']->factory_id))
        {
            $page['error'] = 'No such manufacturing center.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the player who owns the factory
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['combatunit']->owner_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        // Production phase and player is phasing
        if ( $page['game']->player_id_playing != $page['player']->player_id || $page['game']->phase !="Production" )
        {
            $this->page['error'] = 'Not the right phase.';
            $this->view($mc_id);
            return;
        }   
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Player owned by signed in user
        if ( $this->ion_auth->get_user()->id != $page['player']->user_id )
        {
            $this->page['error'] = 'You do not own that combat unit or factory.';
            $this->view($mc_id);
            return;
        }
        
        // Combat unit must be buildable
        if ( $page['combatunit']->strength != 0 )
        {
            $this->page['error'] = 'You can\'t build a combat unit that is already constructed.';
            $this->view($mc_id);
            return;
        }      
        
        // Factory undamaged and not being built
        if ( $page['factory']->is_damaged || $page['factory']->being_built )
        {
            $this->page['error'] = 'You can\'t build anything, including combat units, at a damaged manufacturing center!';
            $this->view($mc_id);
            return;
        }
        
        // Start construction!!!
        $this->db->trans_start();
        
         // Player must be able to afford it
        $cost = get_price($page['combatunit']->is_merc, $page['player']->tech_level);
        if ( $page['player']->money < $cost )
        {
            $this->db->trans_complete();
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($mc_id);
            return;
        }

        $this->db->query('UPDATE players SET money=money-'.$cost.' WHERE player_id='.$page['player']->player_id);

        $page['combatunit']->being_built = true;
        $page['combatunit']->price_paid = $cost;
        $page['combatunit']->location_id = $page['factory']->location_id;
        $this->combatunitmodel->update($page['combatunit']->combatunit_id, $page['combatunit']);
        if ($page['combatunit']->is_rebuild)
            $strength = 4;
        else
            $strength = $page['combatunit']->prewar_strength;
        
        game_message($page['game']->game_id, $page['player']->faction.' builds '.$page['combatunit']->name.', '.$strength.' on '.$page['factory']->name.'.');
        $this->db->trans_complete();
        $this->page['notice'] = 'Building '.$page['combatunit']->name.'.';

        // Reload factory view
        $this->view($mc_id);
    }
    
    /**
     * Build a conventional unit
     */
    function conventional($mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $mc_id == 0 )
        {
            $page['error'] = 'No such factory.';
            $this->load->view('templatexml', $page);
        }
            
        // Fetch the factory
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['factory']->location_id);
        
        // Fetch the player who owns the factory
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['territory']->player_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases, including building a conventional unit, may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Production phase and player is phasing
        if ( $page['game']->player_id_playing != $page['player']->player_id || $page['game']->phase !="Production" )
         {
            $this->page['error'] = 'Not the right phase!';
            $this->view($mc_id);
            return;
        }   
        
        // Player owned by signed in user
        if ( $this->ion_auth->get_user()->id != $page['player']->user_id )
        {
            $this->page['error'] = 'You are not playing in that game!';
            $this->view($mc_id);
            return;
        }    
        
        // Factory undamaged and not being built
        if ( $page['factory']->is_damaged || $page['factory']->being_built )
        {
            $this->page['error'] = 'You aren\'t allowed to build anything at this factory, let alone a conventional unit!';
            $this->view($mc_id);
            return;
        }
      
        // Start construction!!!
        $this->db->trans_start();
        
        $page['player'] = $this->playermodel->get_by_id($page['territory']->player_id);
        // Player must be able to afford it
        $cost = 3;
        if ( $page['player']->money < $cost )
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($mc_id);
            $this->db->trans_complete();
            return;
        }
        
        $this->db->query('update players set money = (money - 3) where player_id='.$page['player']->player_id);

        $this->load->model('combatunitmodel');
        $conventional = new stdClass();
        $conventional->name = 'Conventional';
        $conventional->prewar_strength = 3;
        $conventional->owner_id = $page['player']->player_id;
        $conventional->location_id = $page['factory']->location_id;
        $conventional->size = 2;
        $conventional->is_conventional = true;
        $conventional->being_built = true;
        $conventional->price_paid = $cost;
        $conventional->game_id = $page['player']->game_id;
        $this->combatunitmodel->create($conventional);
            
        game_message($page['game']->game_id, $page['player']->faction.' builds a Conventional unit on '.$page['territory']->name.'.');
        
        $this->db->trans_complete();
        
        $this->page['notice'] = 'Building conventional unit.';
        
        // Reload factory view
        $this->view($mc_id);        
    }
    
    /**
     * Build an elemental unit
     * Strength 2
     * Size 1
     * Cost 5
     * May combine with any other unit
     */
    function elemental($mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $mc_id == 0 )
        {
            $page['error'] = 'No such factory.';
            $this->load->view('templatexml', $page);
        }
            
        // Fetch the factory
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['factory']->location_id);
        
        // Fetch the player who owns the factory
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['territory']->player_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases, including building an elemental unit, may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Production phase and player is phasing
        if ( $page['game']->player_id_playing != $page['player']->player_id || $page['game']->phase !="Production" )
         {
            $this->page['error'] = 'Not the right phase!';
            $this->view($mc_id);
            return;
        }   
        
        // Player owned by signed in user
        if ( $this->ion_auth->get_user()->id != $page['player']->user_id )
        {
            $this->page['error'] = 'You are not playing in that game!';
            $this->view($mc_id);
            return;
        }    
        
        // Factory undamaged and not being built
        if ( $page['factory']->is_damaged || $page['factory']->being_built )
        {
            $this->page['error'] = 'You aren\'t allowed to build anything at this factory, let alone an elemental unit!';
            $this->view($mc_id);
            return;
        }
        
        // Player must be able to afford it
        $cost = 5;
        
        if ($page['player']->tech_level >= 13)
            $cost = 4;
        
        if ( $page['player']->money < $cost )
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($mc_id);
            return;
        }
        
        // Must be allowed
        if (!isset($page['player']->may_build_elementals))
        {
            $this->page['error'] = 'You are not allowed to build elementals/battle armor!';
            $this->view($mc_id);
            return;
        }
        
        // Start construction!!!
        $page['player']->money = $page['player']->money - $cost;
        $this->playermodel->update($page['player']->player_id, $page['player']);
        
        $this->load->model('combatunitmodel');
        $elemental = new stdClass();
        $elemental->name = $page['player']->may_build_elementals;
        $elemental->prewar_strength = 2;
        $elemental->owner_id = $page['player']->player_id;
        $elemental->location_id = $page['factory']->location_id;
        $elemental->size = 1;
        $elemental->is_elemental = true;
        $elemental->being_built = true;
        $elemental->price_paid = $cost;
        $elemental->game_id = $page['player']->game_id;
        $this->combatunitmodel->create($elemental);
            
        game_message($page['game']->game_id, $page['player']->faction.' builds a '.$page['player']->may_build_elementals.' unit on '.$page['territory']->name.'.');
        $this->page['notice'] = 'Building '.$page['player']->may_build_elementals.' unit.';
        
        // Reload factory view
        $this->view($mc_id);   
        
    }  // end elemental
    
    /**
     * Build a jumpship
     * 
     * @param type $size
     * @param type $mc_id 
     */
    function jumpship($size=0, $mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $mc_id == 0 )
        {
            $page['error'] = 'No such factory.';
            $this->load->view('templatexml', $page);
            return;
        }
 
        // Fetch the factory
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['factory']->location_id);
        
        // Fetch the player who owns the factory
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($page['territory']->player_id);
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        // Production phase and player is phasing
        if ( $page['game']->player_id_playing != $page['player']->player_id || $page['game']->phase !="Production" )
         {
            $this->page['error'] = 'Not the right phase!';
            $this->view($mc_id);
            return;
        }   
        
        // Jumpship must be valid size
        if ( $size != 1 && $size != 2 && $size != 3 && $size != 5 )
        {
            if ($page['game']->use_extd_jumpships)
            {
                if ( $size != 7 && $size != 9 && $size != 12 )
                {
                    $this->page['error'] = 'You can\'t build a jumpship of that magnitude!';
                    $this->view($mc_id);
                    return;
                }
            }
            else
            {
                $this->page['error'] = 'You can\'t build a jumpship of that magnitude!';
                $this->view($mc_id);
                return;
            }
        }
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases, including building a jumpship, may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Player owned by signed in user
        if ( $this->ion_auth->get_user()->id != $page['player']->user_id )
        {
            $this->page['error'] = 'You are not playing in that game!';
            $this->view($mc_id);
            return;
        }    
        
        // Factory undamaged and not being built
        if ( $page['factory']->is_damaged || $page['factory']->being_built )
        {
            $this->page['error'] = 'You aren\'t allowed to build anything at this factory, let alone a jumpship!';
            $this->view($mc_id);
            return;
        }
        
        // Player must be able to afford it
        $prices = array(0,12,16,20,0,25,0,30,0,34,0,0,40);
        $cost = $prices[$size];
        if ( $page['player']->money < $cost )
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($mc_id);
            return;
        }
        
        // Start construction!!!
        $page['player']->money = $page['player']->money - $cost;
        $this->playermodel->update($page['player']->player_id, $page['player']);
        
        $this->load->model('jumpshipmodel');
        $jumpship = new stdClass();
        $jumpship->owner_id = $page['player']->player_id;
        $jumpship->location_id = $page['factory']->location_id;
        $jumpship->capacity = $size;
        $jumpship->being_built = true;
        $this->jumpshipmodel->create($jumpship);
            
        game_message($page['game']->game_id, $page['player']->faction.' builds a Jumpship '.$jumpship->capacity.' on '.$page['territory']->name.'.');
        $this->page['notice'] = 'Building Jumpship '.$jumpship->capacity;
        
        // Reload factory view
        $this->view($mc_id); 
    }
    
    /**
     * Upgrade a combat unit
     * 
     * @param type $unit_id 
     */
    function upgrade($unit_id = 0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if($unit_id == 0)
        {
            $page['error'] = 'No such factory!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Load unit, must exist
        $this->load->model('combatunitmodel');
        $unit = $this->combatunitmodel->get_by_id($unit_id);
        if(!isset($unit->combatunit_id))
        {
            $page['error'] = 'No such combat unit!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be located at a factory
        $this->load->model('factorymodel');
        $factory = $this->factorymodel->get_by_location($unit->location_id);
        if (!isset($factory->factory_id))
        {
            $page['error'] = 'No such manufacturing center!';
            $this->load->view('templatexml', $page);
            return;
        }
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($unit->location_id);
        
        if($unit->being_built)
        {
            $this->page['error'] = 'That combat unit is being built!';
            $this->view($factory->factory_id);
            return;
        }
           
        if ($unit->strength == 0)
        {
            $this->page['error'] = 'Can\'t upgrade a destroyed unit!';
            $this->view($factory->factory_id);
            return;
        }
        if ($unit->is_conventional)
        {
            $this->page['error'] = 'Can\'t upgrade a conventional unit!';
            $this->view($factory->factory_id);
            return;
        }
        if ($unit->is_elemental)
        {
            $this->page['error'] = 'Can\'t upgrade an elemental unit!';
            $this->view($factory->factory_id);
            return;
        }
        
        // Must be owned by this user/player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($unit->owner_id);
        if ($player->user_id != $page['user']->id)
        {
            $this->page['error'] = 'You do not own that combat unit!';
            $this->view($factory->factory_id);
            return;
        }        
        
        // Must be production
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        if ($game->phase != 'Production')
        {
            $this->page['error'] = 'Not the right phase!';
            $this->view($factory->factory_id);
            return;
        }
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($game->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($game->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No purchases, including upgrading units, may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be the active player
        if ($game->player_id_playing != $player->player_id)
        {
            $this->page['error'] = 'Not your turn! You can only open a periphery region for bidding on your own turn.';
            $this->view($factory->factory_id);
            return;
        }
        
        // Must have the money
        if ($player->money < 5)
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($factory->factory_id);
            return;
        }
        
        // Away we go
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money - 5;
        $this->playermodel->update($player->player_id, $playerupdate);
        
        $unitupdate = new stdClass();
        $unitupdate->combatunit_id = $unit->combatunit_id;
        $unitupdate->being_built = true;
        //$unitupdate->strength = 0;    // Don't lose track of the original strength
        $unitupdate->is_rebuild = true;
        $this->combatunitmodel->update($unit_id, $unitupdate);
        
        game_message($game->game_id, $player->faction.' upgrades '.$unit->name.' to strength 4 in '.$territory->name.'.');
        $this->page['notice'] = 'Upgrading '.$unit->name.'.';
        
        // Load factory view
        $this->view($factory->factory_id);
        
    }  // end upgrade
    
    /**
     * Repair a damaged manufacturing center
     * 
     * @param type $mc_id 
     */
    function repair($mc_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $mc_id == 0 )
        {
            $page['error'] = 'Error! Cannot repair a factory without a factory ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the MC
        $this->load->model('factorymodel');
        $page['factory'] = $this->factorymodel->get_by_id($mc_id);
        
        // Fetch the territory
        $this->load->model('territorymodel');
        $page['territory'] = $this->territorymodel->get_by_id($page['factory']->location_id);
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['territory']->game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {

                $page['is_playing'] = true;
                $page['player'] = $player;

            }
        }
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'Error! Cannot repair a factory when you are not playing.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must own the factory
        if ( $page['player']->player_id != $page['territory']->player_id )
        {
            $page['error'] = 'Error! Cannot repair a factory that is not your own.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be production phase
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        if ( $page['game']->phase != 'Production' )
        {
            $this->page['error'] = 'Error! Cannot repair a factory except during your Production phase.';
            $this->view($mc_id);
            return;
        }
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['game']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['game']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be the players turn
        if ( $page['game']->player_id_playing != $page['player']->player_id )
        {
            $this->page['error'] = 'Error! Cannot repair a factory except during your own Production phase.';
            $this->view($mc_id);
            return;
        }           
        
        // Should not already be under repair
        if ($page['factory']->being_repaired)
        {
            $this->page['error'] = 'Error! Cannot repair a factory that is already undergoing repairs.';
            $this->view($mc_id);
            return;
        } 
        
        // Must be able to afford it
        if ( $page['player']->money < 20 )
        {
            $this->page['error'] = 'You cannot afford to repair a factory.';
            $this->view($mc_id);
            return;
        }
        
        // Make repairs
        $playerupdate = new stdClass();
        $playerupdate->player_id = $page['player']->player_id;
        $playerupdate->money = $page['player']->money - 20;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        $factoryupdate = new stdClass();
        $factoryupdate->factory_id = $mc_id;
        $factoryupdate->being_repaired = true;
        $this->factorymodel->update($mc_id, $factoryupdate);
        
        $this->page['notice'] = 'Manufacturing Center repaired.';
        game_message($page['game']->game_id, $page['player']->faction.' begins repairs on the manufacturing center at '.$page['territory']->name.'.');
        
        // Back to the factory
        $this->view($mc_id);
    }
    
    /**
     * Cancel a construction in progress
     * Only allowed if money was actually spent on the item in question
     * Types:
     * 1 -- Combat Unit
     */
    public function cancel($factory_id=0, $unit_id=0, $type=0)
    {
        $page = $this->page;
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
        {
            redirect('auth/login', 'refresh');
        }
        
        $this->load->model('combatunitmodel');
        $unit = $this->combatunitmodel->get_by_id($unit_id);
        
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($unit->game_id);
        
        // Make sure the user is playing in the game
        // and also be the current player's turn
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game->game_id);
        $is_playing = false;
        foreach( $players as $p )
        {
            if ( $p->user_id == $page['user']->id )
            {
                $is_playing = true;
                $player = $p;
            }
        }        
        if ( !$is_playing )
        {
            $page['error'] = 'ERROR 1!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ( $player->player_id != $game->player_id_playing )
        {
            $page['error'] = 'ERROR 2!';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be the production phase
        if ( $game->phase != 'Production' )
        {
            $page['error'] = 'Error 3!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have actually paid money for the item in question
        if ($unit->price_paid == 0)
        {
            $page['error'] = 'Error 4!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!?!?
        // Start transaction
        $this->db->trans_start();
        
        if ( $unit->is_conventional || $unit->is_elemental)
        {
             $this->combatunitmodel->delete($unit->combatunit_id);
        }
        else
        {
             $unitu = new stdClass();
             $unitu->combatunit_id = $unit->combatunit_id;
             $unitu->strength = 0;
             $unitu->die = 0;
             $unitu->location_id = null;
             $unitu->last_roll = 0;
             $unitu->combine_with = null;
             $unitu->was_loaded = 0;
             $unitu->target_id = null;
             $unitu->combo_broken = 0;
             $unitu->price_paid = 0;
             $unitu->being_built = 0;

             $this->combatunitmodel->update($unit->combatunit_id, $unitu);
        }
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money + $unit->price_paid;
        $this->playermodel->update($player->player_id, $playerupdate);
        
        $this->page['notice'] = 'Production canceled.';
        game_message($game->game_id, $player->faction.' canceled production of '.$unit->name.'.');

        // Back to the factory
        $this->db->trans_complete();
        if ($factory_id != 0)
            $this->view($factory_id);
        else
            $this->load->view('template_xml');
            
        
    }  // end cancel
    
    /**
     * Make sure the user is allowed to perform the production action
     */
    private function check($game_id)
    {   
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( count($page['game']) != 1 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is playing in the game
        // and also be the current player's turn
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }        
        if ( !$page['is_playing'] )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ( $page['player']->player_id != $page['game']->player_id_playing )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }

        // Must be the production phase
        if ( $page['game']->phase != 'Production' )
        {
            $page['error'] = 'Error! Constructing a manufacturing facility can only be done in the Production phase.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        return $page;
    }  // end check

}  // end production controller
