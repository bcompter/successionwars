<?php

/**
 * Handles card draws, plays, and shuffles
 */
class Cards extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }    
    
    /**
     * Play a card from a players hand
     * 
     * @param type $card_id  The card id
     * @param type $target_id The target of the card if required
     * @param type $location_id The location of the card if required
     */
    function play($card_id=0, $target_id=0, $location_id=-1)
    {
        // Make sure at least the first id is present
        // Other ids should be checked for later if they are required by that 
        // particular card.
        if ( $card_id == 0 )
        {
            $page['error'] = 'No such card.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Fetch the card
        $this->load->model('cardmodel');
        $card = $this->cardmodel->get_by_id($card_id);
        
        // Make sure the card exists
        if ( !isset( $card->card_id ) )
        {
            $page['error'] = 'No such card.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Obviously can't be in the deck or discard pile
        if ( $card->owner_id == 0 || $card->owner_id == null )
        {
            $page['error'] = 'No such card.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Fetch the owner
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($card->owner_id);
        
        // Must be playing in this game and belong to this user
        // User is signed in
        // Player exists
        if ( $player->user_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'No such card.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Can't have more than the max hand size
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        $cards = $this->cardmodel->get_by_player($card->owner_id);
        if ( count($cards) > $game->hand_size )
        {
            $page['error'] = 'You have too many cards in your hand and must discard down to '.$game->hand_size.' cards.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Must be the proper phase to play
        
        if ( $game->phase != $card->phase && $card->phase != 'Any' && $player->player_id != $game->player_id_playing)
        {
            $page['error'] = 'Wrong phase.';
            $this->load->view('templatexml', $page);
            return;
        }
        if ( $game->phase == 'Setup' || $game->phase == 'Player Setup')
        {
            $page['error'] = 'Wrong phase.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // If the game is on hold, the card being played must match this card
        $cardbeingplayed = $this->cardmodel->get_hold($game->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            if ($cardbeingplayed->card_id != $card_id)
            {
                $this->page['error'] = 'Cannot play another card until the current card, '.$cardbeingplayed->title.', is resolved.';
                $this->view($player->player_id);
                return;
            }
        }
        
        // Check if any periphery nations are up for bid
        $this->load->model('peripherymodel');
        $bids = $this->peripherymodel->get_by_game($game->game_id);
        if(count($bids) > 0)
        {
            $page['error'] = 'No cards may be played while bidding is open on a periphery nation!';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Execute the card
        switch( $card->type_id )
        {
            case 1:     // Increase tech by 3
                $this->technology3($card, $player);
                break;
            case 2:     // Increase tech by 5
                $this->technology5($card, $player);
                break;
            case 3:     // Death Commandos
                $this->death_commandos($card, $player, $target_id);  
                break;
            case 4:     // Spy
                if ( $target_id == 0 )
                {
                    // Select a target for the spy
                    $page['players'] = $this->playermodel->get_by_game($game->game_id);
                    $page['card'] = $card;
                    $page['player'] = $player;
                    
                    $this->load->view('spy', $page);
                }
                else
                {
                    // Target selected 
                    $target = $this->playermodel->get_by_id($target_id);
                    
                    // Must exist
                    if ( !isset($target->player_id) )
                    {
                        $this->page['error'] = 'Cannot spy without a player id.';
                        $this->view($player->player_id);
                        return;
                    }  

                    // Must be playing in this game
                    if ( $game->game_id != $target->game_id )
                    {
                        $this->page['error'] = 'Cannot spy in a game you are not playing in.';
                        $this->view($player->player_id);
                        return;
                    }  

                    // Can't be this player... duh    
                    if ( $target->player_id == $player->player_id )
                    {
                        $this->page['error'] = 'Cannot spy on your own heand.';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Can't be an eliminated player
                    if ($target->turn_order == 0)
                    {
                        $this->page['error'] = 'Cannot spy on a player that has been eliminated.';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Newb check
                    if ( count($this->cardmodel->get_by_player($target->player_id)) == 0 )   // this only hurts newbs and they have enough to learn already
                    {
                        $this->page['error'] = $target->faction.' has no cards in their hand to spy on.';
                        $this->view($player->player_id);
                        return;
                    }
                   
                    // Away we go
                    $page['cards'] = $this->cardmodel->get_by_player($target->player_id);
                    $page['target'] = $target;
                    
                    discard($card);
                    game_message($player->game_id, $player->faction.' played a Spy card against '.$target->faction.'. '.$target->faction.'\'s play hand is shown to '.$player->faction.'.');

                    $this->load->view('spyresult', $page);
                }
                
                
                break;
            case 5:     // Free Jumpship 3
                // Must be the players turn
                if ($game->player_id_playing != $player->player_id)
                {
                    $this->page['error'] = 'Cannot build a jumpship when it is not your turn.';
                    $this->view($player->player_id, $page);
                    return;
                }
                
                if ( $target_id == 0 )
                {
                    // Select a factory
                    $this->load->model('factorymodel');
                    $page['factories'] = $this->factorymodel->get_by_player($player->player_id);
                    
                    $page['card'] = $card;
                    $page['player'] = $player;
                    $page['content'] = 'freejumpship';
                    
                    $this->load->view('templatexml', $page);
                }
                else 
                {
                    // Factory selected
                    $this->load->model('factorymodel');
                    $factory = $this->factorymodel->get_by_id($target_id);
                    
                    // Must be owned by the player
                    if ( $factory->player_id != $player->player_id )
                    {
                        $this->page['error'] = 'Cannot build a jumpship at a factory you do not own.';
                        $this->view($player->player_id, $page);
                        return;
                    }
                    
                    // factory is undamaged
                    if ($factory->is_damaged)
                    {
                        $this->page['error'] = 'Cannot build a jumpship at a factory that is damaged.';
                        $this->view($player->player_id);
                        return;
                    } 
                    
                    // Away we go...
                    $this->load->model('jumpshipmodel');
                    $jumpship = new stdClass();
                    $jumpship->capacity = 3;
                    $jumpship->owner_id = $player->player_id;
                    $jumpship->location_id = $factory->territory_id;
                    $jumpship->being_built = true;
                    $this->jumpshipmodel->create($jumpship);
                    
                    discard($card);
                    
                    $this->load->model('territorymodel');
                    $territory = $this->territorymodel->get_by_id($factory->location_id);
                    game_message($game->game_id, $player->faction.' played a Free Jumpship card!  A new Jumpship, 3 is being built on '.$territory->name.'.');

                    // Back to the play hand
                    $this->view($player->player_id);
                }
                break;
            case 6:     // Free combat unit
                
                 // Must be the players turn
                if ($game->player_id_playing != $player->player_id)
                {
                    $this->page['error'] = 'Cannot build a free unit when it is not your turn.';
                    $this->view($player->player_id, $page);
                    return;
                }
                
                if ( $target_id == 0 || $location_id == -1 )
                {
                    // Must select target and locations
                    $this->load->model('combatunitmodel');
                    $page['combatunits'] = $this->combatunitmodel->get_by_killed($player->player_id);
                    $this->load->model('factorymodel');
                    $page['factories'] = $this->factorymodel->get_by_player($player->player_id);
                    
                    $page['card'] = $card;
                    $page['title'] = 'Free Combat Unit';
                    $this->load->view('freecombatunit', $page);
                    
                }
                else
                {
                    // Target selected 
                    $this->load->model('combatunitmodel');
                    $this->db->join('players', 'players.player_id=combatunits.owner_id');
                    $combatunit = $this->combatunitmodel->get_by_id($target_id);
                    $this->load->model('factorymodel');
                    $factory = $this->factorymodel->get_by_id($location_id);
                    
                    // must own combat unit and factory
                    if ($player->player_id != $combatunit->owner_id )
                    {
                        $this->page['error'] = 'Cannot build a free unit that you do not own.';
                        $this->view($player->player_id);
                        return;
                    } 
                    if ($player->player_id != $factory->player_id)
                    {
                        $this->page['error'] = 'Cannot build a free unit at a factory you do not own.';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // combat unit and factory from the same game
                    $this->load->model('territorymodel');
                    $location = $this->territorymodel->get_by_id($factory->location_id);
                    if($location->game_id != $combatunit->game_id)
                    {
                        $this->page['error'] = 'Invalid combat unit..';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // combat unit is not alive at the moment
                    if ($combatunit->strength != 0)
                    {
                        $this->page['error'] = 'Combat unit is not valid.';
                        $this->view($player->player_id);
                        return;
                    }  
                    
                    // factory is undamaged
                    if ($factory->is_damaged)
                    {
                        $this->page['error'] = 'Factory is damaged.';
                        $this->view($player->player_id);
                        return;
                    }  
                    
                    // away we go
                    if ($combatunit->is_rebuild)
                        $strength = 4;
                    else
                        $strength = $combatunit->prewar_strength;
                    
                    $combatunitupdate = new stdClass();
                    $combatunitupdate->combatunit_id = $combatunit->combatunit_id;
                    $combatunitupdate->being_built = true;
                    $combatunitupdate->strength = $strength;
                    $combatunitupdate->location_id = $factory->location_id;
                    $this->combatunitmodel->update($target_id, $combatunitupdate);
                    
                    discard($card);
                    game_message($game->game_id, $player->faction.' played a Free Combat Unit card! '.$combatunit->name.', '.$strength.' are being built on '.$location->name.'.');

                    // Back to the play hand
                    $this->page['notice'] = 'Free Combat Unit card played.';
                    $this->view($player->player_id);
                }
            case 7:     // Normal cost pre-war strength combat unit
            //
                 // Must be the players turn
                if ($game->player_id_playing != $player->player_id)
                {
                    $this->page['error'] = 'It is not your turn.';
                    $this->view($player->player_id, $page);
                    return;
                }
                
                if ( $target_id == 0 || $location_id == -1 )
                {
                    // Must select target and locations
                    $this->load->model('combatunitmodel');
                    $page['combatunits'] = $this->combatunitmodel->get_by_rebuild($player->player_id);
                    $this->load->model('factorymodel');
                    $page['factories'] = $this->factorymodel->get_by_player($player->player_id);
                    
                    $page['card'] = $card;
                    $page['title'] = 'Pre-War Strength Combat Unit';
                    $page['content'] = 'freecombatunit';
                    $this->load->view('templatexml', $page);
                }
                else
                {
                    // Target selected 
                    $this->load->model('combatunitmodel');
                    $combatunit = $this->combatunitmodel->get_by_id($target_id);

                    $this->load->model('factorymodel');
                    $factory = $this->factorymodel->get_by_id($location_id);
 
                    // must own combat unit and factory
                    if ($player->player_id != $combatunit->owner_id)
                    {
                        $this->page['error'] = 'You don\'t own that unit!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // check factory
                    $this->load->model('territorymodel');
                    $factorylocation = $this->territorymodel->get_by_id($factory->location_id);
                    if ($factorylocation->player_id != $player->player_id)
                    {
                        $this->page['error'] = 'You don\'t own that factory!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // combat unit is not alive at the moment
                    if ($combatunit->strength != 0)
                    {
                        $this->page['error'] = 'Bad target.';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Combat unit must be a rebuild
                    if (!$combatunit->is_rebuild)
                    {
                        $this->page['error'] = 'That unit cannot be rebuilt because it has not been destroyed yet!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // factory is undamaged
                    if ($factory->is_damaged)
                    {
                        $this->page['error'] = 'Factory is damaged.';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Must be able to afford it
                    $cost = get_price($combatunit->is_merc, $player->tech_level);
                    if ( $player->money < $cost )
                    {
                        $this->page['error'] = 'You can\'t afford it!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // away we go
                    $combatunitupdate = new stdClass();
                    $combatunitupdate->combatunit_id = $combatunit->combatunit_id;
                    $combatunitupdate->being_built = true;
                    $combatunitupdate->is_rebuild = false;
                    $combatunitupdate->location_id = $factory->location_id;
                    $this->combatunitmodel->update($target_id, $combatunitupdate);
                    
                    $playerupdate = new stdClass();
                    $playerupdate->player_id = $player->player_id;
                    $playerupdate->money = $player->money - $cost;
                    $this->playermodel->update($player->player_id, $playerupdate);
                    discard($card);
                    game_message($game->game_id, $player->faction.' played a Pre-War Unit card! '.$combatunit->name.', '.$combatunit->prewar_strength.' are being built on '.$factorylocation->name.'.');
                    $this->page['notice'] = 'Pre-war Unit Strength card played!';
                    
                    // Back to the play hand
                    $this->view($player->player_id);
                }
                break;
           case 8:     // Regional combat bonus
                if ( $target_id == 0 || $location_id == -1 )
                {
                    // Must select target and locations
                    $page['player'] = $player;
                    $this->load->model('playermodel');
                    $page['players'] = $this->playermodel->get_by_game($player->game_id);
                    $this->load->model('territorymodel');
                    $this->db->order_by('name');
                    $page['territories'] = $this->territorymodel->get_by_game($player->game_id);
                    
                    $page['card'] = $card;
                    $this->load->view('starleaguebonus', $page);
                    
                }
                else
                {
                    // Target selected 
                    $this->load->model('territorymodel');
                    $territory = $this->territorymodel->get_by_id($location_id);
                    $target = $this->playermodel->get_by_id($target_id);
                    
                    // check for valid targets
                    if ( $territory->game_id != $player->game_id )
                    {
                        $this->page['error'] = 'Invalid territory!';
                        $this->view($player->player_id);
                        return;
                    }
                    if ( $target->game_id != $player->game_id )
                    {
                        $this->page['error'] = 'Invalid target!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Must not already have a regional bonus                  
                    $this->load->model('combatbonusmodel');
                    $bonus = $this->combatbonusmodel->get_by_player_territory($player->player_id, $territory->territory_id);
                    foreach($bonus as $b)
                    {
                        if ($b->source_type == 0 && $b->value == 2)
                        {
                            $this->page['error'] = 'You already have a Star League Bonus in effect for this turn in '.$territory->name.'!';
                            $this->view($player->player_id);
                            return;
                        }
                    }
                    unset($bonus);
                    $bonus = new stdClass();
                    $bonus->game_id = $game->game_id;
                    $bonus->player_id = $target->player_id;
                    $bonus->location_id = $territory->territory_id;
                    $bonus->source_type = 0;        // CARD
                    $bonus->source_id = $card->card_id;
                    $bonus->value = 2;
                    $bonus->ttl = 1;
                    $this->combatbonusmodel->create($bonus);
                    
                    discard($card);
                    game_message($game->game_id, $player->faction.' played a Regional Combat Bonus card! '.$target->faction.' receives +2 combat strength bonus in '.$territory->name.'.');

                    // Back to the play hand
                    $this->page['notice'] = 'Star League Combat Bonus card played!';
                    $this->view($player->player_id);
                }
                break;
                
           case 9:     // Regional interdict
                if ( $target_id == 0 || $location_id == -1 )
                {
                    // Must select target and locations
                    $this->load->model('playermodel');
                    $page['players'] = $this->playermodel->get_by_game($player->game_id);
                    $this->load->model('territorymodel');
                    $this->db->order_by('name');
                    $page['territories'] = $this->territorymodel->get_by_game($player->game_id);
                    
                    $page['card'] = $card;
                    $page['player'] = $player;
                    $this->load->view('interdict', $page);
                    
                }
                else
                {
                    // Target selected 
                    $this->load->model('territorymodel');
                    $territory = $this->territorymodel->get_by_id($location_id);
                    $target = $this->playermodel->get_by_id($target_id);
                    
                    // check for valid targets
                    if ( $territory->game_id != $player->game_id )
                    {
                        $this->page['error'] = 'You are not playing in that game!';
                        $this->view($player->player_id);
                        return;
                    }
                    if ( $target->game_id != $player->game_id )
                    {
                        $this->page['error'] = 'You can\'t target that player!';
                        $this->view($player->player_id);
                        return;
                    }
                    
                    // Must not already have a regional interdict                    
                    $this->load->model('combatbonusmodel');
                    $bonus = $this->combatbonusmodel->get_by_player_territory($player->player_id, $territory->territory_id);
                    foreach($bonus as $b)
                    {
                        if ($b->source_type == 0 && $bonus->value == -2)
                        {
                            $this->page['error'] = 'You already have an Interdict in effect on '.$territory->name.'!';
                            $this->view($player->player_id);
                            return;
                        }
                    }
                    unset($bonus);
                    $bonus = new stdClass();
                    $bonus->game_id = $game->game_id;
                    $bonus->player_id = $target->player_id;
                    $bonus->location_id = $territory->territory_id;
                    $bonus->source_type = 0;        // CARD
                    $bonus->source_id = $card->card_id;
                    $bonus->value = -2;
                    $bonus->ttl = 5;
                    $this->combatbonusmodel->create($bonus);
                    
                    discard($card);
                    game_message($game->game_id, $player->faction.' played a Regional Interdict card! '.$target->faction.' receives -2 combat strength penalty in '.$territory->name.'.');

                    // Back to the play hand
                    $this->page['notice'] = 'Regional Interdict card played!';
                    $this->view($player->player_id);
                }
                break;
           case 10:    // Create new merc unit
                    $this->create_merc_unit($card, $player, $target_id);
                    break;
                    
           case 11:     // Spy and Steal
                    $targetcard_id = $location_id;

                    if ($target_id == 0)
                    {
                        // Select target
                        $page['players'] = $this->playermodel->get_by_game($game->game_id);
                        $page['attacker'] = $player;
                        $page['content'] = 'spyandsteal';
                        $page['card'] = $card;
                        $this->load->view('templatexml', $page);
                    }
                    else if ($targetcard_id == -1)
                    {
                        // Must be a valid target
                        $target = $this->playermodel->get_by_id($target_id);
                        if ($target->game_id != $player->game_id)
                        {
                            $this->page['error'] = 'Error! Cannoy spy steal on a player in another game.';
                            $this->view($player->player_id);
                            return;
                        }
                        
                        // Don't allow multiple looks at player hands!
                        // Check for a hold state first!  If found, fwd to the next level
                        if (isset($cardbeingplayed->card_id))
                        {
                            if ($cardbeingplayed->target_id != $target_id)
                            {
                                $this->page['error'] = 'Error! Cannot spy with a spy steal card on more than one player.';
                                $this->view($player->player_id);
                                return;
                            }
                        }
                        
                        // Can't be an eliminated player
                        if ( $target->turn_order == 0)
                        {
                            $this->page['error'] = 'Error! You cannot spy steal on a player that has been eliminated.';
                            $this->view($player->player_id);
                            return;
                        }
                        
                        // Select card
                        $page['cards'] = $this->cardmodel->get_by_player($target_id);
                        if (count($page['cards']) == 0)
                        {
                            $this->page['error'] = 'Error! Cannot spy steal from a player that has no cards in their hand.';
                            $this->view($player->player_id);
                            return;
                        }

                        // Place game in a hold until resolved
                        $cardupdate = new stdClass();
                        $cardupdate->card_id = $card_id;
                        $cardupdate->being_played = true;
                        $cardupdate->target_id = $target_id;
                        $this->cardmodel->update($card_id, $cardupdate);
                        
                        game_message($game->game_id, $player->faction.' played a Spy and Steal card on '.$target->faction.'! '.$player->faction.' gets to steal a card!');

                        $page['content'] = 'spyandsteal';
                        $page['player'] = $player;
                        $page['originalcard'] = $card;
                        $this->load->view('templatexml', $page);
                    }
                    else
                    {
                        // Must be a valid target
                        $target = $this->playermodel->get_by_id($target_id);
                        if ($target->game_id != $player->game_id)
                        {
                            $this->page['error'] = 'Error! cannot spy steal from a player not in this game.';
                            $this->view($player->player_id);
                            return;
                        }

                        // Make sure the target card is ok
                        $targetcard = $this->cardmodel->get_by_id($targetcard_id);
                        if($targetcard->owner_id != $target_id)
                        {
                            $this->page['error'] = 'Error! Cannot spy steal a card not owned by the target player.';
                            $this->view($player->player_id);
                            return;
                        }

                        // Remove hold and discard
                        unset($cardupdate);
                        $cardupdate = new stdClass();
                        $cardupdate->card_id = $card_id;
                        $cardupdate->being_played = false;
                        $cardupdate->target_id = null;
                        $this->cardmodel->update($card_id, $cardupdate);
                        
                        // Steal card
                        $cardupdate->card_id = $targetcard_id;
                        $cardupdate->owner_id = $player->player_id;
                        $this->cardmodel->update($targetcard_id, $cardupdate);
                        
                        discard($card);
                        
                        game_message($game->game_id, $player->faction.' stole a card from '.$target->faction.'!');
                        game_message($game->game_id, $player->faction.' stole the card "'.$targetcard->title.'" from your hand.', $target_id);
                        
                        $this->page['notice'] = 'Card stolen successfully!';
                        $this->view($player->player_id);
                    }
                    break;
                
           case 12:            // Bribe
           case 13:            // Blackmail
                    $this->page['notice'] = 'This card is played against an oppossing leader.<br />  View an enemy leader to play.';
                    $this->view($player->player_id);
                    break;
           case 14:            // End Contract, location_id is actually the offer value
                    $this->contract_ends($card, $player, $game, $target_id, $location_id);
                    break;
           case 15:
                $this->houseinterdict($card, $player, $target_id);
                break;
           case 16:
                $this->removehouseinterdict($card, $player, $target_id);
                break;
            case 17:     // Sabotage
                $this->sabotage($card, $player, $target_id);
                break;
            case 18:     // Espionage
                $this->espionage($card, $player, $target_id);
                break;
            case 19:    // Create house unit
                $this->create_house_unit($card, $player, $target_id);
                break;
            case 20:    // Economic Boom
                $this->economic_boom($card, $player);
                break;
            case 21:
                $this->bombardment($card, $player, $target_id);
                break;
            case 22:
                $this->air_raid($card, $player, $target_id);
                break;
            case 23:
                $this->hpg_blackout($card, $player);
                break;
            case 24:
                $this->star_league_memory_core($card, $player);
                break;
            case 25:
                $this->holy_shroud($card, $player);
                break;
            case 26:
                $this->germanium_supply($card, $player);
                break;
            case 27:
                $this->economic_sabotage($card, $player, $target_id);
                break;
            case 28:
                $this->embezzlement($card, $player, $target_id);
                break;
            case 29:
                $this->research($card, $player);
                break;
            case 30:
                $this->holy_shroud_2($card, $player, $target_id);
                break;
            case 31:
                $this->holy_shroud_3($card, $player, $target_id);
                break;
            case 32:
                $this->houseinterdict_1($card, $player, $target_id);
                break;
            case 33:
                $this->economic_fraud($card, $player);
                break;
            case 34:
                $this->nuclear_strike($card, $player, $target_id);
                break;
            case 35:
                $this->reinforcements($card, $player, $target_id);
                break;
            case 36:
                $this->regional_improvement($card, $player, $target_id);
                break;
            case 37:
                $this->fast_recharge($card, $player, $target_id);
                break;

            // etc... add more cards...
        }

    }
    
    /**
     * View all of the available cards in the game
     */
    function view_all()
    {
        $page = $this->page;
        
        $this->load->model('cardmodel');
        $page['cards'] = $this->cardmodel->get_all_types();
        
        $page['content'] = 'card_view_list';
        $this->load->view('template', $page);
        
    }  // end view_list
    
    /**
     * View the cards in a player's hand
     * 
     * @param type $player_id 
     */
    function view( $player_id = 0 )
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $player_id == 0 )
            redirect($this->config->item('base_url'), 'refresh');

        // Fetch the player
        $this->load->model('playermodel');
        $page['player'] = $this->playermodel->get_by_id($player_id);
        
        // Must be playing in this game and belong to this user
        // User is signed in
        // Player exists
        
        // TODO, this generates and erro if not logged in...
        if ( !isset($page['player']->user_id) || $page['player']->user_id != $this->ion_auth->get_user()->id )
            redirect($this->config->item('base_url'), 'refresh');

        // Fetch the game, used in the view for play links
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($page['player']->game_id);
        
        // Fetch cards
        $this->load->model('cardmodel');
        $page['cards'] = $this->cardmodel->get_by_player($player_id);
        
        $page['content'] = 'hand';
        $this->load->view('templatexml', $page);
    }
    
    function discard($card_id=0)
    {
        // Make sure variables are provided
        if ($card_id == 0)
        {
            $page['ERROR!'] = 'Cannot discard a card without a card id.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the card
        $this->load->model('cardmodel');
        $card = $this->cardmodel->get_by_id($card_id);
        
        // Make sure the card exists
        if ( !isset( $card->card_id ) )
        {
            $page['error'] = 'Cannor discard a card that is not found.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Obviously can't be in the deck or discard pile
        if ( $card->owner_id == 0 || $card->owner_id == null )
        {
            $page['error'] = 'Cannot discard a card when it is in the deck or discard pile.';
            $this->load->view('templatexml', $page);
            return;
        }

        // Fetch the owner
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($card->owner_id);
        
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($player->game_id);
        
        // Must be playing in this game and belong to this user
        // User is signed in
        // Player exists
        if ( $player->user_id != $this->ion_auth->get_user()->id )
        {
            $page['error'] = 'Cannot discard a card that is not yours.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Game must not be on hold
        $cardbeingplayed = $this->cardmodel->get_hold($game->game_id);
        if (isset($cardbeingplayed->card_id))
        {
            $this->page['error'] = 'Cannot discard a card until the current card in play is resolved.';
            $this->view($player->player_id);
            return;
        }
        
        // Confirm
        if ($this->session->flashdata('confirm') != 'YES')
        {
            $this->page['warning'] = 'Are you sure you want to discard this card?';
            $this->session->set_flashdata('confirm', 'YES');
            $this->view($player->player_id);
            return;
        }
        
        // Away we go!
        discard($card);
        game_message($game->game_id, $player->faction.' discarded a '.$card->title.' card from their hand.');   
        $this->page['notice'] = 'Card discarded!';
        $this->view($player->player_id);
    }

    /**
     *  Give a card to another player
     * @param type $player_id The player to give to
     * @param type $card_id  The card to give
     */
    function trade_cards($player_id = 0, $card_id = 0)
    {
        // Make sure variables are provided
        if ($player_id == 0)
        {
            $page['error'] = 'Cannot trade a card without a player id provided.';
            $this->load->view('templatexml', $page);            
            return;
        }
        
        // Target must exist
        $this->load->model('playermodel');
        $target = $this->playermodel->get_by_id($player_id);
        if (!isset($target->player_id))
        {
            $page['error'] = 'Cannot trade a card to a player that is not found.';
            $this->load->view('templatexml', $page);            
            return;
        }
        
        // Do not allow players to give cards to eliminated players.
        if ($target->turn_order == 0)
        {
            $page['error'] = 'Cannot trade a card to an eliminated player.';
            $this->load->view('templatexml', $page);            
            return;            
        }
        
        // Can't have more than the max hand size
        $this->load->model('gamemodel');
        $this->load->model('cardmodel');
        $game = $this->gamemodel->get_by_id($target->game_id);
        $cards = $this->cardmodel->get_by_player($target->player_id);
        if ( count($cards) >= $game->hand_size )
        {
            $page['error'] = 'Cannot give a card to a player with '.$game->hand_size.' or more cards.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        $player;    // This player, the player giving the card
        $players = $this->playermodel->get_by_game($target->game_id);
        foreach($players as $p)
        {
            if ($p->user_id == $this->page['user']->id)
                $player = $p;
        }        
        if (!isset($player->player_id))
        {
            $page['error'] = 'Cannot trade a card when you are not a player in the game.';
            $this->load->view('templatexml', $page);            
            return;
        }
        
        if ($card_id == 0)
        {
            // Load view
            $page['target'] = $target;
            $page['cards'] = $this->cardmodel->get_by_player($player->player_id);
            $this->load->view('trade_cards', $page);
            return;
        }
        
        // Card must exist, be owned by the player
        $card = $this->cardmodel->get_by_id($card_id);
        if ($card->owner_id != $player->player_id)
        {
            $page['error'] = 'No such card!';
            $this->load->view('templatexml', $page);            
            return;
        }
        
        // Card must not have been traded already while in play
        if ($card->traded == 1)
        {
            $page['error'] = 'Cannot trade a card that has been traded to you.';
            $this->load->view('templatexml', $page);            
            return;
        }
        
        // Away we go...
        $cardupdate = new stdClass();
        $cardupdate->card_id = $card_id;
        $cardupdate->owner_id = $target->player_id;
        $cardupdate->traded = 1;
        $this->cardmodel->update($card_id, $cardupdate);
        
        game_message($player->game_id, $player->faction.' gave '.$target->faction.' a card.');
        
        // Load player view
        $this->page['notice'] = 'You gave a card to '.$target->faction.'.';
        $this->view($player->player_id);
        
    }  // end trade cards
    
    private function technology3($card, $player)
    {
        discard($card);
        $this->page['notice'] = 'Tech +3 card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played a technology +3 card.');  
        tech_mod ($player, 3);
    }
    
    private function technology5($card, $player)
    {
        discard($card);
        $this->page['notice'] = 'Tech +5 card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played a technology +5 card.');
        tech_mod ($player, 5);
    }
    
    private function death_commandos($card, $player, $target_id)
    {
        if ( $target_id == 0 )
        {
            // Select a target for the attack
            $page['card'] = $card;
            $this->load->model('combatunitmodel');
            $this->db->where('combatunits.owner_id <>', $player->player_id);
            $page['combatunits'] = $this->combatunitmodel->get_by_game($player->game_id);
            $page['player'] = $player;

            $this->load->view('deathcommando', $page);
        }
        else
        {
            // Target is selected, validate attack
            $this->load->model('combatunitmodel');
            $combatunit = $this->combatunitmodel->get_by_id($target_id);

            // Target must exist
            if ( !isset($combatunit->combatunit_id) )
            {
                $this->page['error'] = 'No such target.';
                $this->view($player->player_id);
                return;
            }

            // Target must be oppossing force
            if ( $combatunit->owner_id == $player->player_id )
            {
                $this->page['error'] = 'Can\'t target your own units.';
                $this->view($player->player_id);
                return;
            }

            // Target must be alive
            if ( $combatunit->strength == 0 )
            {
                $this->page['error'] = 'Target is already destroyed.';
                $this->view($player->player_id);
                return;
            }

            // Go ahead and roll!
            $this->load->model('territorymodel');
            $territory = $this->territorymodel->get_by_id($combatunit->location_id);

            $dieroll = roll_dice(1,10);
            if ( $dieroll < 8 )
            {
                // Kill the unit
                game_message($player->game_id, $player->faction.' played a Death Commando card against '.$combatunit->name.' in '.$territory->name.' The attack is successful!');
                kill_unit($combatunit);
                $this->page['notice'] = 'Attack is successful.';
                update_territory($combatunit->location_id);
            }
            else
            {
                game_message($player->game_id, $player->faction.' played a Death Commando card against '.$combatunit->name.' in '.$territory->name.' The attack failed!');
                $this->page['notice'] = 'The attack failed.';
            }

            // result will be put into game result table for everyones 
            // benefit...
            discard($card);

            // Back to the play hand
            $this->view($player->player_id);
        }
        
    }  // end death_commandos
    
    /**
     * House interdict for 2 turns
     */
    private function houseinterdict($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'House Interdict';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($player->game_id);
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'Could not find the player you were refering to.';
            $this->load->view('templatexml', $page);
        }
        
        $countofplayers = 0;
        foreach($players as $p)
        {
            if ($p->turn_order != 0)
                $countofplayers++;
        }
        
        $target->house_interdict = $countofplayers * 2; // Two turns 
        $this->playermodel->update($target_id, $target);
        
        $this->load->model('gamemodel');
        $gameinfo = $this->gamemodel->get_by_id($target->game_id);
        $currentplayer = $this->playermodel->get_by_id($gameinfo->player_id_playing);
        game_message($target->game_id, $player->faction.' plays House Interdict card on '.$target->faction.' in round '.$gameinfo->turn.' during '.$currentplayer->faction.'\'s turn!');

        discard($card);
        $this->page['notice'] = 'House Interdict card played.';
        $this->view($player->player_id);
    }
    
    /**
     * House interdict for 1 turn
     */
    private function houseinterdict_1($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'House Interdict';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $this->load->model('playermodel');
        $players = $this->playermodel->get_by_game($player->game_id);
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'Could not find the player you were refering to.';
            $this->load->view('templatexml', $page);
        }
        
        $countofplayers = 0;
        foreach($players as $p)
        {
            if ($p->turn_order != 0)
                $countofplayers++;
        }
        
        $target->house_interdict = $countofplayers; // One turns
        $this->playermodel->update($target_id, $target);
        
        game_message($target->game_id, $player->faction.' plays House Interdict card on '.$target->faction.'!');

        discard($card);
        $this->page['notice'] = 'House Interdict 1 card played.';
        $this->view($player->player_id);
    }  // end houseinterdict_1
    
    private function removehouseinterdict($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Lift House Interdict';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be under house interdict
        $target = $this->playermodel->get_by_id($target_id);
        
        // The standard Lift House Interdict does not work on Terra Interdict
        $this->load->model('gamemodel');
        $currentgame = $this->gamemodel->get_by_id($player->game_id);
        // Check if use_terra_interdict == 1
        if ($currentgame->use_terra_interdict == TRUE)
        {
            // Check if the target player controls Terra
            $this->load->model('territorymodel');
            $terracheck = $this->territorymodel->player_owns_terra($target);
            if ($terracheck)
            {
                $this->page['error'] = 'Comstar Lift House Interdict does not work on the Comstar Terran Interdict.';
                $this->view($player->player_id);
                return;
            }
        }
        
        if (!$target->house_interdict)
        {
            $this->page['error'] = 'Target is not under Comstar House Interdict!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        $target->house_interdict = false;
        $this->playermodel->update($target_id, $target);
        
        game_message($target->game_id, $player->faction.' plays Comstar Lift House Interdict card on '.$target->faction.'!');
        
        discard($card);
        $this->page['notice'] = 'Comstar Lift House Interdict card played.';
        $this->view($player->player_id);
    }
    
    private function sabotage($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Sabotage';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Target must not be eliminated!
        if ($target->turn_order == 0)
        {
            $this->page['error'] = 'You can\'t play this card against an eliminated player!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        discard($card);
        $this->page['notice'] = 'Sabotage card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played a sabotage card against '.$target->faction.'.');
        tech_mod ($target, -3);
    }  // end sabotage
    
    private function espionage($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Espionage';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Target must not be eliminated!
        if ($target->turn_order == 0)
        {
            $this->page['error'] = 'You can\'t play this card against an eliminated player!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        discard($card);
        $this->page['notice'] = 'Espionage card played!';
        $this->view($player->player_id);
        game_message($player->game_id, 
                $player->faction.' played an espionage card against '.$target->faction.'.');
        tech_mod ($target,-3);
        tech_mod ($player,3); 
        
    }  // end espionage
    
    /**
     * Create a new house unit
     * Optional strength and cost:
     * - Green,     str 3   Cost 6
     * - Regular,   str 4   Cost 8
     * - Veteran,   str 6   Cost 12
     * - Elite,     str 8   Cost 16
     */
    private function create_house_unit($card, $player, $strength, $location_id)
    {
        // If no location, show the view
        if ($location_id == -1)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['content'] = 'create_house_unit';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Valid strength
        $cost;
        if ($strength == 3)
            $cost = 6;
        else if ($strength == 4)
            $cost = 8;
        else if ($strength == 6)
            $cost = 12;
        else if ($strength == 8)
            $cost = 16;
        else
        {
            $this->page['error'] = 'Invalid strength!';
            $this->view($player->player_id);
            return;
        }
        
        // Must be able to afford it
        if ($player->cbills < $cost)
        {
            $this->page['error'] = 'You cannot afford it!';
            $this->view($player->player_id);
            return;
        }
        
        // Location must exist
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($location_id);
        if (!isset($territory->territory_id))
        {
            $this->page['error'] = 'Invalid location!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go...
        $unit->name = $this->input->post('name');
        if (!isset($unit->name) || $unit->name == '')
        {
            $this->page['error'] = 'Invalid unit name!';
            $this->view($player->player_id);
            return;
        }
        
        $this->load->model('combatunitmodel');
        $this->combatunitmodel->create($unit);
        
        discard($card);
        $this->page['notice'] = 'Create House Unit card played!';
        $this->view($player->player_id);
        game_message($player->game_id, 
                $player->faction.' created a new House Unit '.$unit->name.','.$strength.' in '.$territory->name.'.');
        
    }  // end create_house_unit
        
    /**
     * +10 CBills
     */
    private function economic_boom($card, $player)
    {   
        // Away we go
        $player->money += 10;
        $this->playermodel->update($player->player_id, $player);
        
        game_message($player->game_id, $player->faction.' plays Economic Boom! +10 CBills!');
        
        discard($card);
        $this->page['notice'] = 'Economic Boom card played.';
        $this->view($player->player_id);
        
    }  // end economic_boom
    
    /**
     * +25 CBills, remove from the game after use
     */
    private function germanium_supply($card, $player)
    {   
        // Away we go
        $player->money += 25;
        $this->playermodel->update($player->player_id, $player);
        
        game_message($player->game_id, $player->faction.' plays Germanium Supply! +25 CBills! Card is removed from the game.');
        
        $this->cardmodel->delete($card->card_id);
        $this->page['notice'] = 'Germanium Supply card played.';
        $this->view($player->player_id);
        
    }  // end economic_boom
    
    private function bombardment($card, $player, $target_id)
    {   
        if ( $target_id == 0 )
        {
            // Select a target for the attack
            $page['card'] = $card;
            $this->load->model('combatunitmodel');
            $this->db->where('combatunits.owner_id <>', $player->player_id);
            $page['combatunits'] = $this->combatunitmodel->get_by_game_contested($player->game_id);
            $page['player'] = $player;

            $this->load->view('bombardment', $page);
        }
        else
        {
            // Target is selected, validate attack
            $this->load->model('combatunitmodel');
            $combatunit = $this->combatunitmodel->get_by_id($target_id);

            // Target must exist
            if ( !isset($combatunit->combatunit_id) )
            {
                $this->page['error'] = 'No such target.';
                $this->view($player->player_id);
                return;
            }       

            // Target must be oppossing force
            if ( $combatunit->owner_id == $player->player_id )
            {
                $this->page['error'] = 'Can\'t target your own units.';
                $this->view($player->player_id);
                return;
            }  

            // Target must be alive
            if ( $combatunit->strength == 0 && !$combatunit->die )
            {
                $this->page['error'] = 'Target is already destroyed.';
                $this->view($player->player_id);
                return;
            }  

            // Target must be in a contested border region
            $this->load->model('territorymodel');
            $territory = $this->territorymodel->get_by_id($combatunit->location_id);
            if (!$territory->is_contested)
            {
                $this->page['error'] = 'Target region must be contested!';
                $this->view($player->player_id);
                return;
            }
            $neighbors = $this->territorymodel->get_adjacent($territory->territory_id, $player->game_id);
            $is_border = false;
            foreach($neighbors as $t)
            {
                if ($t->player_id == $player->player_id)
                {
                    $is_border = true;
                    break;
                }
            }
            if (!$is_border && $territory->player_id != $player->player_id)
            {
                $this->page['error'] = 'Target region must border your territory!';
                $this->view($player->player_id);
                return;
            }
            
            // Go ahead and roll!
            $this->load->model('territorymodel');
            $territory = $this->territorymodel->get_by_id($combatunit->location_id);

            $dieroll = roll_dice(1,10);
            if ( $dieroll < 7 )
            {
                // Kill the unit
                game_message($player->game_id, $player->faction.' played a Bombardment card against '.$combatunit->name.' in '.$territory->name.'. The attack is successful!');
                kill_unit($combatunit);
                $this->page['notice'] = 'Attack is successful.';
                update_territory($combatunit->location_id);
            }
            else
            {
                game_message($player->game_id, $player->faction.' played a Bombardment card against '.$combatunit->name.' in '.$territory->name.'. The attack failed!');
                $this->page['notice'] = 'The attack failed.';
            }

            // result will be put into game result table for everyones 
            // benefit...
            discard($card);

            // Back to the play hand
            $this->view($player->player_id);
        }
        
    }  // end bombardment
    
    private function air_raid($card, $player, $target_id)
    {   
        if ( $target_id == 0 )
        {
            // Select a target for the attack
            $page['card'] = $card;
            $this->load->model('combatunitmodel');
            $this->db->where('combatunits.owner_id <>', $player->player_id);
            $page['combatunits'] = $this->combatunitmodel->get_by_game_contested($player->game_id);
            $page['player'] = $player;

            $this->load->view('air_raid', $page);
        }
        else
        {
            // Target is selected, validate attack
            $this->load->model('combatunitmodel');
            $combatunit = $this->combatunitmodel->get_by_id($target_id);

            // Target must exist
            if ( !isset($combatunit->combatunit_id) )
            {
                $this->page['error'] = 'No such target.';
                $this->view($player->player_id);
                return;
            }       

            // Target must be oppossing force
            if ( $combatunit->owner_id == $player->player_id )
            {
                $this->page['error'] = 'Can\'t target your own units.';
                $this->view($player->player_id);
                return;
            }  

            // Target must be alive
            if ( $combatunit->strength == 0 && !$combatunit->die)
            {
                $this->page['error'] = 'Target is already destroyed.';
                $this->view($player->player_id);
                return;
            }  

            // Target must be in a contested border region or your own territory
            $this->load->model('territorymodel');
            $territory = $this->territorymodel->get_by_id($combatunit->location_id);
            if (!$territory->is_contested)
            {
                $this->page['error'] = 'Target region must be contested!';
                $this->view($player->player_id);
                return;
            }
            $neighbors = $this->territorymodel->get_adjacent($territory->territory_id, $player->game_id);
            $is_border = false;
            foreach($neighbors as $t)
            {
                if ($t->player_id == $player->player_id)
                {
                    $is_border = true;
                    break;
                }
            }
            if (!$is_border && $territory->player_id != $player->player_id)
            {
                $this->page['error'] = 'Target region must border your territory!';
                $this->view($player->player_id);
                return;
            }
            
            // Go ahead and roll!
            $this->load->model('territorymodel');
            $territory = $this->territorymodel->get_by_id($combatunit->location_id);

            $dieroll = roll_dice(1,10);
            if ( $dieroll < 5 )
            {
                // Kill the unit
                game_message($player->game_id, $player->faction.' played an Air Raid card against '.$combatunit->name.' in '.$territory->name.'. The attack is successful!');
                kill_unit($combatunit);
                $this->page['notice'] = 'Attack is successful.';
                update_territory($combatunit->location_id);
            }
            else
            {
                game_message($player->game_id, $player->faction.' played an Air Raid card against '.$combatunit->name.' in '.$territory->name.'. The attack failed!');
                $this->page['notice'] = 'The attack failed.';
            }

            // result will be put into game result table for everyones 
            // benefit...
            discard($card);

            // Back to the play hand
            $this->view($player->player_id);
        }
        
    }  // end air_raid
    
    /**
     * Target mercenary unit (and leader) becomes a house unit
     */
    private function contract_of_duration($card, $player, $target_id)
    {   
        
    }
    
    /**
     * Target house unit becomes a merc unit and goes up for bid
     */
    private function loss_of_confidence($card, $player, $target_id)
    {   
        
    }
    
    /**
     * House Interdict for all players for two turns
     */
    private function hpg_blackout($card, $player)
    {
        $players = $this->playermodel->get_by_game($player->game_id);
        
        // Count up the active players
        $countofplayers = 0;
        foreach($players as $p)
        {
            if ($p->turn_order != 0)
                $countofplayers++;
        }
        
        // Interdict for everybody
        foreach($players as $p)
        {
            $pupdate = new stdClass();
            $pupdate->player_id = $p->player_id;
            $pupdate->house_interdict = $countofplayers * 2; // Two turns
            $this->playermodel->update($p->player_id, $pupdate);
        }
        
        // Burn the card
        discard($card);
        
        // Game message
        game_message($player->game_id, $player->faction.' played HPG Blackout!  House Interdict for all players.');
        $this->page['notice'] = 'HPG Blackout card played.';
        
        // Back to the play hand
        $this->view($player->player_id);
        
    }  // end hpg_blackout
    
    /**
     * +7 Technology, delete instead of discard
     */
    private function star_league_memory_core($card, $player)
    {
        $this->cardmodel->delete($card->card_id);       // delete instead of discard
        $this->page['notice'] = 'Tech +7 card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played a Star League Memory Core card.');
        tech_mod($player,7);
        
    }  // end star_league_memory_core
    
    /**
     * -3 Technology for all players
     */
    private function holy_shroud($card, $player)
    {
        game_message($player->game_id, $player->faction.' played Holy Shroud!');
        
        $players = $this->playermodel->get_by_game($player->game_id);
        foreach($players as $p)
            tech_mod($p,-3);
        
        // Burn the card
        discard($card);
        
        $this->page['notice'] = 'Everone\'s tech -3 card played.';
        
        // Back to the play hand
        $this->view($player->player_id);
        
    }  // end holy_shroud
    
    /**
     * Target player -5 technology, -3 if at or below 0 tech
     */
    private function holy_shroud_2($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Holy Shroud';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Target must not be eliminated!
        if ($target->turn_order == 0)
        {
            $this->page['error'] = 'You can\'t play this card against an eliminated player!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        game_message($player->game_id, $player->faction.' played a Holy Shroud card against '.$target->faction.'.');
        if ($target->tech_level > 0)
            tech_mod ($target, -5);
        else 
            tech_mod ($target, -3);

        discard($card);
        $this->page['notice'] = 'Holy Shroud card played!';
        $this->view($player->player_id);
    }  // end holy_shroud_2
    
    
    /**
     * Target player -3 technology, -1 if at or below 0 tech
     */
    private function holy_shroud_3($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Holy Shroud';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Target must not be eliminated!
        if ($target->turn_order == 0)
        {
            $this->page['error'] = 'You can\'t play this card against an eliminated player!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        game_message($player->game_id, $player->faction.' played a Holy Shroud card against '.$target->faction.'.');
        if ($target->tech_level > 0)
            tech_mod ($target, -3);
        else
            tech_mod ($target, -1);

        discard($card);
        $this->page['notice'] = 'Holy Shroud card played!';
        $this->view($player->player_id);
    }  // end holy_shroud_3
    
    /**
     * All players give you 1 CBill if able
     */
    private function economic_fraud($card, $player)
    {
        $cbills_stolen = 0;
        $players = $this->playermodel->get_by_game($player->game_id);
        foreach($players as $p)
        {
            if ($p->money > 0  && $p->turn_order != 0 && $p->player_id != $player->player_id)
            {
                $playerupdate = new stdClass();
                $playerupdate->player_id = $p->player_id;
                $playerupdate->money = $p->money - 1;
                $cbills_stolen++;
                $this->playermodel->update($playerupdate->player_id, $playerupdate);
            }
        }
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money + $cbills_stolen;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        // Burn the card
        discard($card);
        
        // Game message
        game_message($player->game_id, $player->faction.' played Economic Fraud!  All players give '.$player->faction.' 1 CBill if able. '.$player->faction.' gained '.$cbills_stolen.' CBills.');
        $this->page['notice'] = 'Economic Fraud card played.';
        
        // Back to the play hand
        $this->view($player->player_id);
        
    }  // end economic_fraud
    
    /**
     * Target player -3 money
     */
    private function economic_sabotage($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Economic Sabotage';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        $playerupdate = new stdClass();
        $playerupdate->player_id = $target->player_id;
        $playerupdate->money = $target->money - 3;
        if ( $playerupdate->money < 0 )
            $playerupdate->money = 0;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        discard($card);
        $this->page['notice'] = 'Economic Sabotage card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played an economic sabotage card against '.$target->faction.'.  CBills decreased to '.$playerupdate->money.'.');
        
    }  // end economic_sabotage
    
    /**
     * Target player -3 money
     */
    private function embezzlement($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Embezzlement';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Target must not be eliminated!
        if ($target->turn_order == 0)
        {
            $this->page['error'] = 'You can\'t play this card against an eliminated player!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        $playerupdate = new stdClass();
        $playerupdate->player_id = $target->player_id;
        $cbills_stolen = 3;
        $playerupdate->money = $target->money - 3;
        if ($target->money < 3)
        {
            $playerupdate->money = 0;
            $cbills_stolen = $target->money;
        }
        $target_money = $playerupdate->money;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money + $cbills_stolen;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
            
        discard($card);
        $this->page['notice'] = 'Embezzlement card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played an embezzelment card against '.$target->faction.'.  CBills decreased to '.$target_money.'. '.$player->faction.' gains '.$cbills_stolen.' CBills.');
        
    }  // end embezzlement
    
    /**
     * Free technology roll
     */
    private function research($card, $player)
    {
        $dieroll = roll_dice(1,10);
        if ( $dieroll == 10 )
        {
            tech_mod ($player, 2);
            game_message($player->game_id, $player->faction.' played a research card.  +2 Technology!');
        }
        else if ( $dieroll > 6 )
        {
            tech_mod ($player, 1);
            game_message($player->game_id, $player->faction.' played a research card.  +1 Technology!');
        }
        else
        {
            game_message($player->game_id, $player->faction.' played a research card.  Technology roll failed!');
        }
        
        discard($card);
        $this->page['notice'] = 'Research card played!';
        $this->view($player->player_id);
    }  // end research
    
    /**
     * Steal 1D6 CBills from a neighboring player
     */
    private function border_raid($card, $player, $target_id)
    {
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['player'] = $player;
            $page['players'] = $this->playermodel->get_by_game($player->game_id);
            $page['content'] = 'targetplayer';
            $page['title'] = 'Border Raid';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must be real
        $target = $this->playermodel->get_by_id($target_id);
        if (!isset($target->player_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Must not be this player!
        if ($target_id == $player->player_id)
        {
            $this->page['error'] = 'You can\'t target yourself!';
            $this->view($player->player_id);
            return;
        }
        
        // Must border this player
        $border = $this->db->query(' LIMIT 1')->row();
        if (!isset($border->territory_id))
        {
            $this->page['error'] = 'Target must border your territory!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go
        $cbills_stolen = roll_dice(1, 6);
        if ($cbills_stolen > $target->money)
            $cbills_stolen = $target->money;

        $playerupdate = new stdClass();
        $playerupdate->player_id = $target->player_id;
        $playerupdate->money = $target->money - $cbills_stolen;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->money = $player->money + $cbills_stolen;
        $this->playermodel->update($playerupdate->player_id, $playerupdate);
        
        discard($card);
        $this->page['notice'] = 'Border Raid card played!';
        $this->view($player->player_id);
        game_message($player->game_id, $player->faction.' played a Border Raid card against '.$target->faction.'. '.$target->faction.' loses '.$cbills_stolen.' CBills. '.$player->faction.' gains '.$cbills_stolen.' cbills.');
        
    }  //end border_raid
    
    /**
     * Pay 5 CBills to upgrade any mech unit to 4 strength in a region you control
     * Play during the production phase
     */
    private function reinforcements($card, $player, $target_id)
    {
        // Must be the players turn
        $game = $this->gamemodel->get_by_id($player->game_id);
        if ($game->player_id_playing != $player->player_id)
        {
            $this->page['error'] = 'Cannot reinforce a unit when it is not your turn.';
            $this->view($player->player_id, $page);
            return;
        }
        
        // If no target, show the view
        if ($target_id == 0)
        {
            $page['card'] = $card;
            $page['content'] = 'reinforcements';
            $page['targets'] = $this->db->query('SELECT combatunits.*, map.name AS location_name FROM combatunits 
                join territories on territories.territory_id=combatunits.location_id
                join map on map.map_id=territories.map_id
                where owner_id='.$player->player_id.' AND strength>0 AND strength<4 AND is_conventional=0 AND is_elemental=0')->result();
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Target must exist and be owned by the playing player
        $this->load->model('combatunitmodel');
        $target = $this->combatunitmodel->get_by_id($target_id);
        if ($target->owner_id != $player->player_id)
        {
            $this->page['error'] = 'Cannot reinforce a unit you do not own!';
            $this->view($player->player_id);
            return;
        }

        // Must be alive
        if ($target->strength == 0)
        {
            $this->page['error'] = 'Cannot reinforce a dead unit!';
            $this->view($player->player_id);
            return;
        }
        
        // Must be a mech unit
        if ($target->is_elemental || $target->is_conventional)
        {
            $this->page['error'] = 'Cannot reinforce a non-mech unit!';
            $this->view($player->player_id);
            return;
        }
        
        // Must have the money
        $this->db->trans_start();
        $player = $this->playermodel->get_by_id($player->player_id);
        if ($player->money < 5)
        {
            $this->db->trans_complete();
            $this->page['error'] = 'Cannot reinforce a unit without paying the local forces!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go...
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($target->location_id);
        $this->db->query('UPDATE players SET money=money-5 WHERE player_id='.$player->player_id);
        $this->db->query('UPDATE combatunits SET strength=4 WHERE combatunit_id='.$target_id);
        
        discard($card);
        game_message($player->game_id, $player->faction.' played a Reinforcement card on '.$target->name.' located in '.$territory->name.'. '.$target->name.' reinforced to strength 4.');
        update_territory($target->location_id);
        
        $this->db->trans_complete();
        $this->page['notice'] = 'Reinforcement card played!';
        $this->view($player->player_id);
        
    }  // end reinforcements
    
    /**
     * Nuclear strike
     * Target a contested region in which you have combat units.
     * Roll a strength 7 attack on all enemy combat units present.
     * Automatically damage any factory.
     * Territory resource -1 permanantly.
     */
    private function nuclear_strike($card, $player, $target_id)
    {
        $this->load->model('combatlogmodel');
        $this->load->model('combatunitmodel');
        $this->load->model('territorymodel');
        
        // If no target is specified, show the form
        if ($target_id == 0)
        {
            $page['targets']    = $this->combatlogmodel->get_by_player($player->player_id);
            $page['card']       = $card;
            $page['player']     = $player;
            $page['content']    = 'nuclear_strike';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Fetch the target territory
        $territory = $this->territorymodel->get_by_id($target_id);
        if (!isset($territory->territory_id))
        {
            $this->page['error'] = 'No such territory!';
            $this->view($player->player_id);
            return;
        }
        
        // Fetch the target player
        $targetplayer = $this->playermodel->get_by_id($territory->player_id);
        
        // Must be playing in same game as this player
        if ($player->game_id != $territory->game_id)
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        
        // Must have all casualties assigned
        $log = $this->combatlogmodel->get_by_player_territory($player->player_id, $territory->territory_id);
        if (!isset($log->combatlog_id))
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }
        if ($log->casualties_owed > 0)
        {
            $this->page['error'] = 'Must have all casualties assigned!';
            $this->view($player->player_id);
            return;
        }
        
        // Must have non-kia units left in the territory
        $units = $this->combatunitmodel->get_by_player_territory($player->player_id, $territory->territory_id);
        if (count($units) == 0)
        {
            $this->page['error'] = 'Must have at least one active unit to carry out the attack!';
            $this->view($player->player_id);
            return;
        }
        
        // Must have the money
        $this->db->trans_start();
        if ($player->money < 10)
        {
            $this->db->trans_complete();
            $this->page['error'] = 'You don\'t have the money!';
            $this->view($player->player_id);
            return;
        }
        
        // Away we go!!!
        discard($card);
        game_message($player->game_id, $player->faction.' played a Nuclear Strike card on '.$territory->name.'!');
        
        // Pay 10 money
        $this->db->query('UPDATE players SET money=money-10 WHERE player_id='.$player->player_id);
        
        // Gather up all victims
        $targets = $this->combatunitmodel->get_by_location($territory->territory_id);
        foreach ($targets as $t)
        {
            // Roll a 7 attack on each non-kia, non-being built, oppossing unit
            // Roll a 3 attack on each non-kia, non-being built, friendly unit
            if (!$t->die && $t->strength > 0)
            {
                $dieroll = roll_dice(1, 10);
                if (($t->owner_id != $player->player_id && $dieroll < 8) || (($t->owner_id == $player->player_id && $dieroll < 4)))
                {
                    // DIE, and break any combinations if applicable
                    $combatunitupdate = new stdClass();
                    $combatunitupdate->combatunit_id    = $t->combatunit_id;
                    $combatunitupdate->die              = 1;
                    $combatunitupdate->combine_with     = null;
                    $combatunitupdate->combined_by       = null;
                    
                    // Break combinations if any
                    if ($t->combine_with)
                    {
                        $combo = $this->combatunitmodel->get_by_id($t->combine_with);
                        if (isset($combo->combatunit_id))
                        {
                            $comboupdate = new stdClass();
                            $comboupdate->combatunit_id = $combo->combatunit_id;
                            $comboupdate->combine_with = null;
                            $this->combatunitmodel->update($combo->combatunit_id, $comboupdate);
                        }
                    }
                    
                    $this->combatunitmodel->update($t->combatunit_id, $combatunitupdate);
                    game_message($player->game_id, $t->name.' destroyed by the nuclear strike on '.$territory->name.'.');
                }
            }  // end attack check
            
        }  // end foreach
        
        // Reduce the resource of this territory by 1
        $tupdate = new stdClass();
        $tupdate->territory_id = $territory->territory_id;
        $tupdate->resource = ($territory->resource > 0 ? $territory->resource - 1 : $territory->resource);
        $this->territorymodel->update($territory->territory_id, $tupdate);
        game_message($player->game_id, $territory->name.' resources was reduced due to a nuclear strike.');

        // Damage or destroy any factory present
        $this->load->model('factorymodel');
        $factory = $this->factorymodel->get_by_location($territory->territory_id);
        if (isset($factory->factory_id))
        {
            if ($factory->is_damaged)
            {
                // Destroyed
                $this->factorymodel->delete($factory->factory_id);
                game_message($player->game_id, 'The factory at '.$territory->name.' is destroyed by a nuclear strike! Minus one Technology.');
                tech_mod($targetplayer, -1);
            }
            else
            {
                // Damaged
                $factoryupdate = new stdClass();
                $factoryupdate->factory_id = $factory->factory_id;
                $factoryupdate->is_damaged = true;
                $this->factorymodel->update($factory->factory_id, $factoryupdate);
                game_message($player->game_id, 'The factory at '.$territory->name.' is damaged by a nuclear strike!');
            }
        }  // factory check
        
        update_territory($territory->territory_id);
        $this->db->trans_complete();
        $this->page['notice'] = 'Nuclear Strike Launched!';
        $this->view($player->player_id);
        
    }  // end nuclear_strike
    
    /**
     * End a mercenary contract and place them up for bid by all players
     */
    private function contract_ends($card, $player, $game, $target_id, $offer=-1)
    {
        // Make sure a target is specified
        if($target_id == 0)
        {
            $this->load->model('combatunitmodel');
            $page['mercs'] = $this->combatunitmodel->all_mercs($player->game_id);

            $page['card'] = $card;
            $page['player'] = $player;
            $page['content'] = 'contract_ends';
            $this->load->view('templatexml', $page);
            return;
        }

        // Fetch target
        $this->load->model('combatunitmodel');
        $merc = $this->combatunitmodel->get_by_id($target_id);
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($merc->location_id);

        // Must be playing in same game as this player
        if ($player->game_id != $territory->game_id)
        {
            $this->page['error'] = 'No such target!';
            $this->view($player->player_id);
            return;
        }

        // Must be a merc
        if (!$merc->is_merc)
        {
            $this->page['error'] = 'Not a mercenary!';
            $this->view($player->player_id);
            return;
        }

        // Must be alive
        if ($merc->strength == 0)
        {
            $this->page['error'] = 'That unit is destroyed!';
            $this->view($player->player_id);
            return;
        }

        // Owner must have all casualties assigned in the mercs location
        $combatlog = $this->db->query('select casualties_owed from combatlog 
            where territory_id='.$merc->location_id.' 
                and player_id='.$merc->owner_id)->row();

        if (isset($combatlog->casualties_owed))
        {
            if ($combatlog->casualties_owed > 0)
            {
                $this->page['error'] = 'Target Mercenary owner must have all casualties assigned before you can play this card! Trust us on this one...';
                $this->view($player->player_id);
                return;
            }
        }

        // Must not be the Mercenary Phase!
        if ($game->phase == 'Mercenary Phase')
        {
            $this->page['error'] = 'You can\'t play a Contract Ends card during the Mercenary Phase! Trust us on this one...';
            $this->view($player->player_id);
            return;
        }
        
        // Get the offer being submitted with this contract ends, if not set, view the form
        log_message('error', 'The offer is '.$offer);
        if ($offer == -1)
        {
            $page['card'] = $card;
            $page['merc'] = $merc;
            $page['content'] = 'mercbid';
            $this->load->view('templatexml', $page);
            return;
        }
                
        // Offer must be a positive integer...
        // ...
        
        // Away we go!

        // Get all mercs with the same name, sorted by combatunit_id
        $mercs = $this->combatunitmodel->get_by_game_name($player->game_id, $merc->name);

        // Place game in a hold until resolved
        $cardupdate = new stdClass();
        $cardupdate->card_id = $card->card_id;
        $cardupdate->owner_id = 0;
        $cardupdate->being_played = true;
        $cardupdate->target_id = $mercs[0]->combatunit_id;

        if ($this->debug > 1) log_message('error', 'CONTRACT ENDS ON '.$mercs[0]->combatunit_id);

        $this->cardmodel->update($card->card_id, $cardupdate);

        // Do the same for any combatunit in this game with the same name
        foreach($mercs as $m)
        {
            log_message('error', 'Contract Ends cuid is '.$m->combatunit_id);
            if ($m->name == $merc->name)
            {
                unset($mercupdate);
                $mercupdate = new stdClass();
                $mercupdate->combatunit_id = $m->combatunit_id;
                $mercupdate->owner_id = null;
                $mercupdate->loaded_in_id = null;
                $mercupdate->target_id = null;

                // And the combo
                if (isset($m->combine_with))
                {
                    $mercupdate->combine_with = null;
                    $mercupdate->combined_by = null;

                    $combo = $this->combatunitmodel->get_by_id($m->combine_with);
                    if (isset($combo->combatunit_id))
                    {
                        $comboupdate = new stdClass();
                        $comboupdate->combatunit_id = $combo->combatunit_id;
                        $comboupdate->combine_with = null;
                        $comboupdate->combined_by = null;
                        $this->combatunitmodel->update($combo->combatunit_id, $comboupdate);
                    }
                }

                $this->combatunitmodel->update($m->combatunit_id, $mercupdate);
            }
        }

        // check for associated leader
        $leaders = $this->db->query('select * from leaders where game_id = '.$player->game_id.' and associated_units = "'.$merc->name.'"')->result();
        if (count($leaders) > 0)
        {
            $this->load->model('leadermodel');
            foreach($leaders as $l)
            {
                $l->controlling_house_id = null;
                $l->allegiance_to_house_id = null;
                $this->leadermodel->update($l->leader_id, $l);
            }
        }

        // Setup an offer for each player in the game
        $players = $this->playermodel->get_by_game($player->game_id);
        foreach($players as $p)
        {
            if ($p->player_id == $player->player_id)
            {
                merc_bid($p, $mercs[0], $offer, false);
            }
            else if ($p->turn_order == 0)
            {
                merc_bid($p, $mercs[0], 0, false);
            }
            else
            {
                merc_bid($p, $mercs[0], -1, false);
            }
        }

        if ($this->debug > 3) log_message('error', 'cards/contract ends combatunit id is '.$mercs[0]->combatunit_id);

        // Game message and notice
        $target = $this->playermodel->get_by_id($merc->owner_id);
        game_message($player->game_id, $player->faction.' played Contract Ends card on '.$merc->name.' from '.$target->faction.'!  Check the Mercenaries link to place your bid.');

        // Email other players that action is required
        email_game($player->game_id, 'The contract for Mercenary unit '.$merc->name.' is up for bid in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                $game->title.
                '</a>.');
        
        // Update game last action
        $this->load->model('gamemodel');
        $gameupdate                 = new stdClass();
        $gameupdate->game_id        = $game->game_id;
        $gameupdate->last_action    = null;
        $this->gamemodel->update($game->game_id, $gameupdate);
        
        $this->page['notice'] = $merc->name.' contract ended!';
        $this->view($player->player_id);
        
    }  // end end_contract
    
    /**
     * Create a new merc unit in a specified territory
     */
    private function create_merc_unit($card, $player, $target_id)
    {
        if ($target_id == 0)
        {
            $this->load->model('territorymodel');
            $page['territories'] = $this->territorymodel->get_by_player($player->player_id);
            $page['card'] = $card;
            $this->load->view('createmerc', $page);
        }
        else
        {
            // Get merc name
            $name = $this->input->post('data');

            // Get location
            $this->load->model('territorymodel');
            $location = $this->territorymodel->get_by_id($target_id);

            // Player must own the location
            if ($player->player_id != $location->player_id)
            {
                $this->page['error'] = 'You do not own that location!';
                $this->view($player->player_id);
                return;
            }

            // Away we go
            $this->load->model('combatunitmodel');
            $merc = new stdClass();
            $merc->name = $name;
            $merc->game_id = $player->game_id;
            $merc->is_merc = true;
            $merc->owner_id = $player->player_id;
            $merc->location_id = $location->territory_id;
            $merc->strength = 5;
            $merc->being_built = false;
            $merc->prewar_strength = 5;
            $this->combatunitmodel->create($merc);

            // Set the player playing to NOT DONE if in the combat phase
            // This is to make sure they set their targets properly
            if ($player->combat_done)
            {
                unset($playerupdate);
                $playerupdate = new stdClass();
                $playerupdate->player_id = $player->player_id;
                $playerupdate->combat_done = 0;
                $this->playermodel->update($player->player_id, $playerupdate);
            }
            
            // If it is the combat phase and there are enemy forces in the territory
            $this->load->model('gamemodel');
            $game = $this->gamemodel->get_by_id($merc->game_id);            
            if ($game->phase=="Combat")
            {
                // Generate combat logs and auto-assign targets as needed
                generate_combat_logs($location, $game);
            }
            
            discard($card);
            game_message($player->game_id, $player->faction.' played a Create Mercenary card! '.$merc->name.', 5 is created on '.$location->name.'.');
            update_territory($merc->location_id);
            $this->page['notice'] = 'New \'Merc unit card played!';
            $this->view($player->player_id);
        }
    }  // end create_merc_unit
    
    /**
    * Pay 5 Cbills to increase a region’s resources by 2.  May only be used on region with 3 or less resource.  
    * Production phase only.
    */
   private function regional_improvement($card, $player, $target_id=0)
   {
        // Must be the players turn
        $game = $this->gamemodel->get_by_id($player->game_id);
        if ($game->player_id_playing != $player->player_id)
        {
            $this->page['error'] = 'Cannot improve a region when it is not your turn.';
            $this->view($player->player_id, $page);
            return;
        }
       
        // Check for a selected target, if none, display the select target view
        $this->load->model('territorymodel');
        if ($target_id === 0)
        {
            $page['title']      = 'Select Target for Regional Improvements';
            $this->db->where('resource <', 4);
            $page['regions']    = $this->territorymodel->get_by_player($player->player_id);
            $page['card']       = $card;
            $page['player']     = $player;
            $page['content']    = 'target_region';
            $this->load->view('templatexml', $page);
            return;
        }

        // Start transaction
        $this->db->trans_start();

        // Fetch the target territory
        $territory = $this->territorymodel->get_by_id($target_id);
        if (!isset($territory->territory_id))
        {
            $this->page['error'] = 'No such territory!';
            $this->view($player->player_id);
            $this->db->trans_complete();
            return;
         }

        // Must be owned by this player
        if ($territory->player_id != $player->player_id)
        {
            $this->page['error'] = 'You do not own that territory!';
            $this->view($player->player_id);
            $this->db->trans_complete();
            return;
        }

        // Must be less than 4 resource
        if ($territory->resource > 3)
        {
            $this->page['error'] = 'That territory is already developed!';
            $this->view($player->player_id);
            $this->db->trans_complete();
            return;
        }

        // Must have the money available
        if ($player->money < 5)
         {
            $this->page['error'] = 'You don\’t have enough money!';
            $this->view($player->player_id);
            $this->db->trans_complete();
            return;
         }

        // Pay the money
        $this->db->query('UPDATE players SET money=money-5 WHERE player_id='.$player->player_id);

        // Improve the region
        $this->db->query('UPDATE territories SET resource=resource+2 WHERE territory_id='.$territory->territory_id);
        update_territory($territory->territory_id);
        
        // Burn the card
        discard($card);	

        // Game message
        game_message($player->game_id, $player->faction.' played a Regional Improvement card on '.$territory->name.'. '.$territory->name.' resource increased from '.$territory->resource.' to '.($territory->resource + 2).'.');

        // All Done!
        $this->db->trans_complete();
        $this->page['notice'] = 'Regional Improvements Card Played!';
        $this->view($player->player_id);

   }  // end regional_improvement
    
   /**
    * Target friendly jumpship +1 movement
    */
    private function fast_recharge($card, $player, $target_id=0)
    {
	$page = $this->page;
        $this->load->model('jumpshipmodel');
        
	// Must be the players turn
	$game = $this->gamemodel->get_by_id($player->game_id);
	if ($game->player_id_playing != $player->player_id)
	{
            $this->page['error'] = 'Cannot be played when it is not your turn.';
            $this->view($player->player_id, $page);
            return;
	}
	
	if ($target_id === 0)
	{
            // Show the target form
            $page['jumpships'] = $this->jumpshipmodel->get_by_player($player->player_id);
            $page['card'] = $card;
            $page['title'] = 'Fast Recharge';
            $page['content'] = 'jumpship_target';
            $this->load->view('templatexml', $page);
            return;
	}
	
	// Target must exist and belong to the playing player
	
	$jumpship = $this->jumpshipmodel->get_by_id($target_id);
	if (!isset($jumpship->jumpship_id))
	{
            $this->page['error'] = 'Error 1.';
            $this->view($player->player_id, $page);
            return;
	}
	if ($jumpship->owner_id != $player->player_id)
	{
            $this->page['error'] = 'Error 2.';
            $this->view($player->player_id, $page);
            return;
	}
	
	// Target must not be under construction
	if ($jumpship->being_built)
	{
            $this->page['error'] = 'Error 3.';
            $this->view($player->player_id, $page);
            return;
	}
	
	// Away we go...
	// Start transaction
	$this->db->trans_start();
	$this->load->model('territorymodel');
	
	$jumpshipupdate = new stdClass();
	$jumpshipupdate->moves_this_turn = $jumpship->moves_this_turn - 1;
	$this->jumpshipmodel->update($target_id, $jumpshipupdate);
	
	// Burn the card
	discard($card);	

	// Game message
        $this->load->model('territorymodel');
        $territory = $this->territorymodel->get_by_id($jumpship->location_id);
	game_message($player->game_id, $player->faction.' played a Fast Recharge card on Jumpship ('.$jumpship->capacity.') '.(isset($jumpship->name) ? $jumpship->name : '').' located in '.$territory->name.'. +1 Movement!');
	
	// All Done!
	$this->db->trans_complete();
	$this->page['notice'] = 'Fast Recharge Card Played!';
	$this->view($player->player_id);
	
    }  // end fast_recharge
   
   
} // end controller cards
