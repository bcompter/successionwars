<?php

/*
 * Comstar
 * 
 * 
 */

class Comstar extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }    
    
    /**
     * View comstar, links to bribe
     * 
     * @param type $game_id 
     */
    function view( $game_id=0 )
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'Error: No game ID provided.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Fetch players
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $user = $this->ion_auth->get_user();
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $user->id )
            {
                $page['player'] = $player;
            }
        }
        
        // Fetch territories
        $this->load->model('territorymodel');
        $this->db->order_by('name');
        $page['territories'] = $this->territorymodel->get_by_game($game_id);
        
        // Must be playing in the game
        if ( !isset($page['player']) )
        {
            $page['error'] = 'Error: You are not playing in that game.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game must be set to allow Comstar
        if (!$page['game']->use_comstar)
        {
            $page['error'] = 'Comstar is not available in this game.  Sorry.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // away we go
        $page['content'] = 'comstar';
        $this->load->view('templatexml', $page);
    }

    /**
     * Bribe Comstar to interdict a territory
     * 
     * @param type $player_id The player making the attempt
     * @param type $target_id The target player
     * @param type $territory_id The target territory 
     */
    function bribe( $player_id = 0, $target_id = 0, $territory_id = 0 )
    {
        // Make sure ids are provided
        if ( $target_id == 0)
        {
            $page['error'] = 'Error: No Player Selected.';
            $this->load->view('templatexml', $page);
            return;
        }
        if ($territory_id == 0 )
        {
            $page['error'] = 'Error: No Region Selected.';
            $this->load->view('templatexml', $page);
            return;
        }
        if ( $player_id == 0)
        {
            $page['error'] = 'Error: Player id invalid.  Please check that you are logged in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('playermodel');
        $this->load->model('territorymodel');
        $this->load->model('gamemodel');

        $player = $this->playermodel->get_by_id($player_id);
        $target = $this->playermodel->get_by_id($target_id);
        $territory = $this->territorymodel->get_by_id($territory_id);
        $game = $this->gamemodel->get_by_id($player->game_id);
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($game->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game must be set to allow Comstar
        if (!$game->use_comstar)
        {
            $page['error'] = 'Comstar is not available in this game.  Sorry.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // player must be owned by this user
        if ( $player->user_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'Error: Invalid player ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // target must be in the same game
        if ( $player->game_id != $target->game_id )
        {
            $page['error'] = 'Error: Invalid target.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // territory in the same game
        if ( $territory->game_id != $player->game_id )
        {
            $page['error'] = 'Error: Invalid game ID';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // player and target cant be the same
        if ( $player_id == $target_id )
        {
            $page['error'] = 'You can\'t bribe yourself!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be able to afford it
        if ( $player->money < 5 )
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($player->game_id);
            return;
        }
        
        // can't already be interdicted
        $this->load->model('combatbonusmodel');
        $bonus = $this->combatbonusmodel->get_by_player_territory($target_id, $territory->territory_id);
        foreach($bonus as $b)
        {
            if ($b->source_type == 0)
            {
                if ($b->value == -2)
                {
                    $this->page['error'] = 'Target player already has an Interdict in effect on '.$territory->name.'!';
                    $this->view($player->game_id);
                    return;
                }
            }
        }
        unset($bonus);
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($player->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Pay the fee
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money - 5;
        $this->playermodel->update($player_id, $playerupdate);
        
        // ROll the dice!
        
        $roll = roll_dice(1,10);
        
        $bonus = new stdClass();
        $bonus->game_id = $player->game_id;
        $bonus->source_type = 0;        // COMSTAR
        $bonus->source_id = 0;
        $bonus->value = -2;
        $bonus->ttl = 5;
        
        if ( $roll < 4 )
        {
            // interdict player!  oops!
            game_message($player->game_id, $player->faction.' pays a bribe to Comstar. Comstar interdicts '.$player->faction.' in '.$territory->name.'.');
            
            $alreadyinterdicted = false;
            $bonuses = $this->combatbonusmodel->get_by_player_territory($player->player_id, $territory->territory_id);
            foreach($bonuses as $b)
            {
                if ($b->source_type == 0 && $bonus->value == -2)
                {
                    $alreadyinterdicted = true;
                }
            }
            
            
            if (!$alreadyinterdicted)
            {
                $bonus->player_id = $player->player_id;
                $bonus->location_id = $territory->territory_id;
                $this->combatbonusmodel->create($bonus);
            }
            
            $this->page['notice'] = 'Bribe paid.  Comstar interdicts you instead!';
        }
        else if ( $roll == 10 )
        {
            // interdict both!
            game_message($player->game_id, $player->faction.' pays a bribe to Comstar. Comstar interdicts '.$player->faction.' and '.$target->faction.' in '.$territory->name.'.');
            
            $alreadyinterdicted = false;
            $bonuses = $this->combatbonusmodel->get_by_player_territory($player->player_id, $territory->territory_id);
            foreach($bonuses as $b)
            {
                if ($b->source_type == 0 && $bonus->value == -2)
                {
                    $alreadyinterdicted = true;
                }
            }

            $bonus->player_id = $target->player_id;
            $bonus->location_id = $territory->territory_id;
            $this->combatbonusmodel->create($bonus);
            
            if (!$alreadyinterdicted)
            {
                $bonus->player_id = $player->player_id;
                $bonus->location_id = $territory->territory_id;
                $this->combatbonusmodel->create($bonus);
            }

            $this->page['notice'] = 'Bribe paid.  Comstar interdicts both instead!';
        }
        else
        {
            // interdict target!
            game_message($player->game_id, $player->faction.' pays a bribe to Comstar. Comstar interdicts '.$target->faction.' in '.$territory->name.'.');
            $bonus->player_id = $target->player_id;
            $bonus->location_id = $territory->territory_id;
            $this->combatbonusmodel->create($bonus);
            $this->page['notice'] = 'Bribe paid. Success!';
        }
  
        // back to comstar view
        $this->view($player->game_id);
    }
        
}