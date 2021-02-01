<?php

/*
 * This is the game dashboard.  This will be where you create new games or join
 * someone elses game.
 */
class Game extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }    
    
        
    function start($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
	if ($game_id == 0)
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }

	// Must be the game owner
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
	if ( isset($page['user']->id) && $game->creator_id != $page['user']->id )
	{
            $page['error'] = 'You do not own this game!';
            $this->load->view('templatexml', $page);
            return;
	}
        $page['game'] = $game;

	// Game must be in setup phase
	if ( $game->phase != 'Player Setup' )
	{
            $this->page['error'] = 'You must be in Player Setup phase to start a game!';
            $this->view($game_id);
            return;
	}
        
        // Must have the game built
        if ($game->built != 1)
        {
            $this->page['error'] = 'The game has not been built yet!';
            $this->view($game_id);
            return;
        }
        
        // Must have all units placed
        if (!$this->gamemodel->allUnitsPlaced($game_id))
        {
            $page['error'] = 'There are unplaced units in the game!';
            $this->load->view('template', $page);
            return;
        }
        if ($game->turn == 0)
        {
            // Draw initial hands
            // Generate starting income
            $this->load->model('playermodel');
            $this->load->model('territorymodel');
            $this->load->model('cardmodel');
            $players = $this->playermodel->get_by_game($game_id);
            $initialplayerid = 0;
            
            // Draw DC cards to house Liao
            $this->db->query('update cards
                join players on players.faction="Liao"
                set owner_id=players.player_id
                where players.game_id='.$game_id.' and
                cards.game_id = '.$game_id.' and
                type_id=3');
            
            foreach ($players as $player)
            {
                $resources = $this->territorymodel->get_by_player($player->player_id);
                $money = 0;
                foreach($resources as $resource)
                {
                    $money += $resource->resource;
                }

                // Add up admin of leaders
                $this->load->model('leadermodel');
                $leaders = $this->leadermodel->get_by_player($player->player_id);
                foreach( $leaders as $leader )
                {
                    $money += $leader->admin;
                }

                if ($this->debug>2) log_message('error', 'Taxes... '.$money.' to '.$player->faction);
                $playerupdate = new stdClass();
                $playerupdate->player_id = $player->player_id;
                $playerupdate->money = $money;
                $this->playermodel->update($player->player_id, $playerupdate);

                $maxhand = 4;
                
                $cards = $this->cardmodel->get_by_player($player->player_id);
                if ($this->debug>2) log_message('error', 'Doing card draws for '.$player->faction.': '.count($cards));

                for ( $i = count($cards); $i < $maxhand; $i++)
                {
                    if ($this->debug>2) log_message('error', 'Drawing a card!');
                    $card = $this->cardmodel->draw($game_id);
                    $card->owner_id = $player->player_id;
                    $this->cardmodel->update($card->card_id, $card);    
                }

                if ($player->turn_order == 1)
                    $initialplayerid = $player->player_id;
            }

            // Change game phase and turn
            // Set player id playing
            $game->turn = 1;
            
            if (!$game->use_merc_phase)
                $game->phase = 'Draw';
            
            $game->player_id_playing = $initialplayerid;
            
            game_message($game_id, 'The game has started!  Good luck to all players!  No guts no galaxy!');
        }
        else
        {
            if ($this->debug > 3) log_message('error', 'Unknown code state in game controller.  Start game when turn > 0');
        }
        
        $this->gamemodel->update($game_id, $game);
        email_game($game->game_id, 'The game '.$game->title.' has started!  <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game_id.'">');
        
        if ($game->use_merc_phase)
            redirect('sw/trigger_merc_phase/'.$game_id, 'refresh');
        else
        {
            // Redirect to view
            $this->session->set_flashdata('notice', 'Game Started!');
            redirect('game/view/'.$game_id, 'refresh');
        }
    }
    
    function playersetup($game_id=0)
    {
        // Make sure an id is provided
	if ($game_id == 0)
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }

	// Must be the game owner
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
	if ( $game->creator_id != $this->ion_auth->get_user()->id )
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
	}
        $page['game'] = $game;

	// Game must be in setup phase
	if ( $game->phase != 'Setup' )
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
	}
        
        // Must have all player slots occupied
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        foreach($players as $p)
        {
            if ($p->user_id == null)
            {
                $this->page['error'] = 'Not all players are filled!';
                $this->view($game_id);
                return;
            }
        }
        
        if ($game->turn == 0)
        {
            // Change game phase
            $game->phase = 'Player Setup';
            $this->gamemodel->update($game_id, $game);

            game_message($game_id, 'Welcome to the game.  The map is open for unit placement at this time.');
            $this->session->set_flashdata('notice', 'Game is now ready for Player Setup!');
        }
        else
        {
            // Change game phase
            $game->phase = $game->previous_phase;
            $this->gamemodel->update($game_id, $game);

            game_message($game_id, 'The game has resumed.');
            email_game($game_id, 'The game '.$game->title.' has resumed! <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game_id.'">');
            $this->session->set_flashdata('notice', 'Game has resumed!');
        }
        
        // Redirect to view
        
        redirect('game/view/'.$game_id, 'refresh');
        
    }
    
    /**
     * Place units during setup or mercenary phase
     * 
     * @param type $game_id The game id
     * @param type $type    The type of unit to be placed, 1 = combat unit, 2 = jumpship, 3 = leader
     * @param type $id      ID of the unit
     * @param type $location Location to place the unit
     */
    function place($game_id=0, $type=0, $id=0, $location=0) 
    {
        $page = $this->page;
        // Make sure an id is provided
	if ($game_id == 0)
	{
            $page['error'] = 'No such game!';
            $this->load->view('templatexml', $page);
            return;
	}

        // Must be playing in the game
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);	
        $page['game'] = $game;

	// Game must be in setup phase
	if ( $game->phase != 'Player Setup' && $game->phase != 'Mercenary Phase' )
	{
            $page['error'] = 'Wrong phase!';
            $this->load->view('templatexml', $page);
            return;
	}
        
        // Fetch the requesting player
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($page['game']->game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        
        // Must be playing in the game
        if (!$page['is_playing'])
        {
            $page['error'] = 'You are not playing in that game!';
            $this->load->view('templatexml', $page);
            return;
        }
         
        $this->load->model('jumpshipmodel');
        $this->load->model('combatunitmodel');
        $this->load->model('leadermodel');
        $this->load->model('territorymodel');
        
        if ($type == 0)
        {
            // Fetch all combat units and jumpships and leaders for this player
            $page['combatunits'] = $this->combatunitmodel->get_not_placed($page['player']->player_id);
            $page['jumpships'] = $this->jumpshipmodel->get_not_placed($page['player']->player_id);
            $page['leaders'] = $this->leadermodel->get_not_placed($page['player']->player_id);

            $page['content'] = 'placement';
            $this->load->view('templatexml', $page);
        }
        else if ($type !=0 && $id != 0 && $location == 0)
        {
            // Pick a location
            
            $this->db->order_by('name');
            $page['locations'] = $this->territorymodel->get_by_player($page['player']->player_id);
            
            if ($type == 1)
            {
                $page['combatunit'] = $this->combatunitmodel->get_by_id($id);
                // must own the unit
                if ($page['player']->player_id != $page['combatunit']->owner_id)
                {
                    $page['error'] = 'You don\'t own that unit!';
                    $this->load->view('templatexml', $page);
                    return;
                }
            }
            else if ($type == 2)
            {
                $page['jumpship'] = $this->jumpshipmodel->get_by_id($id);
                // must own the jumpship
                if ($page['player']->player_id != $page['jumpship']->owner_id)
                {
                    $page['error'] = 'You don\'t own that unit!';
                    $this->load->view('templatexml', $page);
                    return;
                }
            }
            else if ($type == 3)
            {
                $page['leader'] = $this->leadermodel->get_by_id_to_place($id);

                // must own the leader
                if ($page['player']->player_id != $page['leader']->controlling_house_id)
                {
                    $page['error'] = 'You don\'t own that leader!';
                    $this->load->view('templatexml', $page);
                    return;
                }
            }
            else
            {
                // Bad type
                $page['error'] = 'ERROR! Bad Type.';
                $this->load->view('templatexml', $page);
                return;
            }
            
            $page['content'] = 'placement2';
            $this->load->view('templatexml', $page);
        }
        else if ($type !=0 && $id != 0 && $location != 0)
        {
            // Place the unit!
            // Must be the phasing player
            if ($page['game']->player_id_playing != $page['player']->player_id && $page['game']->phase != 'Mercenary Phase')
            {
                $this->page['error'] = 'It is not your turn!';
                $this->place($page['game']->game_id);
                return;
            }
            
            $territory = $this->territorymodel->get_by_id($location);
            // must own territory
            if ($page['player']->player_id != $territory->player_id)
                {
                    $page['error'] = 'You don\'t own that unit!';
                    $this->load->view('templatexml', $page);
                    return;
                }
            
            if ($type == 1)
            {
                $combatunit = $this->combatunitmodel->get_by_id($id);
                // must own the unit
                if ($page['player']->player_id != $combatunit->owner_id)
                {
                    $page['error'] = 'You don\'t own that unit!';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                // Combat unit must not be already placed
                if ($combatunit->location_id != null)
                {
                    $page['error'] = 'That unit is already placed on the map!';
                    $this->load->view('templatexml', $page);
                    return;
                }
                $unitupdate = new stdClass();
                $unitupdate->combatunit_id = $combatunit->combatunit_id;
                $unitupdate->location_id = $location;
                $this->combatunitmodel->update($id,$unitupdate);
                
                // Update territory last_update
                update_territory($location);
                game_message($game_id, $page['player']->faction.' placed '.$combatunit->name.' ('.$combatunit->strength.') in '.$territory->name.'.');
                
                if ($page['game']->phase == 'Mercenary Phase')
                {
                    // Advance the turn to production if all leaders and combat units are placed
                    $unplacedleaders = $this->leadermodel->get_not_placed($page['player']->player_id);
                    $unplacedunits = $this->combatunitmodel->get_not_placed($page['player']->player_id);
                    
                    if (count($unplacedleaders) == 0 && count($unplacedunits) == 0)
                    {
                        $gameupdate                 = new stdClass();
                        $gameupdate->phase          = 'Production';
                        $gameupdate->game_id        = $game_id;
                        $gameupdate->last_action    = null;
                        $this->gamemodel->update($game_id, $gameupdate);
                        
                        // Send email to the phasing player
                        email_player($page['game']->player_id_playing, 'A Mercenary unit in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                                $game->title.
                                '</a>.  has been placed and your turn has resumed.');
                        
                        $page['notice'] = 'Combat unit placed.';
                        $this->load->view('templatexml', $page);
                    }
                    else
                    {
                        $this->session->set_flashdata('notice','Combat unit placed.');
                        redirect('sw/mercs/'.$game_id);
                    }
                    
                }
                else
                {
                    $this->page['notice'] = 'Combat unit placed.';
                    $this->place($game_id);
                }
                return;
            }
            else if ($type == 2)
            {
                $jumpship = $this->jumpshipmodel->get_by_id($id);
                // must own the jumpship
                if ($page['player']->player_id != $jumpship->owner_id)
                {
                    $page['error'] = 'You don\'t own that unit!';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                $unitupdate = new stdClass();
                $unitupdate->jumpship_id = $id;
                $unitupdate->location_id = $location;
                $this->jumpshipmodel->update($id,$unitupdate);
                
                // Update territory last_update
                update_territory($location);
                
                game_message($game_id, $page['player']->faction.' placed Jumpship ('.$jumpship->capacity.') in '.$territory->name.'.');
                $this->page['notice'] = 'Jumpship placed.';
                $this->place($game_id);
                return;
            }
            else if ($type == 3)
            {
                $page['leader'] = $this->leadermodel->get_by_id_to_place($id);

                // must own the leader
                if ($page['player']->player_id != $page['leader']->controlling_house_id)
                {
                    $page['error'] = 'You don\'t own that leader!';
                    $this->load->view('templatexml', $page);
                    return;
                }
                
                $unitupdate = new stdClass();
                $unitupdate->leader_id = $id;
                $unitupdate->location_id = $location;
                $this->leadermodel->update($id,$unitupdate);
                
                // Update territory last_update
                update_territory($location);
                game_message($game_id, $page['player']->faction.' placed '.$page['leader']->name.' in '.$territory->name.'.');
                
                if ($page['game']->phase == 'Mercenary Phase')
                {
                    // Advance the turn to production if all leaders and combat units are placed
                    $unplacedleaders = $this->leadermodel->get_not_placed($page['player']->player_id);
                    $unplacedunits = $this->combatunitmodel->get_not_placed($page['player']->player_id);
                    
                    if (count($unplacedleaders) == 0 && count($unplacedunits) == 0)
                    {                    
                        $gameupdate->phase = 'Production';
                        $gameupdate->game_id = $game_id;
                        $this->gamemodel->update($game_id, $gameupdate);
                        $page['notice'] = 'Leader placed.';
                        $this->load->view('templatexml', $page);
                    }
                    else
                    {
                        $this->session->set_flashdata('notice','Leader placed.');
                        redirect('sw/mercs/'.$game_id);
                    }
                    
                }
                else
                {
                    $this->page['notice'] = 'Leader placed.';
                    $this->place($game_id);
                }
                return;
            }
            else
            {
                // Bad type
                $page['error'] = 'ERROR!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        
    }
    
    /**
     *
     */
    function undeploy($type=0, $id=0)
    {
        $page = $this->page;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Make sure an id is provided
	if ($type == 0 || $id == 0)
	{
            $page['error'] = 'Invalid Input!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the unit in question, the game it exists in, and the player who owns it
        $this->load->model('gamemodel');
        $this->load->model('playermodel');
        if ($type == 1)
        {
            $this->load->model('combatunitmodel');
            $unit = $this->combatunitmodel->get_by_unit_id($id);
            $player = $this->playermodel->get_by_id($unit->owner_id);
            $game = $this->gamemodel->get_by_id($player->game_id);
        }
        else if ($type == 2)
        {
            $this->load->model('jumpshipmodel');
            $unit = $this->jumpshipmodel->get_by_id($id);
            $player = $this->playermodel->get_by_id($unit->owner_id);
            $game = $this->gamemodel->get_by_id($player->game_id);
        }
        else if ($type == 3)
        {
            $this->load->model('leadermodel');
            $unit = $this->leadermodel->get_by_id($id);
            $player = $this->playermodel->get_by_id($unit->controlling_house_id);
            $game = $this->gamemodel->get_by_id($unit->game_id);
        }
        else
        {
            $page['error'] = 'Invalid Input!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Unit must exist
        if (!isset($unit->location_id))
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game must exixt
        if (!isset($game->game_id))
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be player setup phase
        if ($game->phase != 'Player Setup')
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Player must exist and be playing in this game and be owned by this user
        $players = $this->playermodel->get_by_game($game->game_id);
        $is_playing = false;
        foreach($players as $p)
        {
            if ($p->user_id == $page['user']->id)
            {
                $is_playing = true;
            } 
        }
        if (!$is_playing)
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        if  ($player->game_id != $game->game_id)
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be this players turn to setup
        if ($game->player_id_playing != $player->player_id)
        {
            $page['error'] = 'Error!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go!
        $this->load->model('territorymodel');
        $location = $this->territorymodel->get_by_id($unit->location_id);
        
        update_territory($location->territory_id);
        
        $unitupdate = new stdClass();
        $unitupdate->location_id = null;
        if ($type == 1)
        {
            $this->combatunitmodel->update($id, $unitupdate);
            game_message($game->game_id, $player->faction.' removed '.$unit->name.' from '.$location->name.'.');
        }
        else if ($type == 2)
        {
            $this->jumpshipmodel->update($id, $unitupdate);
            game_message($game->game_id, $player->faction.' removed Jumpship '.$unit->capacity.' from '.$location->name.'.');
        }
        else if ($type == 3)
        {
            $this->leadermodel->update($id, $unitupdate);
            game_message($game->game_id, $player->faction.' removed '.$unit->name.' from '.$location->name.'.');
        }

        $this->session->set_flashdata('notice', 'Unit Undeployed');
        redirect('sw/location/'.$unit->location_id);
        
    }  // end undeploy
    
    /**
     * Game dashboard
     * List all games the user is participating in.
     * Short list of open games.  Link ot full open games list.
     * 
     */
    function index()
    {
        $page = $this->page;
        $this->load->model('gamemodel');
        
        // Must be logged in
        if (!isset($page['user']->id))
        {
            redirect('auth/login', 'refresh');
        }
        
        // Fetch games created or playing in
        $userid = 0;
        if ( $this->ion_auth->logged_in() )
            $userid = $this->ion_auth->get_user()->id;
        
        $this->db->limit(5);
        $page['gamescreated'] = $this->gamemodel->get_by_creator($userid);
        
        if ( $this->ion_auth->is_admin() )
        {
            $this->load->model('gamehelpmodel');
            $page['helpgames'] = $this->gamehelpmodel->get_active();
       
            $this->db->limit(5);
            $this->load->model('player_admin_swap_model');
            $page['gamesswapped'] = $this->player_admin_swap_model->get_gamesswapped_by_user_id($userid);
        }
        else
        {
            $page['helpgames'] = NULL;
            $page['gamesswapped'] = NULL;
        }
        
        $page['gamesplaying'] = $this->gamemodel->get_by_user($userid);
        
        //$dumbcounter = 0;  // <- I don't think this is used
        foreach ($page['gamesplaying'] as $playinggame)
        {
//log_message('error', 'Gampe.php function index() for game: '.$playinggame->game_id); // TEMPORARY DEBUG
            $playinggame->needsattention=0;     // Default to does NOT need attention.
            $playinggame->display = 1;          // Default to display
            
            // Is the game not full and you are the owner?
            if ($this->gamemodel->is_game_open($playinggame->game_id) && $playinggame->creator_id == $page['user']->id)
                $playinggame->needsattention=1;
            // Is the game over
            elseif ($playinggame->phase == 'Game Over')
                {}
            // Is buildstep == 0 && you're the owner?
            elseif ($playinggame->build_step == 0 && $playinggame->creator_id == $page['user']->id)
                $playinggame->needsattention=1;
            else // NESTED else statements to reduce checking once attention required is determined
            {
                // get user's player info for this $playinggame
                $this->load->model('playermodel');
                $player = $this->playermodel->get_by_game_and_user_id($playinggame->game_id, $page['user']->id);
                
                // Is player eliminated?
                if ($player->turn_order == 0)
                {
                    $playinggame->display = 0;
                }
                
                // Is it placement phase?  If so, is it your turn?                
                elseif ($playinggame->phase == 'Placement'  &&  $playinggame->player_id_playing == $player->player_id)
                    $playinggame->needsattention=1;
                else
                {
                    // Merc offer needed from player?
                    $this->load->model('offermodel');
                    $mercofferneeded = $this->offermodel->get_by_player_id($player->player_id);
                    
                    if (is_countable($mercofferneeded) && count($mercofferneeded) > 0 && is_object($mercofferneeded) && $mercofferneeded->offer == NULL)  // I wanted to use isset() instead of count, but I'd just get an error
                    {
                        $playinggame->needsattention = 1;
                    }
                    else
                    {
                        // Periphery offer needed from player?
                        $this->load->model('peripherymodel');
                        $peripheryofferneeded = $this->peripherymodel->get_by_player_id($player->player_id);
                        if (isset($peripheryofferneeded) && is_countable(peripheryofferneeded) && count($peripheryofferneeded) > 0 && $peripheryofferneeded->offer == NULL)  // I wanted to use isset() instead of count, but I'd just get an error
                            $playinggame->needsattention = 1;
                        // If your turn OR combat, marked as done?
                        else if ($player->combat_done == 0 && ($playinggame->phase == 'Combat' || $playinggame->player_id_playing == $player->player_id))
                        {
                            // Check if a merc is up for bid and if your bid is already submitted
                            if (is_countable($mercofferneeded) && count($mercofferneeded) > 0 && $mercofferneeded->offer != NULL)
                                $playinggame->needsattention=0;
                            else
                                $playinggame->needsattention=1;
                        }
                        else
                            if ($playinggame->phase == 'Mercenary Phase')
                            {
                                $this->load->model('combatunitmodel');
                                $combatunitstoplace = $this->combatunitmodel->mercs_to_place($playinggame->game_id);
                                if (count ($combatunitstoplace) > 0)
                                    $playinggame->needsattention=1;
                                else
                                {
                                    $this->load->model('leadermodel');
                                    $leaderstoplace = $this->leadermodel->get_not_placed($player->player_id);
                                    if (count($leaderstoplace) > 0)
                                        $playinggame->needsattention=1;
                                }
                            }
                    }
                }
            }
        }
        
        // Fetch recent games
        $page['gamesrecent'] = $this->gamemodel->get_recent();
        
        // Fetch games requiring your action...
        $page['gamesaction'] = array();
        
        // Fetch number of trackers you have not voted on
        $this->load->model('bugmodel');
        $page['trackerstovote'] = $this->bugmodel->get_not_voted($page['user']->id);
        
        // Fetch all user bugs
        if (isset($page['user']->id))
        {
            $this->db->limit(10);
            $this->db->where('status !=', 'Completed');
            $page['user_bugs'] = $this->bugmodel->get_by_user($page['user']->id);
        }
        
        $page['content'] = 'dashboard';
        $this->load->view('template', $page);
    }
    
    /**
     * Create a new game
     */
    function create()
    {
        $page = $this->page;
        
        // Load required libraries
        $this->load->library('form_validation');
        $this->load->model('orderofbattlemodel');
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Only 3 game allowed per user unless that user is an admin
        $this->load->model('gamemodel');
        $games = $this->gamemodel->get_by_creator($this->page['user']->id);
        if (count($games) > 2)
        {
            if ($this->page['user']->group_id != 1 && $this->page['user']->group_id != 3)
            {
                $this->page['error'] = 'Only three games are allowed per user for the time being, sorry.';
                $this->index();
                return;
            }
        }
        
        // Validate form input
        $this->form_validation->set_rules('title', 'Game Title', 'required|max_length[40]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[200]');

        if ($this->form_validation->run() == true)
        { 
            // create the game
            $newgame['title'] = $this->input->post('title', true);
            $newgame['description'] = $this->input->post('description', true);
            $newgame['creator_id'] = $this->ion_auth->get_user()->id;
            $password = $this->input->post('password', true);
            if ($password!='')
                $newgame['password'] = $password;
            else
                $newgame['password'] = null;
            $newgame['orderofbattle'] = $this->input->post('orderofbattle', true);
            
            // Fetch OOB
            $this->load->model('orderofbattlemodel');
            $oob = $this->orderofbattlemodel->get_by_id($newgame['orderofbattle']);
            if (!isset($oob->orderofbattle_id))
            {
                $this->page['error'] = 'I could not find the Order of Battle you were looking for!';
                $this->index();
                return;
            }
            if ($oob->draft)
            {
                $this->page['error'] = 'Order of Battle is a draft!';
                $this->index();
                return;
            }
            
            $newgame['use_merc_phase']          = $oob->use_merc_phase;
            $newgame['auto_factory_dmg_mod']    = $oob->auto_factory_dmg_mod;
            $newgame['destroy_jumpships']       = $oob->destroy_jumpships;
            $newgame['capitals_to_win']         = $oob->capitals_to_win;
            $newgame['use_comstar']             = $oob->use_comstar;
            $newgame['use_terra_interdict']     = $oob->use_terra_interdict;
            $newgame['use_terra_loot']          = $oob->use_terra_loot;
            $newgame['year']                    = $oob->year;
            $newgame['phase'] = 'Setup';
            
            $this->load->model('gamemodel');
            $this->gamemodel->create($newgame);
            
            // Back to the dashboard
            $this->page['notice'] = 'Game created successfully.';
            $this->viewall();
        }
        else
        {   
            $page['oobs'] = $this->orderofbattlemodel->get_all();
            
            $page['title'] = array('name' => 'title',
                        'id' => 'title',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('title'),
            );
            $page['description'] = array('name' => 'description',
                        'id' => 'description',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('description'),
            );
            $page['password'] = array('name' => 'password',
                        'id' => 'password',
                        'type' => 'password',
                        'value' => $this->form_validation->set_value('password'),
            );
            
            $page['content'] = 'creategame';
            $this->load->view('template', $page);
        }

    }
    
    /**
     * Delete a game
     */
    function delete($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR! No game id provided!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must own the game or be an admin
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'No such game!';
            $this->load->view('template', $page);
            return;
        }
        
        if ($game->creator_id != $page['user']->id)
        {
            // Allow admins to delete
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'You don\'t own that game!';
                $this->load->view('template', $page);
                return;
            }
        }
        
        // Confirm!
        if ($this->session->flashdata('confirm') != 'YES')
        {
            $this->page['warning'] = 'You are about to delete this game!  Click on the delete link again if you are sure.';
            $this->session->set_flashdata('confirm', 'YES');
            
            // Determine source, so we know where to route the confirmation...
            $this->load->library('user_agent');
    		$refer =  $this->agent->referrer();
    			
    		// Redirect
    		if (strpos($refer, 'view_admin') === false)
    		{
    			$this->game_tools($game->game_id);
    		}
            else
            {
            	$this->view_admin($game->game_id);
            }
            
            
            return;
        }
        
        // Away we go...
        
        // Delete all cards
        $this->db->query('delete from cards where game_id='.$game_id);
        
        // Delete all leaders
        $this->db->query('delete from leaders where game_id='.$game_id);
        
        // Delete all jumpships
        $this->db->query('delete jumpships from jumpships
            join territories on territories.territory_id = jumpships.location_id
            where territories.game_id='.$game_id);
        
        // Delete all combat units
        $this->db->query('delete combatunits from combatunits
            join territories on territories.territory_id = combatunits.location_id
            where territories.game_id='.$game_id);
        
        // Delete all factories
        $this->db->query('delete factories from factories
            join territories on territories.territory_id = factories.location_id
            where game_id='.$game_id);
        
        // Delete all territories
        $this->db->query('delete from territories where game_id='.$game_id);
        
        // Delete all players
        $this->db->query('delete from players where game_id='.$game_id);
        
        // Delete the game...
        $this->db->query('delete from games where game_id='.$game_id);
        
        // Back to the dashboard
        $this->session->set_flashdata('notice','Game deleted!');
        redirect('game','refresh');
        
    } // end delete
    
    /**
     * View a game
     * @param type $game_id 
     */
    function view($game_id=0)
    {
        $page = $this->page;
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR! No game id provided!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR! That game does not exist!';
            $this->load->view('template', $page);
            return;
        }
        
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        
        $this->load->model('orderofbattlemodel');
        $page['orderofbattle'] = $this->orderofbattlemodel->get_by_id($page['game']->orderofbattle);
        
        // Detect if the current user is playing the game
        // Count up open slots
        $page['is_playing'] = false;
        $page['open_slots'] = 0;

        foreach( $page['players'] as $player )
        {
            if ( $this->ion_auth->logged_in() && $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['this_player'] = $player;
            }
            
            if ($player->user_id == null)
                $page['open_slots']++;
        }
        
        // Fetch owner
        $page['owner'] = $this->ion_auth->get_user($page['game']->creator_id);
        
        $this->load->model('territorymodel');
        $page['territories'] = $this->territorymodel->get_by_game($game_id);
        $this->load->model('jumpshipmodel');
        $page['jumpships'] = $this->jumpshipmodel->get_by_game($game_id);
        $this->load->model('leadermodel');
        $page['leaders'] = $this->leadermodel->get_by_game($game_id);
        $this->load->model('cardmodel');
        $page['cards'] = $this->cardmodel->get_by_game($game_id);
        $this->load->model('combatunitmodel');
        $page['units'] = $this->db->query('select * from combatunits join players on players.player_id=combatunits.owner_id where combatunits.game_id='.$game_id)->result();
        $this->load->model('factorymodel');
        $page['factories'] = $this->factorymodel->get_by_game($game_id);
        $this->load->model('victorymodel');
        $page['conditions'] = $this->victorymodel->get_by_game($game_id);
        
        $page['content'] = 'viewgame';
        $this->load->view('template', $page);
        
    }  // end view
    
    /**
     * View a game as an admin
     * This will have a view of detailed information and eventually access to 
     * update sql rows to fix more common issues encountered during testing.
     */
    function view_admin($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        $page['owner'] = $this->ion_auth->get_user($page['game']->creator_id);
        
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        
        // Cards in play
        $this->load->model('cardmodel');
        $page['cardsinplay'] = $this->cardmodel->get_hold($game_id);
        
        // Combat Logs
        $page['combatlogs'] = $this->db->query('select combatlog.*, players.faction as faction, map.name as territory_name from combatlog
            join players on players.player_id=combatlog.player_id
            join territories on combatlog.territory_id=territories.territory_id
            join map on map.map_id=territories.map_id
            where 
            combatlog.game_id='.$game_id)->result();
        
        // Merc offers
        $page['mercoffers'] = $this->db->query('
            select mercoffers.merc_id, combatunits.name as merc_name, players.faction as player_name, players.turn_order, mercoffers.offer, mercoffers.offer_id
            from mercoffers 
            left join players on players.player_id=mercoffers.player_id
            join combatunits on combatunits.combatunit_id=mercoffers.merc_id
            where players.game_id='.$game_id
            )->result();
        
        // Periphery offers
        $page['peripheryoffers'] = $this->db->query('
            select peripherybids.nation_id as periphery_id, map.name as periphery_name,players.faction as player_name, players.turn_order, peripherybids.offer, peripherybids.bid_id
            from peripherybids
            join players on players.player_id=peripherybids.player_id
            join territories on territories.territory_id=peripherybids.nation_id
            join map on map.map_id=territories.map_id
            where players.game_id='.$game_id
            )->result();
        
        // Admin Swap info
        $page['swapped']=0;
        $page['playing']=0;
        // Check if admin is playing in game
        foreach ($page['players'] as $player)
        {
            // does the player's user_id match the person logged in?
            if ($player->user_id == $page['user']->id)
            {
                $page['playing']=1;  // Admin is playing = TRUE
                // if playing, then check if admin is swapped
                $this->load->model('player_admin_swap_model');
                if ($this->player_admin_swap_model->check_by_admin_user_id($player->user_id,$game_id))
                    $page['swapped']=1;
                else
                    $page['swapped']=0;
            }
         }
         
        $this->load->model('votemodel');
        $page['govotes'] = $this->votemodel->get_by_game($game_id);

        $page['content'] = 'viewgameadmin';
        $this->load->view('template', $page);
        
    }  // end view_admin
    
    /**
     * View the map, with map and territory ids for use when making updates/bug fixes
     */
    function view_admin_map($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Fetch terriories
        $page['territories'] = $this->db->query('SELECT * FROM territories 
                                                JOIN map ON map.map_id=territories.map_id
                                                JOIN players ON players.player_id=territories.player_id
                                                WHERE territories.game_id='.$game_id.' ORDER BY map.name ASC')->result();
        $page['content'] = 'game_map_view_admin';
        $this->load->view('template', $page);
        
    }  // end view_admin_map
    
    function swap_admin_and_player($swap_with_player_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $swap_with_player_id == 0 )
        {
            $page['error'] = 'Error! Cannot swap without indicating a player to swap with.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        $this->load->model('player_admin_swap_model');
        $this->load->model('playermodel');
        
        // Load the player
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_player_by_player_id($swap_with_player_id);
        // Make sure the player exists
        if ( !isset($page['player']->player_id) )
        {
            $page['error'] = 'Error! Cannot find player to swap with.';
            $this->load->view('template', $page);
            return;
        }
        
        // CHECK if already swapped in this game
        $swapentryfound = $this->player_admin_swap_model->get_by_player_id($swap_with_player_id);
        if ( count($swapentryfound) == 1 )
        {
            
            // CHECK if other admin is swapped with player_id
            if ($swapentryfound->admin_user_id != $page['user']->id)
            {
                $page['error'] = 'Error! Cannot swap with a player that another admin is swapped in for.';
                //redirect('game/view_admin/'.$page['player']->game_id, 'refresh');
                $this->load->view('template', $page);
                return;
            }
            
            // CHECK if un-swapping            
            if ($swapentryfound->admin_user_id == $page['user']->id)
            {
                // CHECK if playing... swap back to original house
                // (currently not allowed to swap in a game you are in... it'll just error out)
                    
                // THEN un-swap
                $playerupdate = new stdClass();
                $playerupdate->user_id = $swapentryfound->user_id;
                $this->playermodel->update($swap_with_player_id, $playerupdate);
                //delete swap entry
                //$SI =& get_instance();
                $this->player_admin_swap_model->delete($swapentryfound->swap_id);
                game_message($page['player']->game_id, 'Admin '.$page['user']->username.' has un-swapped from '.$page['player']->faction.'.');
                $this->session->set_flashdata('notice', 'Un-swapped you (user_id '.$swapentryfound->admin_user_id.') back out for user_id '.$swapentryfound->user_id.' playing player_id '.$swap_with_player_id.' in game '.$page['player']->game_id.'!');
            }
            // ELSE switch swap
            else
            {
                // Just error out for now
                $page['error'] = 'Error! Cannot swap in a game you are already swapped into.';
                //redirect('game/view_admin/'.$page['player']->game_id, 'refresh');
                $this->load->view('template', $page);
                return;
                
                // Restore old player entry
                $playerfromupdate = new stdClass();
                $playerfromupdate->user_id = $swapentryfound->user_id;
                $this->playermodel->update($swapentryfound->player_id, $playerfromupdate);

                // Update player being swapped to
                $playertoupdate = new stdClass();
                $playertoupdate->user_id = $page['user']->id;
                $this->playermodel->update($page['player']->player_id, $playertoupdate);
                
                // Update Swap entry
                $swapupdate = new stdClass();
                $swapupdate->admin_user_id = $page['user']->id;
                $this->player_admin_swap_model->update($swapentryfound->swap_id, $swapupdate);
            }
        }
        else
        {
            // CHECK if playing in the game!!!
            $page['players'] = $this->playermodel->get_by_game($page['player']->game_id);
            
            foreach ($page['players'] as $player)
            {
                // does the player's user_id match the person logged in?
                if ($player->user_id == $page['user']->id)
                {
                    // Put other player in the admin's spot
                    // Just error out for now
                    $page['error'] = 'Error! Cannot swap in a game you are playing in.';
                    //redirect('game/view_admin/'.$page['player']->game_id, 'refresh');
                    $this->load->view('template', $page);
                    return;
                }
            }
            
            // Create Swap entry and swap in
            $swapentry = new stdClass();
            $swapentry->game_id = $page['player']->game_id;
            $swapentry->player_id = $swap_with_player_id;
            $swapentry->user_id = $page['player']->user_id;
            $swapentry->admin_user_id = $page['user']->id;
            $this->player_admin_swap_model->create($swapentry);
            
            $playerupdate = new stdClass();
            $playerupdate->user_id = $page['user']->id;
            //$SI =& get_instance();
            $this->playermodel->update($swap_with_player_id, $playerupdate);

            game_message($page['player']->game_id, 'Admin '.$page['user']->username.' has temporarily swapped in for user '.$page['player']->username.', '.$page['player']->faction.'.');
            $this->session->set_flashdata('notice', 'Swapped you (user_id '.$page['user']->id.') with user_id '.$page['player']->user_id.' for player_id '.$swap_with_player_id.' in game '.$page['player']->game_id.'!');
        }
        
        redirect('game/view_admin/'.$page['player']->game_id, 'refresh');
        
    }  // end set_game_owner    
    
    /**
     * View all games
     */
    function viewall()
    {
        $page = $this->page;
        // Load required libraries
        $this->load->library('form_validation');
        
        // Load the games
        $this->load->model('gamemodel');
        $page['games'] = $this->gamemodel->get_all();
        
        $page['content'] = 'viewallgames';
        $this->load->view('template', $page);
    }
    
    /**
     * View inactive games
     */
    function viewinactive()
    {
        $page = $this->page;

        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Load the games
        $today = new DateTime();
        $today->modify('-30 days');
        
        $games;
        $inactive_games = $this->db->query('select * from games join (select * from gamemsg order by timestamp desc) as gamemsg on gamemsg.game_id=games.game_id where 1 group by games.game_id')->result();
        $num_inactive_games = 0;
        foreach($inactive_games as $game)
        {
            if ($game->timestamp < $today->format('Y-m-d H:i:s'))
                $games[] = $game;
        }
        
        if (isset($games))
            $page['games'] = $games;
        $page['content'] = 'viewinactivegames'; 
        $this->load->view('template', $page);
    }
    
    /**
     * View open games
     * 
     */
    function viewopen()
    {
        $page = $this->page;
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Load the games
        $this->load->model('gamemodel');
        $page['games'] = $this->gamemodel->get_open();
        
        $page['content'] = 'viewopengames';
        $this->load->view('template', $page);
    }   
    
    /**
     * View all games
     * 
     */
    function viewyour()
    {
        $page = $this->page;
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Load the games
        $this->load->model('gamemodel');
        $page['games'] = $this->gamemodel->get_by_user($page['user']->id);
        
        $page['content'] = 'game_view_your';
        $this->load->view('template', $page);
    } 
    
    /**
     * Join a game as a player
     * @param type $game_id 
     */
    function join($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
            redirect('auth/login', 'refresh');
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be built
        if ( !$page['game']->built )
        {
            $page['error'] = 'This game is waiting to be built!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Load players
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        
        // Make sure the user is not already playing in the game
        foreach( $page['players'] as $player )
        {
            if ( $player->user_id == $this->ion_auth->get_user()->id )
            {
                $page['error'] = 'ERROR!';
                $this->load->view('template', $page);
                return;
            }
        }
        
        // Get emply player slots
        $page['players'] = $this->playermodel->get_by_game_open($game_id);
        
        // Does this game have a password?
        $page['is_private'] = false;
        if ( isset( $page['game']->password ) )
            $page['is_private'] = true;
        
        // Validate form input
        //...
        $this->form_validation->set_rules('faction', 'Faction', 'required');
        
        if ($this->form_validation->run() == true)
        { 
            // Join the game
            // ...
            $player_id = $this->input->post('faction', true);
            
            if ( $page['is_private'] )
                $password = $this->input->post('password', true);
            else
                $password = null;
            
            // Check password
            if ( $password != $page['game']->password )
            {
                $this->session->set_flashdata('error', 'Incorrect Password!');
                redirect('game/view/'.$game_id, 'refresh');
            }
            
            // Join
            $newplayer['player_id'] = $player_id;
            $newplayer['user_id'] = $page['user']->id;
            $this->playermodel->join_game($newplayer);
            
            $this_player = $this->playermodel->get_by_id($player_id);
            game_message($page['game']->game_id, $page['user']->username.' has joined the game as '.$this_player->faction.'.');

            // Check to see if everyone has joined
            // if so, change game phase to Player Setup
            $players = $this->playermodel->get_by_game($game_id);
            $full = true;
            $firstplayer;
            foreach($players as $p)
            {
                if ($p->user_id == null)
                    $full = false;

                if ( $p->setup_order == 1 )
                    $firstplayer = $p;
            }

            // Change game phase if full
            if ($full && $page['game']->turn == 0)
            {
                // Set player playing to setup_order 1
                $page['game']->player_id_playing = $firstplayer->player_id;
                
                // Advance phase
                $page['game']->phase = 'Player Setup';
                $this->gamemodel->update($game_id, $page['game']);
            }

            // View the game you just joined
            $this->session->set_flashdata('notice', 'You have joined the game successfully!');
            redirect('game/view/'.$game_id, 'refresh');
        }
        else
        {          
            $page['password'] = array('name' => 'password',
                        'id' => 'password',
                        'type' => 'password',
            );
            
            $page['content'] = 'joingame';
            $this->load->view('template', $page);
        }
    }
    
    
    function edit($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Validate form input
        $this->form_validation->set_rules('title', 'Game Title', 'required|max_length[40]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[200]');

        if ($this->form_validation->run() == true)
        { 
            // edit the game
            $title = $this->input->post('title');
            $description = $this->input->post('description');
            $password = $this->input->post('password');

            $gameupdate = new stdClass();
            $gameupdate->game_id = $game_id;
            $gameupdate->title = $title;
            $gameupdate->description = $description;
            $gameupdate->password = $password;
            $this->gamemodel->update($game_id, $gameupdate);
            
            // View the game you just edited
            $this->session->set_flashdata('notice', 'Game Updated.');
            redirect('game/view/'.$game_id,'refresh');
        }
        else
        {   
            $page['title'] = array('name' => 'title',
                        'id' => 'title',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('title'),
            );
            $page['description'] = array('name' => 'description',
                        'id' => 'description',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('description'),
            );
            $page['password'] = array('name' => 'password',
                        'id' => 'password',
                        'type' => 'password',
                        'value' => $this->form_validation->set_value('password'),
            );
            
            $page['content'] = 'editgame';
            $this->load->view('template', $page);
        }

    }    
    
    function options($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Make sure the user is signed in
        if ( !isset($page['user']->id) )
        {
            redirect('auth/login', 'refresh');
        }
        
        // Fetch the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch players
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        
        // Validate form input
        $input_valid = true;
        $destroy_jumpships      = $this->input->post('destroy_jumpships', true);
        $factory_damage         = $this->input->post('factory_damage', true);
        $merc_phase             = $this->input->post('merc_phase', true);
        $capitals_to_win        = (int)$this->input->post('capitals_to_win', true);
        $use_comstar            = $this->input->post('use_comstar', true);
        $use_terra_interdict    = $this->input->post('use_terra_interdict', true);
        $use_terra_loot         = $this->input->post('use_terra_loot', true);
        $alt_victory            = $this->input->post('alt_victory', true);
        $extd_jumpships         = $this->input->post('extd_jumpships', true);
        
        if ($destroy_jumpships === false)
            $input_valid = false;
        
        // Check for integer and at least 2 but less than num players
        if (!is_int($capitals_to_win) || $capitals_to_win < 2 || $capitals_to_win > count($page['players']) )
        {
            $input_valid = false;
        }
        
        if ($input_valid == true)
        { 
            // edit options
            $gameupdate = new stdClass();
            $gameupdate->game_id                = $game_id;
            $gameupdate->destroy_jumpships      = $destroy_jumpships;
            $gameupdate->auto_factory_dmg_mod   = $factory_damage;
            $gameupdate->use_merc_phase         = $merc_phase;
            $gameupdate->capitals_to_win        = $capitals_to_win;
            $gameupdate->use_comstar            = $use_comstar;
            $gameupdate->use_terra_interdict    = $use_terra_interdict;
            $gameupdate->use_terra_loot         = $use_terra_loot;
            $gameupdate->alt_victory            = $alt_victory;
            $gameupdate->use_extd_jumpships     = $extd_jumpships;
            $this->gamemodel->update($game_id, $gameupdate);
            
            // View the game you just edited
            $this->session->set_flashdata('notice', 'Game Updated.');
            redirect('game/options/'.$game_id, 'refresh');
        }
        else
        {   
            $page['content'] = 'gameoptions';
            $this->load->view('template', $page);
        }

    }    
    
    /**
     * Build a game using an order of battle 
     */
    function new_build($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
	if ($game_id == 0)
	{
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Begin our transaction
        $this->db->trans_start();
        
        // Must be the game owner
	$this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
	if ( $game->creator_id != $this->ion_auth->get_user()->id )
	{
            $this->db->trans_complete();
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }

	// Game must be in setup phase
	if ( $game->phase != 'Setup' )
	{
            $this->db->trans_complete();
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game must not be already built
		if ( $game->built )
		{
            $this->db->trans_complete();
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $page['game'] = $game;
        $page['isDone'] = 0;
        
        // Fetch the order of battle and all of the data
        $this->load->model('orderofbattlemodel');
        $oob = $this->orderofbattlemodel->get_by_id($game->orderofbattle);
        
        // First things first, see if players are added
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        if (is_countable($players) && count($players) == 0)
        {
            unset($players);
            $players = $this->orderofbattlemodel->get_players($game->orderofbattle);
            
            foreach($players as $p)
            {
                if ($this->debug>2) 
                	log_message('error', 'building player '.$p->arg0data);
                unset($player);
                $player[$p->arg0column] = $p->arg0data;
                $player[$p->arg1column] = $p->arg1data;
                $player[$p->arg2column] = $p->arg2data;
                $player[$p->arg3column] = $p->arg3data;
                $player[$p->arg4column] = $p->arg4data;
                $player[$p->arg5column] = $p->arg5data;
                $player[$p->arg6column] = $p->arg6data;
                $player[$p->arg7column] = $p->arg7data; // new setup order
                if (isset($p->arg8data))
                {
                    $player['may_build_elementals'] = $p->arg8data; // Elementals
                }
                $player['game_id'] = $game_id;
                $this->playermodel->create($player);
            }
            
            $this->db->trans_complete();
            $page['notice'] = 'Building Game...';
            $page['content'] = 'build_status';
            $this->load->view('template', $page);
            return;
        }
        
        // If players are done, then move on to territories
        $factions;
        foreach($players as $player)
        {
            $factions[$player->faction] = $player->player_id;
        }
        $factions['Neutral'] = null;
        $factions['Comstar'] = 0;
        
        $this->load->model('territorymodel');
        $territories = $this->territorymodel->get_by_game($game_id);
        
        $tdata = $this->orderofbattlemodel->get_territories($game->orderofbattle);
        
        if (count($territories) != count($tdata))
        {
            $this->load->model('territorymodel');
            
            for ($i = count($territories); $i < count($territories) + 10 && $i < count($tdata); $i++)
            {
                unset($object);
                
                $object['player_id']    = $factions[$tdata[$i]->arg0data];
                $object['map_id']       = $tdata[$i]->arg1data;
                $object['is_periphery'] = $tdata[$i]->arg2data;
                $object['resource']     = ($tdata[$i]->arg4data == NULL ? 0 : $tdata[$i]->arg4data);
                $object['is_regional']  = ($tdata[$i]->arg5data == NULL ? 0 : $tdata[$i]->arg5data);
                $object['is_capital']   = ($tdata[$i]->arg6data == NULL ? 0 : $tdata[$i]->arg6data);
                if (isset($tdata[$i]->arg3data) && $tdata[$i]->arg3data != '')
                    $object['garrison_name'] = $tdata[$i]->arg3data;
                    
                $object['game_id'] = $game_id;
                
                $this->territorymodel->create($object);
            }
            
            $page['notice'] = 'Building Map...'.count($territories);
            if ($this->isAjax())
            {
                $this->load->view('build_status_xml', $page);
            }
            else
            {
                $page['content'] = 'build_status';
                $this->load->view('template', $page);
            }
            $this->db->trans_complete();
            return;
        }
              
        $data = $this->orderofbattlemodel->get_data($game->orderofbattle);
        
        $this->load->model('cardmodel');
        $this->load->model('leadermodel');
        $this->load->model('combatunitmodel');
        $this->load->model('factorymodel');
        $this->load->model('jumpshipmodel');
        $this->load->model('territorymodel');
        
        $end = $game->build_step + 10;
        for ($i=$game->build_step; $i < $end && $i < count($data); $i++)
        {
            // Don't repeat territories or players
            if ($data[$i]->type != 0 && $data[$i]->type != 2)
            {
            
                unset($object);
                
                if ( $data[$i]->type == 1 )
                {
                    // cards
                    $object[$data[$i]->arg0column] = $data[$i]->arg0data;
                    
                    $object['game_id'] = $game_id;
                }
                else if ( $data[$i]->type == 3 )
                {
                    // combat units
                    $object['game_id'] = $game_id;
                    
                    $object[$data[$i]->arg0column] = $data[$i]->arg0data;
                    
                    $object['owner_id'] = $factions[$data[$i]->arg1data];
                    $object['original_owner_id'] = $factions[$data[$i]->arg1data];
  
                    $object[$data[$i]->arg2column] = $data[$i]->arg2data;
                    
                    if ( $data[$i]->arg3data != 'None' )
                    {
                        if ( isset( $data[$i]->arg3data ) && $data[$i]->arg3data != 'Free' )
                        {
                            $t_id = $this->territorymodel->get_by_game_map($game_id, $data[$i]->arg3data)->territory_id;
                            $object['location_id'] = $t_id;
                        }
                        else if ($data[$i]->arg3data == 'Free')
                        {
                            $object['can_undeploy'] = true;
                        }
                        
                        $object['strength'] = $data[$i]->arg2data;
                    }
                    else
                    {
                        $object['strength'] = 0;
                    }
                    
                    $object[$data[$i]->arg4column] = $data[$i]->arg4data;
                    if ($data[$i]->arg4data == 0)
                    {
                        $object['original_owner_id'] = $factions[$data[$i]->arg1data];
                    }
                    
                    $object['being_built'] = 0;
                    
                    if (isset($data[$i]->arg5data))
                        $object['can_rebuild'] = $data[$i]->arg5data;
                    
                    // Set conventional or elemental state if required
                    if ($data[$i]->arg6column == 'is_conventional')
                    {
                        $object['is_conventional'] = true;
                    }
                    if ($data[$i]->arg6column == 'is_elemental')
                    {
                        $object['is_elemental'] = true;
                    }
                    
                }
                else if ( $data[$i]->type == 4 )
                {
                    // leaders
                    $object[$data[$i]->arg0column] = $data[$i]->arg0data;
                    
                    if ($data[$i]->arg1data != 'Neutral')
                    {
                        $object['original_house_id'] = $factions[$data[$i]->arg1data];
                        $object['controlling_house_id'] = $factions[$data[$i]->arg1data];
                        $object['allegiance_to_house_id'] = $factions[$data[$i]->arg1data];
                    }
                    
                    $t_id = $this->territorymodel->get_by_game_map($game_id, $data[$i]->arg2data);
                    
                    if(isset($t_id->territory_id))
                    {
                        $object['can_undeploy'] = false;
                        $object['location_id'] = $t_id->territory_id;
                    }
                    else
                    {
                        $object['can_undeploy'] = true;
                        $object['location_id'] = null;
                    }
                    
                    $object[$data[$i]->arg3column] = $data[$i]->arg3data;

                    $object[$data[$i]->arg4column] = $data[$i]->arg4data;

                    $object[$data[$i]->arg5column] = $data[$i]->arg5data;

                    $object[$data[$i]->arg6column] = $data[$i]->arg6data;
                    
                    if ( isset( $data[$i]->arg7data ) )
                    {
                        // Mercenary
                        $object[$data[$i]->arg7column] = $data[$i]->arg7data;
                        $object['original_house_id'] = null;
                    }
                    
                    $object['game_id'] = $game_id;
                }
                else if ( $data[$i]->type == 5 )
                {
                    // factories
                    $t_id = $this->territorymodel->get_by_game_map($game_id, $data[$i]->arg0data)->territory_id;
                    $object['location_id'] = $t_id;
                }
                else if ( $data[$i]->type == 6 )
                {
                    // jumpships
                    if ( isset( $data[$i]->arg0data ) )
                        $object['owner_id'] = $factions[$data[$i]->arg0data];

                    if ( isset( $data[$i]->arg1data ) && $data[$i]->arg1data != '' )
                    {
                        $t_id = $this->territorymodel->get_by_game_map($game_id, $data[$i]->arg1data)->territory_id;
                        $object['location_id'] = $t_id;
                        $object['can_undeploy'] = false;
                    }
                    else
                    {
                        $object['can_undeploy'] = true;
                    }

                    $object[$data[$i]->arg2column] = $data[$i]->arg2data;
                }
                
                // CREATE THE OBJECT
                if ($data[$i]->type == 1)
                   $this->cardmodel->create($object);
                else if ($data[$i]->type == 3)
                   $this->combatunitmodel->create($object);
                else if ($data[$i]->type == 4)
                   $this->leadermodel->create($object);
                else if ($data[$i]->type == 5)
                   $this->factorymodel->create($object);
                else if ($data[$i]->type == 6)
                   $this->jumpshipmodel->create($object);
            }
            // update step
            $game->build_step++;
            
        }
        
        $this->gamemodel->update($game_id, $game);
        
        if ($game->build_step == count($data))
        {
            $page['notice'] = 'Build Complete!';
            $page['isDone'] = 1;
            
            $game->built = true;
            $this->gamemodel->update($game_id, $game);
            
            // Set official capitals for each player
            foreach($players as $player)
            {
                // Fetch capital
                $this->db->where('player_id', $player->player_id);
                $capital = $this->db->query('select * from territories join map on map.map_id=territories.map_id where is_capital=1 and territories.player_id='.$player->player_id)->row();
                unset($pu);
                $pu = new stdClass();
                $pu->player_id = $player->player_id;
                if (isset($capital->territory_id))
                {
                    $pu->official_capital = $capital->territory_id;
                    $pu->original_capital = $capital->territory_id;
                }
                $this->playermodel->update($pu->player_id, $pu);
            }
            
        }
        else
        {
            $page['notice'] = 'Building Game Step '.$game->build_step.' of '.count($data);
        }
        
        $this->db->trans_complete();
        if ($this->isAjax())
        {
            $this->load->view('build_status_xml', $page);
        }
        else
        {
            $page['content'] = 'build_status';
            $this->load->view('template', $page);
        }
    }  // end new_build

    function play($game_id=0)
    {
        $page = $this->page;
        // Make sure an id is provided
        if ( $game_id == 0 )
        {

            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('ion_auth');
        $this->load->helper('url');
        $this->load->helper('form');
		$this->load->database();
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id))
        {
            $page['error'] = 'Error! Sorry, that game does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        foreach( $page['players'] as $player )
        {
            if ( isset($page['user']->id) && $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
            if ( $player->player_id == $page['game']->player_id_playing )
            {
                $page['player_playing'] = $player;
            }
        }
        
        $page['content'] = 'playgame';
        $this->load->view('template', $page);
    }
        
    /**
     * A set of quick tools to allow game owners to tweak certain values without
     * having to rely on sql
     * @param type $game_id 
     */
    function game_tools($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'Error! Game Tools are not available without providing a game ID.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Game Tools are not available for non-existant games.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game or is admin
        if ( $page['game']->creator_id != $this->page['user']->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'Error! Game Tools are not available unless you are the GO or an Admin.';
                $this->load->view('template', $page);
                return;
            }
        }
        
        // Make sure the game is built first
        if (!$page['game']->built)
        {
            $page['error'] = 'Error! Game Tools are not available until you build the game!';
            $this->load->view('template', $page);
            return;
        }
        
        if ($this->ion_auth->is_admin())
            $page['is_admin'] = true;
        else
            $page['is_admin'] = false;
        
        $this->load->model('playermodel');
        $page['players'] = $this->playermodel->get_by_game($game_id);
        
        // Fetch the current player
        $page['player_playing'] = $this->playermodel->get_by_id($page['game']->player_id_playing);
        
        // Fetch game help status
        $this->load->model('gamehelpmodel');
        $page['game_help'] = $this->gamehelpmodel->get_by_game($game_id);
        if (isset($page['game_help']->status) && $page['game_help']->status == 0)
            unset($page['game_help']);
        
        // Load the view
        $page['content'] = 'game_tools';
        $this->load->view('template', $page);

    }  // end game tools
    
    
    /**
     * Change the help status
     * $status 0 = Normal
     * $status 1 = HELP!
     * $status 2 = Resolved
     */
    function change_help_status($game_id=0, $status=-1)
    {
        $page = $this->page;
        
        // Make sure an id is provided and is valid
        if ( $game_id == 0 || $status == -1)
        {
            $page['error'] = 'Error! Cannot change help status of a game without Game ID and new status.';
            $this->load->view('template', $page);
            return;
        }
        if ($status != 0 && $status != 1 && $status != 2)
        {
            $page['error'] = 'Error! Cannot change help status without appropriately specified new status.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Cannot change help status of a game that does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game or is admin
        if ( $page['game']->creator_id != $this->page['user']->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'Error! Cannot change help status unless you are the GO or an Admin.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Fetch game help status
        $this->load->model('gamehelpmodel');
        $page['game_help'] = $this->gamehelpmodel->get_by_game($game_id);
        
        // If it does not exist, create it
        $help_id = 0;
        if (!isset($page['game_help']->help_id))
        {
            $new_help = new stdClass();
            $new_help->game_id = $game_id;
            $new_help->status = $status;
            $this->gamehelpmodel->create($new_help);
            $help_id = $this->db->insert_id();
        }
        else
        {
            // Update the existing help request
            $helpupdate = new stdClass();
            $helpupdate->help_id = $page['game_help']->help_id;
            $helpupdate->status = $status;
            $this->gamehelpmodel->update($page['game_help']->help_id, $helpupdate);
            $help_id = $page['game_help']->help_id;
        }
        
        if ($status == 1)
        {
            // Email all admins
            $admins  = $this->db->query('SELECT * FROM users WHERE group_id=1')->result();
            foreach($admins as $a)
            {
                email_user($a, 'A new help request, <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/game_tools/'.$game_id.'">'.$game->title.'</a>, has been created.');
            }
            
            redirect('game/edit_help_description/'.$help_id, 'refresh');
        }
        else
        {
            // Back to the game tools
            $this->session->set_flashdata('notice', 'Help status updated!');
            redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
        }

    }  // end change_help_status
    
    /**
     * Edit the help description
     */
    function edit_help_description($help_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ($help_id == 0)
        {
            $page['error'] = 'Error! Cannot edit help description without providing a help ID.';
            $this->load->view('template', $page);
            return;
        }
        
        // Get the help request
        $this->load->model('gamehelpmodel');
        $page['game_help'] = $this->gamehelpmodel->get_by_id($help_id);
        
        // Help must exist
        if (!isset($page['game_help']->help_id))
        {
            $page['error'] = 'Error! Cannot edit help description if the help does not exist yet.';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist and must be the game owner
        $this->load->model('gamemodel');
        $game  = $this->gamemodel->get_by_id($page['game_help']->game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'Error! Cannot edit help description for a gae that does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        if ($page['user']->id != $game->creator_id)
        {
            $page['error'] = 'Error! Cannot edit help description if you are not the Game Owner.';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go
        $this->load->library('form_validation');
        $this->form_validation->set_rules('description', 'Description', 'required|max_length[1000]');

        if ($this->form_validation->run() == true)
        { 
            // Edit and return to game tools
            $helpupdate = new stdClass();
            $helpupdate->help_id = $help_id;
            $helpupdate->description = $this->input->post('description');
            $this->gamehelpmodel->update($help_id, $helpupdate);
            
            $this->session->set_flashdata('notice', 'Help description updated!');
            redirect('game/game_tools/'.$game->game_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['description'] = array('name' => 'description',
                        'id' => 'description',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('description'),
            );
            $page['game'] = $game;
            
            $page['content'] = 'game_help_edit_description';
            $this->load->view('template', $page);
        }
        
        
    }  // end edit_help_description
    
    /**
     * Edit the help reply
     */
    function edit_help_reply($help_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ($help_id == 0)
        {
            $page['error'] = 'Error! Cannot edit help reply without providing a help ID.';
            $this->load->view('template', $page);
            return;
        }
        
        // Get the help request
        $this->load->model('gamehelpmodel');
        $page['game_help'] = $this->gamehelpmodel->get_by_id($help_id);
        
        // Help must exist
        if (!isset($page['game_help']->help_id))
        {
            $page['error'] = 'Error! Cannot edit help reply for a help request that does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist and must be admin
        $this->load->model('gamemodel');
        $game  = $this->gamemodel->get_by_id($page['game_help']->game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'Error! Cannot edit help reply for a game that does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        if ( !$this->ion_auth->is_admin() )
        {
            $page['error'] = 'Error! Cannot edit help reply unless you are an Admin.';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go
        $this->load->library('form_validation');
        $this->form_validation->set_rules('reply', 'Reply', 'required|max_length[1000]');

        if ($this->form_validation->run() == true)
        { 
            // Edit and return to game tools
            $helpupdate = new stdClass();
            $helpupdate->help_id = $help_id;
            $helpupdate->reply = $this->input->post('reply');
            $this->gamehelpmodel->update($help_id, $helpupdate);
            
            $this->session->set_flashdata('notice', 'Help reply updated!');
            redirect('game/game_tools/'.$game->game_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['description'] = array('name' => 'reply',
                        'id' => 'reply',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('reply'),
            );
            $page['game'] = $game;
            
            $page['content'] = 'game_help_edit_reply';
            $this->load->view('template', $page);
        }

    }  // end edit_help_reply
    
    /**
     * Show a list of all games with active help status
     */
    function view_games_needing_help()
    {
        $page = $this->page;
        
        if ( !$this->ion_auth->is_admin() )
        {
            $page['error'] = 'Error! Cannot view games needing help unless you are an admin.';
            $this->load->view('template', $page);
            return;
        }
        
        $this->load->model('gamehelpmodel');
        $page['games'] = $this->gamehelpmodel->get_active();
        
        $page['content'] = 'game_help_view_all';
        $this->load->view('template', $page);
        
    }  // end view_games_needing_help
    
    /**
     * Set the current player playing
     */
    function set_player_playing($game_id=0, $player_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0  || $game_id == 0)
        {
            $page['error'] = 'Error! Cannot set player playing without providing both a player ID and a game ID.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'Error! Cannot set player playing if the player does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Cannot set player playing if the game doe snot exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'Error! Cannot set player playing unless you are the game owner or an Admin.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        $gameupdate = new stdClass();
        $gameupdate->player_id_playing = $player_id;
        $this->gamemodel->update($game_id, $gameupdate);
        
        $this->session->set_flashdata('notice', 'Player playing updated!');
        redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
        
    }  // end set_player_playing

    /**
     * Set a player to the game owner
     */
    function set_game_owner($game_id=0, $user_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 || $user_id == 0 )
        {
            $page['error'] = 'Error! Cannot set game owner without a game ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Cannot set game owner for a game that does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Load the user
        
        // 
        
        // Away we go
        $gameupdate = new stdClass();
        $gameupdate->game_id = $game_id;
        $gameupdate->creator_id = $user_id;
        $this->gamemodel->update($game_id, $gameupdate);
        
        
        $this->session->set_flashdata('notice', 'Game Owner updated!');
        redirect('game/view_admin/'.$page['game']->game_id, 'refresh');
        
    }  // end set_game_owner
    
    /**
     * Edit a combat log
     */
    function edit_combat_log($log_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $log_id == 0 )
        {
            $page['error'] = 'Error! Cannot edit combat log without providing a log ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Fetch the log
        $this->load->model('combatlogmodel');
        $log = $this->combatlogmodel->get_by_id($log_id);
        if (!isset($log->combatlog_id))
        {
            $page['error'] = 'Error! Cannot edit combat log if the log does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Validate form input
        $this->load->library('form_validation');
        $this->form_validation->set_rules('casualties_owed', 'Casualties', 'required|numeric');

        if ($this->form_validation->run() == true)
        { 
            $casualties = $this->input->post('casualties_owed');

            $logupdate = new stdClass();
            $logupdate->combatlog_id = $log_id;
            $logupdate->casualties_owed = $casualties;
            $this->combatlogmodel->update($log_id, $logupdate);

            $this->session->set_flashdata('notice', 'Game updated!');
            redirect('game/view_admin/'.$log->game_id, 'refresh');
            
        }
        else
        {
            $page['log'] = $log;
            // Show the form
            $page['casualties_owed'] = array('name' => 'Casualties',
                        'id' => 'Casualties',
                        'type' => 'text',
                        'value' => $this->form_validation->set_value('casualties_owed'),
            );
            
            $page['content'] = 'game_admin_edit_combatlog';
            $this->load->view('template', $page);
        }
        
    }  // edit_combat_log
    
    /**
     * Toggle the retreat allowed flag on a combat log
     */
    function toggle_retreat_allowed($log_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $log_id == 0 )
        {
            $page['error'] = 'Error! Cannot edit combat log without providing a log ID.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Fetch the log
        $this->load->model('combatlogmodel');
        $log = $this->combatlogmodel->get_by_id($log_id);
        if (!isset($log->combatlog_id))
        {
            $page['error'] = 'Error! Cannot edit combat log if the log does not exist.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $logupdate = new stdClass();
        $logupdate->combatlog_id = $log_id;
        $logupdate->is_retreat_allowed = ($log->is_retreat_allowed ? false : true);
        $this->combatlogmodel->update($log_id, $logupdate);
        
        $this->session->set_flashdata('notice', 'Combatlog updated!');
        redirect('game/view_admin/'.$log->game_id, 'refresh');
        
    }  // end toggle_retreat_allowed
    
    /**
     * Delete a combat log
     */
    function delete_combat_log($log_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $log_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Fetch the log
        $this->load->model('combatlogmodel');
        $log = $this->combatlogmodel->get_by_id($log_id);
        if (!isset($log->combatlog_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $this->combatlogmodel->delete($log_id);
        
        $this->session->set_flashdata('notice', 'Combat Log Deleted!');
        redirect('game/view_admin/'.$log->game_id, 'refresh');
        
    }  // end delete_combat_log
    
    /**
     * Delete a merc offer
     */
    function delete_merc_offer($offer_id = 0, $game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $offer_id == 0 || $game_id==0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Fetch the merc offer
        $this->load->model('offermodel');
        $offer = $this->offermodel->get_by_id($offer_id);
        if (!isset($offer->offer_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go
        $this->offermodel->delete($offer_id);
        $this->session->set_flashdata('notice', 'Mercenary Offer Deleted!');
        redirect('game/view_admin/'.$game_id, 'refresh');        
        
    }  // end delete_merc_offer
    
    /**
     * Edit a merc offer
     */
    function edit_merc_offer($offer_id=0, $game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $offer_id == 0 || $game_id==0)
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Fetch the merc offer
        $this->load->model('offermodel');
        $offer = $this->offermodel->get_by_id($offer_id);
        if (!isset($offer->offer_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load required libraries
        $this->load->library('form_validation');
        
        // Validate form input
        $this->form_validation->set_rules('merc_id', 'Mercenary Id', 'required');
        $this->form_validation->set_rules('player_id', 'Player_id', 'required');

        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        
        if ($this->form_validation->run() == true)
        { 
            // Edit the merc offer
            $merc_id = $this->input->post('merc_id');
            $player_id = $this->input->post('player_id');
            $offer = $this->input->post('offer');

            $mercupdate = new stdClass();
            $mercupdate->merc_id = $merc_id;
            $mercupdate->player_id = $player_id;
            if ($offer != '')
                $mercupdate->offer = $offer;
            else
                $mercupdate->offer = null;
            
            $this->offermodel->update($offer_id, $mercupdate);
            
            $this->session->set_flashdata('notice', 'Mercenary Offer Updated!');
            redirect('game/view_admin/'.$game_id, 'refresh');  
        }
        else
        {
            $page['offer'] = $offer;
            $page['game'] = $game;
            $page['content'] = 'merc_offer_edit';
            $this->load->view('template', $page);  
        }
        
    }  // end edit_merc_offer
    
    /**
     * Set the phase of the game
     */
    function set_game_phase($game_id=0, $phase=0)
    {
        $page = $this->page;

        // Make sure an id is provided
        if ( $phase === 0  || $game_id === 0)
        {
            $this->session->set_flashdata('error', 'Error! Cannot set game phase without all input.');
            redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $this->session->set_flashdata('error', 'Error! Cannot set game phase of a non-existing game.');
            redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $this->session->set_flashdata('error', 'Error! Cannot set game phase of a game you do not own.');
                redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
                return;
            }
        }
        
        // Make sure the phase is valid
        $phase = ucfirst($phase);
        if ($phase != 'Setup' && $phase != 'Draw' && $phase != 'Production' && $phase != 'Movement' && $phase != 'Combat' && $phase != 'Game_over' && $phase != 'Mercenary_phase')
        {
            $this->session->set_flashdata('error', 'Error! Cannot set game phase to a phase that does not exist.');
            redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
            return;
        }
        if ($phase == 'Game_over')
            $phase = 'Game Over';
        if ($phase == 'Mercenary_phase')
            $phase = 'Mercenary Phase';
        
        // Away we go!
        $gameupdate = new stdClass();
        $gameupdate->phase = $phase;
        $this->gamemodel->update($game_id, $gameupdate);
        
        $this->session->set_flashdata('notice', 'Game phase updated!');
        redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
        
    }  // end set_game_phase
    
    
    /**
     * Force a player to done status, executing the turn
     */
    function force_done($player_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        // todo
        
        $this->session->set_flashdata('notice', 'Player set to done.');
        redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
    }
    
    function toggle_combat_done($player_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player_id;
        $playerupdate->combat_done = ! $player->combat_done;
        $this->playermodel->update($player_id, $playerupdate);
        
        $this->session->set_flashdata('notice', 'Player Combat Status Toggled.');
        redirect('game/game_tools/'.$page['game']->game_id, 'refresh');
    }
    
    /**
     * Adjust the technology level of a player
     */
    function adjust_tech($player_id=0)
    {
        $page = $this->page;
        $this->load->helper('form');
        $page['content'] = 'adjust_tech_level';
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR! Must specify player to adjust Tech.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR! Must specify a valid player to adjust Tech.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!  Must be a valid game to adjust Tech.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR!  Must be Admin or Game Owner to adjust Tech.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        
        // Get input
        // Validate input
        $valid_data = true;
        $tech = $this->input->post('tech');
        if ($tech !== false && strlen($tech))
        {
            // Must be numeric
            if (!is_numeric($tech) || !is_int((int)$tech) )
            {
                $valid_data = false;
                $page['error'] = 'Tech input not an integer!';
            }

            // Must be > -11 and < 26
            if ($tech < -10 || $tech > 25)
            {
                $valid_data = false;
                $page['error'] = 'Tech input out of range!';
            }
        }
        else
            $valid_data = false;
        
        if ($valid_data)
        {
            if ($player->tech_level == intval($tech))
            {
                $page['error'] = 'Same value entered! Technology remains unchanged.';
                $page['player'] = $player;
                $page['content'] = 'adjust_tech_level';
                $this->load->view('template', $page);
            }
            else
            {
                // away we go
                $playerupdate = new stdClass();
                $playerupdate->player_id = $player_id;
                $old_tech_level = $player->tech_level;
                $playerupdate->tech_level = $tech;
                $this->playermodel->update($player_id, $playerupdate);

                game_message($page['game']->game_id, $player->faction.' technology adjusted from '.$old_tech_level.' to '.$tech.' by '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));

                $this->session->set_flashdata('notice', 'Technology adjusted from '.$old_tech_level.' to '.$tech.' as '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));
                redirect('game/game_tools/'.$page['game']->game_id);
            }
        }
        else
        {
            $page['player'] = $player;
            $page['content'] = 'adjust_tech_level';
            $this->load->view('template', $page);
        }
    }  // end adjust_tech
    
    function adjust_cbills($player_id=0)
    {
        $page = $this->page;
        $this->load->helper('form');
        $page['content'] = 'adjust_cbills';
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR! Must specify a player to adjust CBills.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR! Must specify a valid player to adjust CBills.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR! Must specify a valid game to adjust CBills.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR! Must be Admin or Game Owner to adjust CBills.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        $valid_data = true;
        $cbills = $this->input->post('cbills');
        if ($cbills !== false && strlen($cbills))
        {
            // Must be numeric
            if (!is_numeric($cbills) || !is_int((int)$cbills) )
            {
                $valid_data = false;
                $page['error'] = 'CBill input not an integer!';
            }
            
            // Must be positive and less than... 1,000,000
            if ($cbills < 0 || $cbills > 1000000)
            {
                $valid_data = false;
                $page['error'] = 'CBill inout out of range!';
            }
        }
        else
            $valid_data = false;
        
        if ($valid_data)
        {
            if ($player->money == intval($cbills))
            {
                $page['error'] = 'Same value entered! Cbill amount remains unchanged.';
                $page['player'] = $player;
                $page['content'] = 'adjust_cbills';
                $this->load->view('template', $page);
            }
            else
            {
                // away we go
                $playerupdate = new stdClass();
                $playerupdate->player_id = $player_id;
                $old_cbills = $player->money;
                $playerupdate->money = $cbills;
                $this->playermodel->update($player_id, $playerupdate);
                game_message($page['game']->game_id, $player->faction.' CBills adjusted from '.$old_cbills.' to '.$cbills.' by '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));

                $this->session->set_flashdata('notice', 'CBills adjusted from '.$old_cbills.' to '.$cbills.' by '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));
                redirect('game/game_tools/'.$page['game']->game_id);
            }
        }
        else
        {
            $page['player'] = $player;
            $page['content'] = 'adjust_cbills';
            $this->load->view('template', $page);
        }
    }
    
    /**
     * Adjust the house interdict turns of a player
     */
    function adjust_house_interdict($player_id=0, $current_interdict=0)
    {
        $page = $this->page;
        $this->load->helper('form');
        $page['content'] = 'adjust_house_interdict';
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR! Must specify a player to adjust a House Interdict.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR! Must specify a valid player to adjust a House Interdict.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR! Must specify a valid game to adjust a House Interdict.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR! Must be Admin or the Game Owner to adjust a House Interdict.';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go!
        
        // Get input
        // Validate input
        $valid_data = true;
        $house_interdict = $this->input->post('house_interdict');
        if ($house_interdict !== false && strlen($house_interdict))
        {
            // Must be numeric
            if (!is_numeric($house_interdict) || !is_int((int)$house_interdict) )
            {
                $valid_data = false;
                $page['error'] = 'Not an integer!';
            }
            
            // Must be positive
            if ($house_interdict < 0)
            {
                $valid_data = false;
                $page['error'] = 'Out of range!';
            }
        }
        else
            $valid_data = false;
        
        if ($valid_data)
        {
            if ($player->house_interdict == intval($house_interdict)) //  === ToDo: results in a false :(
            {
                $page['error'] = 'Same value entered! House Interdict remains unchanged.';
                $page['player'] = $player;
                $page['content'] = 'adjust_house_interdict';
                $this->load->view('template', $page);
            }
            else
            {
                // away we go
                $playerupdate = new stdClass();
                $playerupdate->player_id = $player_id;
                $old_house_interdict = $player->house_interdict;
                $playerupdate->house_interdict = $house_interdict;
                $this->playermodel->update($player_id, $playerupdate);

                game_message($page['game']->game_id, $player->faction.' House Interdict adjusted from '.$old_house_interdict.' to '.$house_interdict.' by '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));

                $this->session->set_flashdata('notice', 'House Interdict adjusted from '.$old_house_interdict.' to '.$house_interdict.' as '.( $page['game']->creator_id != $this->ion_auth->get_user()->id ? 'admin.' : 'game owner.'));
                redirect('game/game_tools/'.$page['game']->game_id);
            }
        }
        else
        {
            $page['player'] = $player;
            $page['content'] = 'adjust_house_interdict';
            $this->load->view('template', $page);
        }
    }  // end adjust_house_interdict
    
    /**
     * Draw a card for a player
     */
    function draw_card($player_id)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game
        if ( $page['game']->creator_id != $this->ion_auth->get_user()->id )
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'ERROR!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Away we go
        $this->load->model('cardmodel');
        $card = $this->cardmodel->draw($page['game']->game_id);
        $card->owner_id = $player_id;
        $this->cardmodel->update($card->card_id, $card);
        game_message($page['game']->game_id, 'Game owner draws a card for '.$player->faction.'.');
        
        $this->session->set_flashdata('notice', 'Drew a card for '.$player->faction.'.');
        redirect('game/game_tools/'.$page['game']->game_id);
    }
    
    /**
     * Boot a player from the game or alternatively allow a player to leave
     * @param type $player_id 
     */
    function boot($player_id = 0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0 )
        {
            $page['error'] = 'Error! Cannot remove without a player id.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the player
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        
        // Must exist
        if (!isset($player->player_id))
        {
            $page['error'] = 'Error! Cannot find player to remove.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must not be an empty slot
        if (!isset($player->user_id))
        {
            $page['error'] = 'Error! Cannot remove from a slot that is already empty.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($player->game_id);
        
        // Make sure the game exists        
        if ( !isset($page['game']->game_id) )
        {
            $page['error'] = 'Error! Cannot remove from a game that does not exist.';
            $this->load->view('template', $page);
            return;
        }
        
        // Make sure the user owns this game or owns the player
        if ( $page['game']->creator_id != $page['user']->id  && $player->user_id != $page['user']->id)
        {
            $page['error'] = 'Error! Cannot remove when you do not own the game.';
            $this->load->view('template', $page);
            return;
        }
        if ($player->user_id == $page['user']->id)
        {
            $self_boot = true;
        }
        else
        {
            $self_boot = false;
        }
        
        // confirm!
        if ($this->session->flashdata('confirm') != 'YES')
        {
            if ($self_boot)
            {
                $this->page['warning'] = 'You are about to remove yourself from the game!  Click on the Leave This Game link again if you are sure.';
                $this->session->set_flashdata('confirm', 'YES');
                $this->view($page['game']->game_id);
            }
            else
            {
                $this->page['warning'] = 'You are about to remove a player from the game!  Click on the boot link again if you are sure.';
                $this->session->set_flashdata('confirm', 'YES');
                $this->game_tools($page['game']->game_id);
            }
            return;
        }
        
        // Away we go
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->user_id = null;
        
        $gameupdate = new stdClass();
        $gameupdate->game_id = $page['game']->game_id;
        if ($page['game']->phase != 'Setup')
            $gameupdate->previous_phase = $page['game']->phase;
        else
            $gameupdate->previous_phase = $page['game']->previous_phase;
        $gameupdate->phase = 'Setup';
        
        $this->playermodel->update($player_id, $playerupdate);
        $this->gamemodel->update($page['game']->game_id, $gameupdate);
        
        if (!$self_boot)
        {
            game_message($page['game']->game_id, 'The game owner booted the '.$player->faction.' player from the game.');
            $this->page['notice'] = 'Player booted.';
            $this->game_tools($page['game']->game_id);
        }
        else
        {
            game_message($page['game']->game_id, $player->faction.' player has left the game.');
            $this->page['notice'] = 'You have been removed from the game.';
            $this->view($page['game']->game_id);
        }
        
    }
    
    function game_log($game_id=0, $offset=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($game->game_id) )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Make sure the user is playing in the game
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($game_id);
        $page['is_playing'] = false;
        foreach( $players as $player )
        {
            if ( $player->user_id == $page['user']->id )
            {
                $page['is_playing'] = true;
                $page['player'] = $player;
            }
        }
        
        if (!$page['is_playing'])
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $page['content'] = 'game_log';
        $page['players'] = $players;
        $page['game'] = $game;
        
        $this->load->model('gamemsgmodel');
        $page['num_logs'] = $this->gamemsgmodel->get_num_logs($game_id);
        $page['logs'] = $this->gamemsgmodel->get_last($game_id, $offset);
        $page['offset'] = $offset;
        
        $this->load->view('templatexml', $page);
        
    }  // end chat_log
    
    
    /**
     * Force a player's bid to 0 for either mercenary or periphery bids
     * 
     */
    function force_bid($player_id=0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $player_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Player must exist
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($player_id);
        if (!isset($player->player_id))
        {
            $page['error'] = 'No such player!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game Owner only
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        if ($page['user']->id != $game->creator_id)
        {
            if (!$this->ion_auth->is_admin())
            {
                $page['error'] = 'Game owner only!';
                $this->load->view('templatexml', $page);
                return;
            }
        }
        
        // Must be stuff up for bid
        $periphery = false;
        $mercenary = false;

        $this->load->model('offermodel');
        $this->load->model('cardmodel');
        $this->load->model('offermodel');
        
        $card = $this->cardmodel->get_hold($game->game_id);
        $this->load->model('peripherymodel');
        $periphery_bids = $this->peripherymodel->get_by_game($game->game_id);
        
        $this->load->model('combatunitmodel');
        $mercs = $this->combatunitmodel->mercs_for_hire($game->game_id);
        
        if (isset($card->card_id) && $card->type_id == 14)
        {
            $mercenary = true;
        }
        else if (count($periphery_bids) != 0)
        {
            $periphery = true;
        }
        else if (count($mercs) > 0)
        {
            $mercenary = true;
        }
        else
        {
            $page['error'] = 'No units are up for bid at this time!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must not have already bid
        if ($mercenary)
        {
            // Get merc unit
            $merc = $mercs[0];
            if (!isset($merc->combatunit_id))
            {
                $page['error'] = 'ERROR!';
                $this->load->view('templatexml', $page);
                return;
            }
            
            // Away we go!
            log_message('error', 'Merc_bid');
            merc_bid($player, $merc, 0, true);
            
        }
        else
        {
            foreach($periphery_bids as $bid)
            {
                if ($bid->player_id == $player_id && isset($offer) && $offer != null)
                {
                    $page['error'] = 'Player has already bid!';
                    $this->load->view('templatexml', $page);
                    return;
                }
                else if ($bid->player_id == $player_id)
                {
                    // Away we go!
                    $this->load->model('territorymodel');
                    $territory = $this->territorymodel->get_by_id($bid->nation_id);
                    periphery_bid($player, $territory, 0, $game, true);                    
                }
            }
        }
        
    }  // end force_bid
    
    /**
     * View this games deck of cards
     * If this request is from the game owner or an admin then display modification options
     */
    function view_deck($game_id = 0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist
        $this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
	
        // Determine if this user is the game owner
        $page['is_game_owner'] = false;
        if ( isset($page['user']->id) && $game->creator_id == $page['user']->id )
	{
            $page['is_game_owner'] = true;
	}
        if ($this->ion_auth->is_admin())
        {
            $page['is_game_owner'] = true;
        }
        
        // Away we go
        $this->load->model('cardmodel');
        $page['cards'] = $this->cardmodel->get_by_game($game_id);
        $page['game'] = $game;
        $page['content'] = 'game_view_deck';
        $this->load->view('template', $page);
        
    }  // end view_deck
    
    /**
     * Add a card to this deck
     */
    function add_card($game_id = 0, $type_id = 0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $game_id == 0)
        {
            $page['error'] = 'Invalid input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist
        $this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Card must exist
        $this->load->model('cardmodel');
        $card = $this->cardmodel->get_by_type($type_id);
        if (!isset($card->type_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Determine if this user is the game owner
        $page['is_game_owner'] = false;
        if ( isset($page['user']->id) && $game->creator_id == $page['user']->id )
	{
            $page['is_game_owner'] = true;
	}
        if ($this->ion_auth->is_admin())
        {
            $page['is_game_owner'] = true;
        }
        if (!$page['is_game_owner'])
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be setup phase
        if ($game->phase != 'Setup')
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go!
        $newcard = new stdClass();
        $newcard->game_id = $game_id;
        $newcard->type_id = $type_id;
        $this->cardmodel->create($newcard);
        
        $this->session->set_flashdata('notice', 'Card Added.');
        redirect('game/view_deck/'.$game_id, 'refresh');
        
    }  // end add_card
    
    /**
     * Remove a card to this deck
     */
    function remove_card($game_id = 0, $card_id = 0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $game_id == 0 || $card_id == 0)
        {
            $page['error'] = 'Invalid input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist
        $this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
         // Card must exist
        $this->load->model('cardmodel');
        $card = $this->cardmodel->get_by_id($card_id);
        if (!isset($card->card_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Determine if this user is the game owner
        $page['is_game_owner'] = false;
        if ( isset($page['user']->id) && $game->creator_id == $page['user']->id )
	{
            $page['is_game_owner'] = true;
	}
        if ($this->ion_auth->is_admin())
        {
            $page['is_game_owner'] = true;
        }
        if (!$page['is_game_owner'])
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be setup phase
        if ($game->phase != 'Setup')
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go!
        $this->cardmodel->delete($card_id);
        
        $this->session->set_flashdata('notice', 'Card Removed.');
        redirect('game/view_deck/'.$game_id, 'refresh');
        
    }  // end remove_card
    
    /**
     * See the available cards to add to a game
     */
    function card_list($game_id = 0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $game_id == 0)
        {
            $page['error'] = 'Invalid input!';
            $this->load->view('template', $page);
            return;
        }
        
        // Game must exist
        $this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Determine if this user is the game owner
        $page['is_game_owner'] = false;
        if ( isset($page['user']->id) && $game->creator_id == $page['user']->id )
	{
            $page['is_game_owner'] = true;
	}
        if ($this->ion_auth->is_admin())
        {
            $page['is_game_owner'] = true;
        }
        
        // Away we go!
        $page['content'] = 'game_card_list';
        $this->load->model('cardmodel');
        $page['cards'] = $this->cardmodel->get_all_types();
        $page['game'] = $game;
        $this->load->view('template', $page);
        
    }  // end card_list
    
    /**
     * Check a game against an order of battle to see if it was built correctly.
     * Check the number of entities in the oob vs the actual game
     */
    public function check($game_id=0)
    {
        $page = $this->page;
        
        // Must provide valid input
        if ( $game_id == 0)
        {
            $page['error'] = 'Invalid input!';
            $this->load->view('template', $page);
            return;
        }

        // Must be logged in and admin
        if ( !$this->ion_auth->logged_in() )
            redirect('','refresh');
        if ( !$this->ion_auth->is_admin() )
            redirect('','refresh');
        
        // Game must exist
        $this->load->model('gamemodel');
	$game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'ERROR!';
            $this->load->view('template', $page);
            return;
        }
        
        // Combat units
        $page['oob_combatunits'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=3 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_combatunits'] = $this->db->query('select count(combatunit_id) as num from combatunits where game_id='.$game->game_id)->row()->num;
        
        // Jumpships
        $page['oob_jumpships'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=6 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_jumpships'] = $this->db->query('select count(jumpship_id) as num from jumpships join territories on territories.territory_id=jumpships.location_id where game_id='.$game->game_id)->row()->num;
        
        // Leaders
        $page['oob_leaders'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=4 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_leaders'] = $this->db->query('select count(leader_id) as num from leaders where game_id='.$game->game_id)->row()->num;
        
        // Territories
        $page['oob_territories'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=2 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_territories'] = $this->db->query('select count(territory_id) as num from territories where game_id='.$game->game_id)->row()->num;
        
        // Factories
        $page['oob_factories'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=5 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_factories'] = $this->db->query('select count(factory_id) as num from factories join territories on territories.territory_id=factories.location_id where game_id='.$game->game_id)->row()->num;
        
        // Cards
        $page['oob_cards'] = $this->db->query('select count(data_id) as num from orderofbattledata where type=1 and oob_id='.$game->orderofbattle)->row()->num;
        $page['game_cards'] = $this->db->query('select count(card_id) as num from cards where game_id='.$game->game_id)->row()->num;
        
        // Load the view
        $page['game'] = $game;
        $page['content'] = 'game_check';
        $this->load->view('template', $page);
        
    }  // end check_game
    
    /**
     * Give a unique name to conventional or elemental units to make them easier to find in drop-down lists
     */
    public function name_conventional($unit_id=0)
    {
        $page = $this->page;
        
        // Valid input
        if ($unit_id==0)
        {
            $page['error'] = 'Error! Cannot name a combat unit without an ID.';
            $this->load->view('templatexml', $error);
            return;
        }
        
        // Must own the unit in question
        $this->load->model('combatunitmodel');
        $unit = $this->combatunitmodel->get_by_id($unit_id);
        
        $this->load->model('playermodel');
        $owner = $this->playermodel->get_by_id($unit->owner_id);
        
        if ($owner->user_id != $page['user']->id)
        {
            $this->session->set_flashdata('error', 'Error! Cannot name a combat unit you do not own.');
            redirect('sw/location/'.$unit->location__id, 'refresh');
        }
        
        // Must be a conventional or elemental
        if (!$unit->is_conventional && !$unit->is_elemental)
        {
            $this->session->set_flashdata('error', 'Error! Must be either a conventional or an elemental.');
            redirect('sw/location/'.$unit->location__id, 'refresh');
        }
        
        // Away we go
        $this->load->library('form_validation');
        
        // Validate form input
        $name = $this->input->post('input');
        if (count_chars($name) > 40 && $name != false)
        {
            $page['error'] = 'Error! Cannot name a combat unit with a name longer than 40 characters.';
            $name == false;
        }
        
        if ($name != false)
        { 
            // Update the combat unit
            $unitupdate = new stdClass();
            if ($unit->is_conventional)
                $unitupdate->name = $name.' (Conv.)';
            else if ($unit->is_elemental)
                $unitupdate->name = $name.' (Elem.)';
            $unitupdate->combatunit_id = $unit_id;
            
            $this->combatunitmodel->update($unit_id, $unitupdate);
            
            // Redirect to jumpship view            
            $this->session->set_flashdata('notice', 'Name Updated');
            redirect('sw/location/'.$unit->location__id, 'refresh');
        }
        else
        {
            $page['unit'] = $unit;
            $page['content'] = 'conventional_name';
            $this->load->view('templatexml', $page);
        }
    }  // end name_conventional
    
    /**
     * Returns true if the current request is an ajax request
     */
    private function isAjax()
    {   
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
    }
       
}  // end game controller
