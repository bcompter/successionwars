<?php

/*
 * Handles technology views and tech rolls
 * 
 * 
 */

class Technology extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    } 

    /**
     * View the player's technology rating.
     * 
     * @param type $player_id 
     */
    function view($player_id = 0)
    {
        $page = $this->page;
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the player
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($player_id);
        $page['players'] = $this->playermodel->get_by_game($page['player']->game_id);
        $page['is_playing'] = false;
        
        // Viewer owns this player (Don't have to be logged in)
        if ($player_id==$this->ion_auth->get_user()->id)
            $page['is_playing'] = true;    

        // Check if someone in the game is using elementals
        if ($this->playermodel->someone_using_elementals($page['player']->game_id))
            $page['player']->using_elementals = TRUE;
        else
            $page['player']->using_elementals = FALSE;
        
        // Fetch possible tech bonus targets
        $this->load->model('combatunitmodel');
        $this->db->order_by('name','asc');
        $page['targets'] = $this->combatunitmodel->get_by_tech_bonus($page['player']->player_id);
        
        // Away we go
        $page['content'] = 'technology';
        $this->load->view('templatexml', $page);
    }
    
    /**
     * Make a technology roll.
     * 
     * @param type $player_id 
     */
    function tech_roll($player_id = 0)
    {
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'No such player!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the player is owned by the current player
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($player_id);
        if ( $page['player']->user_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'No such player!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must have 5 million cbills
        if ( $page['player']->money < 5 )
        {
            $this->page['error'] = 'You can\'t afford it!';
            $this->view($page['player']->player_id);
            return;
        }
        
        // Game can't be on hold
        $this->load->model('cardmodel');
        $cardbeingplayed = $this->cardmodel->get_hold($page['player']->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $this->page['error'] = 'A '.$cardbeingplayed->title.' card being played needs to be resolved first.';
            $this->view($player_id);
            return;
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($page['player']->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No Purchases may be made while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Can't be in the middle of 'Merc bidding (Mercenary Phase does not have a card hold)
        $this->load->model('offermodel');
        $mercbeingbid = $this->offermodel->get_by_game_id($page['player']->game_id);
        if (count($mercbeingbid) > 0)
        {
            $this->page['error'] = 'Error! Cannot invest in tech while \'Mercs are up for bid.';
            $this->view($player_id);
            return;
        }
        
        // Away we go
        $playerupdate = new stdClass();
        $playerupdate->player_id = $page['player']->player_id;
        $playerupdate->money = $page['player']->money - 5;
        $this->playermodel->update($page['player']->player_id, $playerupdate);
        
        $dieroll = roll_dice(1,10);
        //game_message($page['player']->game_id, $page['player']->faction.' buys a Technology roll; rolls '.$dieroll.'.');
        
        if ( $dieroll == 10 )
        {
            $this->page['notice'] = 'Technology roll succeeded, +2 Technology!';
            game_message($page['player']->game_id, $page['player']->faction.' buys a Technology roll; rolls '.$dieroll.'; Success +2 Technology!');
            tech_mod ($page['player'], 2);
        }
        else if ( $dieroll > 6 )
        {
            $this->page['notice'] = 'Technology roll succeeded, +1 Technology!';
            game_message($page['player']->game_id, $page['player']->faction.' buys a Technology roll; rolls '.$dieroll.'; Success +1 Technology!');  
            tech_mod ($page['player'], 1);     
        }
        else
        {
            $this->page['error'] = 'Technology roll failed!';
            game_message($page['player']->game_id, $page['player']->faction.' buys a Technology roll; rolls '.$dieroll.'; Failed!');
        }
        
        $this->view($player_id);
        
    }  // end tech_roll
    
    
    /**
     * Give a +2 technology bonus to one 'Mech combat unit
     * 
     * One bonus at tech_level 7 and another at 12
     * 
     * @param type $player
     * @param type $target 
     */
    function tech_bonus($player_id=0, $target_id=0)
    {
        // Check input
        if ($player_id == 0 || $target_id == 0)
        {
            $page['error'] = 'No such target!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch page
        $page = $this->page;
        
        // Fetch player, logged in user must own this
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        if ($page['user']->id != $player->user_id)
        {
            $page['error'] = 'No logged in!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch target, must be owned by player
        $this->load->model('combatunitmodel');
        $unit = $this->combatunitmodel->get_by_unit_id($target_id);
        if ($unit->owner_id != $player->player_id)
        {
            $page['error'] = 'You don\'t own that combat unit!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be a mech unit
        if ($unit->is_conventional)
        {
            $page['error'] = 'You can\'t give Technology Combat Bonuses to conventional units!';
            $this->load->view('templatexml', $page);
            return;
        }

        // Cannot be a unit that has already been given a Technology bonus
        $this->load->model('combatbonusmodel');
        if ($this->combatbonusmodel->get_tech_by_unit($target_id))
        {
            $page['error'] = 'You can\'t give more than one Technology Combat Bonus to a single unit!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be allowed
        if ($player->tech_level < 7)
        {
            $page['error'] = 'Your technology is too low!';
            $this->load->view('templatexml', $page);
            return;
        }
        else if($player->tech_level < 12 && $player->tech_bonus > 0)
        {
            $page['error'] = 'You have already used all of your tech bonuses this turn!';
            $this->load->view('templatexml', $page);
            return;
        }
        else if($player->tech_bonus > 1)
        {
            $page['error'] = 'You have already used all of your tech bonuses this turn!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        $this->load->model('combatbonusmodel');
        $bonus = new stdClass();
        $bonus->game_id = $player->game_id;
        $bonus->ttl = 1;
        $bonus->value = 2;
        $bonus->combatunit_id = $unit->combatunit_id;
        $bonus->source_id = $player->player_id;
        $bonus->source_type = 2;
        $this->combatbonusmodel->create($bonus);
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player_id;
        $playerupdate->tech_bonus = $player->tech_bonus + 1;
        $this->playermodel->update($player_id, $playerupdate);
        
        game_message($player->game_id, $player->faction.' gives a Technology Bonus +2 to '.$unit->name.' in '.$unit->territory.'.');
        $this->page['notice'] = 'Technology bonus played on '.$unit->name.'.';
        $this->view($player_id);
        
    }  // end tech_bonus
}  // end technology