<?php
 class Player extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }  
  
    /**
     * View all players in the game
     * 
     * @param type $game_id 
     */
    function view_all($game_id=0)
    {
        // Make sure an id is provided
        if ($game_id == 0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
               
        // fetch allplayers in the game
        $page = $this->page;
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['game_id'] = $game_id;
        
        $this->load->view('viewplayers', $page);
    }
    
    function view($player_id=0)
    {
        $page = $this->page;
                
        // Make sure an id is provided
        if ($player_id == 0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
               
        // fetch the player in the game
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($page['player']->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // fetch the user
        $page['playeruser'] = $this->ion_auth_model->get_user($page['player']->user_id)->row();
        
        // Fetch the number of cards in their hand
        $this->load->model('cardmodel');
        $cards = $this->cardmodel->get_by_player($player_id);
        $page['player']->cards = count($cards);
        
        // Military strength
        $page['military'] = $this->db->query('select sum(strength) as strength from combatunits where owner_id='.$player_id.' and strength>0 order by owner_id')->row()->strength;
        if ($page['military']==NULL)
            $page['military']=0;        
                // Mech strength
        $page['total_mech_strength'] = $this->db->query('select sum(strength) as strength from combatunits where owner_id='.$player_id.' and strength>0 and is_conventional=0')->row()->strength;
        if ($page['total_mech_strength']==NULL)
            $page['total_mech_strength']=0;   
                // Non-Merc strength
        $page['total_nmerc_strength'] = $this->db->query('select sum(strength) as strength from combatunits where owner_id='.$player_id.' and strength>0 and is_merc=0 and is_conventional=0')->row()->strength;
        if ($page['total_nmerc_strength']==NULL)
            $page['total_nmerc_strength']=0;  
                // Merc strength
        $page['total_merc_strength'] = $this->db->query('select sum(strength) as strength from combatunits where owner_id='.$player_id.' and strength>0 and is_merc=1 and is_conventional=0')->row()->strength;
        if ($page['total_merc_strength']==NULL)
            $page['total_merc_strength']=0;  
                // Conventional strength
        $page['total_conventional_strength'] = $this->db->query('select sum(strength) as strength from combatunits where owner_id='.$player_id.' and strength>0 and is_conventional=1')->row()->strength;
        if ($page['total_conventional_strength']==NULL)
            $page['total_conventional_strength']=0;   
                // Leader Combat strength
        $page['total_leader_combat'] = $this->db->query('select sum(combat) as combat from leaders where controlling_house_id='.$player_id.' and allegiance_to_house_id='.$player_id)->row()->combat;
        if ($page['total_leader_combat']==NULL)
            $page['total_leader_combat']=0;
        // Leader Admin
        $this->load->model('leadermodel');
        if ($this->debug>2) log_message('error', 'get admin total');
        $page['total_leader_admin'] = $this->leadermodel->get_admin_tax($player_id);
        if ($this->debug>2) log_message('error', 'GOT admin total');
        $page['negative_leader_admin'] = $this->leadermodel->get_negative_admin_tax($player_id);
        
        // Jumpship Fleet
        $page['capacity'] = $this->db->query('select sum(capacity) as capacity from jumpships where owner_id='.$player_id)->row()->capacity;
        if ($page['capacity']==NULL)
            $page['capacity']=0;
        // Jumpship Fleet
        $page['js5'] = $this->db->query('select sum(capacity) as capacity from jumpships where capacity=5 and owner_id='.$player_id)->row()->capacity;
        if ($page['js5']==NULL)
            $page['js5']=0;
        else $page['js5'] /=5;
        $page['js3'] = $this->db->query('select sum(capacity) as capacity from jumpships where capacity=3 and owner_id='.$player_id)->row()->capacity;
        if ($page['js3']==NULL)
            $page['js3']=0;
        else $page['js3'] /=3;
        $page['js2'] = $this->db->query('select sum(capacity) as capacity from jumpships where capacity=2 and owner_id='.$player_id)->row()->capacity;
        if ($page['js2']==NULL)
            $page['js2']=0;
        else $page['js2'] /=2;        
        $page['js1'] = $this->db->query('select sum(capacity) as capacity from jumpships where capacity=1 and owner_id='.$player_id)->row()->capacity;
        if ($page['js1']==NULL)
            $page['js1']=0;
        
        if ($page['player']->original_capital != $page['player']->official_capital)
        {
            
            $page['player']->official_capital_territory = $this->db->query('SELECT `map`.`name` FROM  `players` JOIN `territories` on `territories`.`territory_id`=`players`.`official_capital` JOIN `map` on `map`.`map_id`=`territories`.`map_id` WHERE  `players`.`player_id` ='.$page['player']->player_id)->row()->name;
        } 
        
        // Tax revenue
        $page['taxes'] = $this->db->query('select sum(resource) as taxes from territories join map on map.map_id=territories.map_id where player_id='.$player_id.' group by player_id')->row();
        
        // Leaders
        $this->load->model('leadermodel');
        $page['leaders'] = $this->leadermodel->get_all_by_player($player_id);
        
        if (isset($page['taxes']->taxes))
            $page['taxes'] = $page['taxes']->taxes;
        else
            $page['taxes'] = 0;
        
        // Capitals
        $page['num_caps'] = $this->db->query('SELECT sum(is_capital) as num FROM territories WHERE player_id='.$player_id.' AND is_capital=1')->row()->num;
        
        // Regionals
        $page['num_regional'] = $this->db->query('SELECT sum(is_regional) as num FROM territories WHERE player_id='.$player_id.' AND is_regional=1')->row()->num;
        
        // Factories
        $this->load->model('factorymodel');
        $factories = $this->factorymodel->get_by_player($player_id);
        $page['num_factories'] = count($factories);
        
        // Victory conditions
        $this->load->model('victorymodel');
        $page['conditions'] = $this->victorymodel->get_by_player($player_id);
        
        $page['game_id'] = $page['player']->game_id;
        $page['content'] = 'viewplayer';
        $this->load->view('templatexml', $page);
    }
    
    /**
     *  Give cbills to another player
     * @param type $player_id The player to give to
     * @param type $cbills  The amount of cbills to give
     */
    function trade_cbills($player_id = 0, $cbills = 0)
    {
        // Make sure variables are provided
        if ($player_id == 0 )
        {
            $page['error'] = 'Error! Your player ID was lost.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ($cbills == 0)
        {
            // Get target
            $this->load->model('playermodel');
            $target = $this->playermodel->get_by_id($player_id);
            
            if (!isset($target->player_id))
            {
                $page['error'] = 'Target player not found!';
                $this->load->view('templatexml', $page);
                return;
            }
            
            $page['player'] = $target;
            
            $this->load->view('trade_cbills', $page);
            return;
        }
        
        // Target must exist
        $this->load->model('playermodel');
        $target = $this->playermodel->get_by_id($player_id);
        if (!isset($target->player_id))
        {
            $page['error'] = 'Error! Target player not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        if ($target->turn_order == 0)
        {
            $page['error'] = 'Error! Target player has been eliminated.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $player;    // This player, the player giving the money
        $players = $this->playermodel->get_by_game($target->game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $this->page['user']->id)
                $player = $p;
        }        
        if (!isset($player->player_id))
        {
            $page['error'] = 'Please login.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // cbills must be a non negative number
        if (!is_numeric($cbills))
        {
            $page['error'] = 'Try entering a number.';
            $this->load->view('templatexml', $page);
            return;
        }
        if ($cbills <= 0)
        {
            $page['error'] = 'You can\'t give negative CBills!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // cbills must be an integer
        if (!ctype_digit($cbills))
        {
            $page['error'] = 'You can\'t give portions of a MM CBills.  The smallest amount tracked is 1,000,000 Cbills.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // player must have the money available
        $mybid=0;
        $this->load->model('offermodel');
        $mb = $this->offermodel->get_by_player_id($player->player_id);
        if ($mb->offer > $mybid)
            $mybid = $mb->offer;

        $this->load->model('peripherymodel');
        $mb = $this->peripherymodel->get_by_player_id($player->player_id);
        if ($mb->offer > $mybid)
            $mybid = $mb->offer;

        if (($player->money - $mybid) < $cbills)
        {
            if ($cbills>0)
                $page['error'] = 'You can\'t afford to give that much with your current bid!';
            else
                $page['error'] = 'You can\'t afford it!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go...
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money - $cbills;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        $playerupdate->player_id = $target->player_id;
        $playerupdate->money = $target->money + $cbills;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        // Load view?
        $this->page['notice'] = 'You gave '.$cbills.' MM CBills to '.$target->faction.'.';
        game_message($player->game_id, $player->faction.' gave '.$cbills.' cbills to '.$target->faction.'.');
        $this->view($player_id);

    }  // end trade cbills

    /**
     * Vote for a new game owner
     * This is the landing page for voting, shows all bids and allows 
     */
    function vote($game_id=0)
    {
        $page = $this->page;
        
        // Make sure variables are provided
        if ($game_id == 0 )
        {
            $page['error'] = 'Your game ID was not found!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'Your game was not found!';
            $this->load->view('template', $page);
            return;
        }
        $page['owner'] = $this->ion_auth->get_user($game->creator_id);
        
        // Fetch this users player, must be playing in the game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        $playing_in_game = false;
        foreach($players as $p)
        {
            if ($p->user_id = $page['user']->id)
            {
                $player = $p;
                $playing_in_game = true;
                break;
            }
        }
        if (!$playing_in_game)
        {
            $page['error'] = 'You are not playing in that game!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go!
        
        // Fetch all existing votes
        $this->load->model('votemodel');
        $page['votes'] = $this->votemodel->get_by_game($game_id);
        
        // Add in the list of players
        $page['players'] = $players;
        
        $page['player'] = $player;
        
        $page['game'] = $game;
        
        // Show the view
        $page['content'] = 'votes_view';
        $this->load->view('template', $page);
        
        
    }  // end vote
    
    /**
     * Vote for a new game owner
     * Must have unanimous vote of non-eliminated players to transfer ownership
     * If $new_go_id = -1, the action should be to delete the vote
     * Otherwise a new vote is created or an existing one updated
     */
    function vote_for_go($game_id=0, $new_go_id=0)
    {
        $page = $this->page;
        
        // Make sure variables are provided
        if ($new_go_id == 0 )
        {
            $page['error'] = 'Your player ID was not found!';
            $this->load->view('template', $page);
            return;
        }
        if ($game_id == 0 )
        {
            $page['error'] = 'Your game ID was not found!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'Your game was not found!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch the target player
        $this->load->model('playermodel');
        $target = $this->playermodel->get_by_id($new_go_id);
        if (!isset($target->player_id))
        {
            $page['error'] = 'Your target player was not found!';
            $this->load->view('template', $page);
            return;
        }
        
        // Target player must not be eliminated
        if ($target->turn_order == 0)
        {
            $page['error'] = 'You can\'t vote for an eliminated player!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch this users player, must be playing in the game
        $players = $this->playermodel->get_by_game($game_id);
        $playing_in_game = false;
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
            {
                $player = $p;
                $playing_in_game = true;
                break;
            }
        }
        if (!$playing_in_game)
        {
            $page['error'] = 'You are not playing in that game!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must not be eliminated yourself
        if ($player->turn_order == 0)
        {
            $page['error'] = 'You can\'t vote for a new game owner if eliminated from the game!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch this user's vote if one exists
        $this->load->model('votemodel');
        $current_votes = $this->votemodel->get_by_player_game($player->player_id, $game_id);
        if (count($current_votes) > 1)
        {
            $page['error'] = 'Something\'s gone wrong and I\'m not sure what.';
            $this->load->view('template', $page);
            return;
        }
        foreach($current_votes as $vote)
        {
            $current_vote = $vote;
        }
        if (!isset($current_vote->player_id))
        {
            unset($current_vote);
        }
        
        // Away we go!
        if (isset($current_vote))
        {
            // Update the existing vote
            $vote_update = new stdClass();
            $vote_update->target_id = $new_go_id;
            $this->votemodel->update($current_vote->vote_id, $vote_update);
        }
        else
        {
            // Create a new vote
            $new_vote = new stdClass();
            $new_vote->player_id = $player->player_id;
            $new_vote->game_id = $game_id;
            $new_vote->target_id = $new_go_id;
            $this->votemodel->create($new_vote);
        }
        
        // Write game message
        $target_user = $this->db->query('SELECT username FROM users WHERE id='.$target->user_id)->row();
        game_message($game_id, $player->username.' has voted for '.$target_user->username.' for Game Owner.');
        
        // Are all of the votes in?
        $num_players = count($players);
        $num_eliminated = 0;
        foreach($players as $p)
        {
            if ($p->turn_order == 0)
                $num_eliminated++;
        }
        $num_required_votes = $num_players - $num_eliminated - 1;
        if ($num_required_votes < 1)
        {
            $page['error'] = 'Something\'s gone wrong and I\'m not sure what...';
            $this->load->view('template', $page);
            return;
        }
        $current_votes = $this->votemodel->get_by_game($game_id);
        if (count($current_votes) >= $num_required_votes)
        {
            $votes_by_player;
            foreach($current_votes as $v)
            {
                if (!isset($votes_by_player[$v->target_id]))
                    $votes_by_player[$v->target_id] = 1;
                else
                    $votes_by_player[$v->target_id] += 1;
            }
            foreach($players as $p)
            {
                if (isset($votes_by_player[$p->player_id]) && $votes_by_player[$p->player_id] >= $num_required_votes)
                {
                    // We have a new gameowner!
                    $this->db->query('DELETE from game_owner_votes where game_id='.$game_id);
                    game_message($game_id, 'New Game Owner has been voted in!');
                    
                    $gameupdate = new stdClass();
                    $game_update->game_id = $game_id;
                    $game_update->creator_id = $p->user_id;
                    $this->gamemodel->update($game_id, $game_update);
                }
                
            }  // end foreach($players as $p)
            
        }  // if (count($current_votes) >= $num_required_votes)
        
        $this->session->set_flashdata('notice', 'You\'re vote has been Submitted!');
        redirect('player/vote/'.$game_id, 'redirect');
        
    }  // end vote_for_go
    
    /**
     * View a players entire military status
     */
    public function military($player_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ($player_id == 0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
               
        // fetch the player in the game
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($page['player']->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->load->model('combatunitmodel');
        $page['combatunits'] = $this->combatunitmodel->get_by_player_all($player_id);
        $page['game_id'] = $page['player']->game_id;
        $page['content'] = 'player_military';
        $this->load->view('templatexml', $page);
    }  // end military
    
    /**
     * Save notes for a player
     */
    public function save_notes($player_id = 0)
    {
        $page = $this->page;
        
        // Make sure variables are provided
        if ($player_id == 0 )
        {
            $page['error'] = 'Your player ID was not found!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the player and make sure it exists
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        if (!isset($player->player_id))
        {
            $page['error'] = 'No such player!';
            $this->load->view('templatexml', $page);
            return;
        } 
        
        // Make sure this user actually owns this player
        if ($page['user']->id != $player->user_id)
        {
            $page['error'] = 'Not your player!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        // Check input
        $notes = $this->input->post('notes');
        if ($notes === false)
        {
            $page['error'] = 'WTF?!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $playerupdate->player_id = $player_id;
        $playerupdate->notes = $notes;
        $this->playermodel->update($player_id, $playerupdate);
        $page['notice'] = 'Notes have been saved!';
        $this->load->view('templatexml', $page);
        
    }  // end save_notes
    
    
 }  // end player controller
?>
