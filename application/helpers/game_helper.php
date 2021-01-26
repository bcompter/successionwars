<?php

if ( ! function_exists('game_message'))
{
    function game_message($game_id, $msg, $player_id=0)
    {
        $CI =& get_instance();
        $CI->load->model('gamemsgmodel');
        
        $gamemsg = new stdClass();
        $gamemsg->game_id = $game_id;
        $gamemsg->message = $msg;
        
        if ($player_id != 0)
            $gamemsg->player_id=$player_id;
        
        $CI->gamemsgmodel->create($gamemsg);
    }
}

if ( ! function_exists('bug_message'))
{
    function bug_message($bug_id, $user_id, $message)
    {
        $CI =& get_instance();
        $CI->load->model('buglogmodel');
        
        $log = new stdClass();
        $log->bug_id = $bug_id;
        $log->user_id = $user_id;
        $log->message = $message;
        
        $CI->buglogmodel->create($log);
    }
}

if ( ! function_exists('game_hold'))
{
    /**
     * To be deprecated...  use check_game_hold instead
     * @param type $game_id
     * @return boolean 
     */
    function game_hold($game_id)
    {
        $hold = false;
        
        $CI =& get_instance();
        
        $CI->db->where('game_id', $game_id);
        $CI->db->where('being_played', true);
        $num = $CI->db->count_all_results('cards');
        if ($num != 0)
            $hold = true;
        
        return $hold;
    }
}

if ( ! function_exists('check_game_hold'))
{
    /**
     * Check if there is a hold on the game
     * 
     * Returns false if there is no hold.
     * Otherwise, returns a string with the appropriate error message
     * 
     * @param type $game_id
     * @return boolean 
     */
    function check_game_hold($game_id)
    {
        $retval = false;
        
        $CI =& get_instance();
        
        $CI->db->where('game_id', $game_id);
        $CI->db->where('being_played', true);
        $num = $CI->db->count_all_results('cards');
        if ($num != 0)
            $retval = 'A card is currently being played that must be resolved first!';
        
        $CI->load->model('peripherymodel');
        $bids = $CI->peripherymodel->get_by_game($game_id);
        if(count($bids) > 0)
            $retval = 'There are periphery nations up for bid that must be resolved first!';
        
        $CI->load->model('combatunitmodel');
        $mercs = $CI->combatunitmodel->mercs_for_hire($game_id);
        if (count($mercs) > 0)
            $retval = 'A mercenary unit is up for bid!';
            
        return $retval;
    }
}

if ( ! function_exists('discard'))
{
    /**
     * Discard a card (owner_id = 0)
     * 
     * @param type $card 
     */
    function discard($card)
    {
        $CI =& get_instance();
        $cardupdate = new stdClass();
        $cardupdate->card_id = $card->card_id;
        $cardupdate->owner_id = 0;
        $CI->load->model('cardmodel');
        $CI->cardmodel->update($card->card_id, $cardupdate);
    }
}

if ( ! function_exists('update_territory'))
{
    function update_territory($t_id)
    {
        $CI =& get_instance();
        unset($time);
        $time = new DateTime();
        $time = $time->format('Y-m-d H:i:s');
        
        if (!isset($CI->territorymodel))
            $CI->load->model('territorymodel');
        
        unset($t);
        $t = new stdClass();
        $t->territory_id = $t_id;
        $t->last_update = $time;
        $CI->territorymodel->update($t_id, $t);
    }
}

/**
 * Bid on a merc unit
 */
if ( ! function_exists('merc_bid'))
{
    function merc_bid($player, $merc, $offer, $force)
    {
        log_message('error', 'merc_bid '.$player->player_id.' '.$merc->name.' '.$merc->combatunit_id.' '.$offer.' '.$force);

        $CI =& get_instance();
    
        $CI->load->model('offermodel');
        $CI->load->model('gamemodel');
        
        $game = $CI->gamemodel->get_by_id($player->game_id);

        // Add offer to the pile
        $newoffer = new stdClass();
        $newoffer->merc_id = $merc->combatunit_id;
        $newoffer->player_id = $player->player_id;
        if (isset($offer) && $offer >= 0)
        {
            $newoffer->offer = $offer; 
        }
        else
        {
            // Just setting up
            $CI->offermodel->create($newoffer);
            return;
        }

        $oldoffer = $CI->offermodel->get_by_merc_player($merc->combatunit_id, $player->player_id);
        if (count($oldoffer)==1)
            foreach($oldoffer as $oo)
                $CI->offermodel->update($oo->offer_id,$newoffer);            
        else  
            $CI->offermodel->create($newoffer);
        
        // Get all offers for this unit
        $offers = $CI->offermodel->get_by_merc($merc->combatunit_id);
        
        // do we have all offers in?
        $numOffers;
        foreach($offers as $o)
        {
            if (!isset($numOffers[$o->player_id]) && isset($o->offer))
            {
                $numOffers[$o->player_id] = $o;
            }
        }
        
        $CI->load->model('playermodel');
        $players = $CI->playermodel->get_by_game($player->game_id);

        if (count($numOffers) == count($players)) // then bidding is done
        {
            // Find highest offer(s)
            $highOffers;
            $highOfferValue = 0;

            foreach($offers as $o)
            {
                if ($o->offer > $highOfferValue)
                {
                    $highOfferValue = $o->offer;
                    unset($highOffers);
                    $highOffers[] = $o;
                }
                else if ($o->offer == $highOfferValue)
                {
                    $highOfferValue = $o->offer;
                    $highOffers[] = $o;
                }
            }
            
            $mercs = $CI->combatunitmodel->mercs($player->game_id);
            $allmercs;
            foreach($mercs as $m)
            {
                if ($m->name == $merc->name)
                {
                    $allmercs[] = $m;
                }
            }
            
            if ($highOfferValue == 0)
            {
                // Nobody bid!  Destroy unit... nobody likes them
                game_message($player->game_id, $merc->name.' disbands due to lack of contract offers!');
                foreach($allmercs as $m)
                {
                    // Delete instead of kill
                    $CI->combatunitmodel->delete($m->combatunit_id);

                    if ($game->phase == 'Mercenary Phase')
                    {
                        // skip to production and email current player
                        $game->phase = 'Production';
                        $CI->gamemodel->update($game->game_id, $game);
                        email_player($game->player_id_playing, 'Nobody contracted with the Mercenaries in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->player_id_playing.'">'.
                                $game->title.
                                '</a> so they disbanded!  Your turn has resumed.');
                    }
                }  // end foreach($allmercs as $m)
                
                // Delete asociated leaders as well...                
                $CI->load->model('leadermodel');
                $leaders = $CI->leadermodel->get_by_merc($player->game_id, $merc->name);
                if (count($leaders) > 0)
                {
                    foreach($leaders as $l)
                    {
                        game_message($player->game_id, $lmerc->name.' has shamefully retired in failure.');
                        //if ($this->debug > 3) log_message('error', 'Deleting leader! '.$l->leader_id);
                        $CI->leadermodel->delete($l->leader_id);
                    }
                }
                
            }  // end if ($highOfferValue == 0)
            else
            {
                // In the event of a tie, randomly select the winner
                $num = count($highOffers) - 1;
                $roll = rand(0,$num);
                
                // Pay up!
                $winner = $CI->playermodel->get_by_id($highOffers[$roll]->player_id);
                $winner->money = $winner->money - $highOffers[$roll]->offer;
                // TODO: Catch if money is negative, remove their bid and quit the bid function.
                
                $CI->playermodel->update($winner->player_id, $winner);
                game_message($winner->game_id, $winner->faction.' is the highest bidder for *'.$merc->name.' paying '.$highOffers[$roll]->offer.'.');
                
                // Switch allegiance!
                $merc->owner_id = $highOffers[$roll]->player_id;
                $strength = 0;
                $locations;
                foreach($allmercs as $m)
                {
                    unset($mercupdate);
                    $mercupdate = new stdClass();
                    $mercupdate->combatunit_id = $m->combatunit_id;
                    $mercupdate->owner_id = $highOffers[$roll]->player_id;
                    $CI->combatunitmodel->update($m->combatunit_id, $mercupdate);
                    $strength += $m->strength;
                    
                    // Check each territory for contested status!
                    // Check for contested territory and multi faction combat
                    // Skip if we are in the Mercenary Phase
                    $CI->load->model('territorymodel');
                    if ($game->phase != 'Mercenary Phase')
                    {
                        $location = $CI->territorymodel->get_by_id($m->location_id);
                        if ($CI->territorymodel->is_contested($m->location_id))
                        {
                            // Update territory if required
                            if (!$location->is_contested)
                            {
                                $tupdate = new stdClass();
                                $tupdate->territory_id = $location->territory_id;
                                $tupdate->is_contested = true;
                                $CI->territorymodel->update($tupdate->territory_id, $tupdate);
                            }
                            if (!isset($locations[$location->territory_id]))
                                $locations[$location->territory_id] = $location;

                        }
                    }
                }

                // check for associated leader
                $leaders = $CI->db->query('select * from leaders where game_id = '.$winner->game_id.' and associated_units = "'.$merc->name.'"')->result();
                if (count($leaders) > 0)
                {
                    $CI->load->model('leadermodel');
                    foreach($leaders as $l)
                    {
                        $l->controlling_house_id = $highOffers[$roll]->player_id;
                        $l->allegiance_to_house_id = $highOffers[$roll]->player_id;
                        $CI->leadermodel->update($l->leader_id, $l);
                    }
                } 

                // generate combat logs if required
                if ($game->phase == 'Combat' && isset($locations))
                {
                    foreach($locations as $l)
                    {
                        generate_combat_logs($l, $game);
                    }
                }
            }
            
            // delete all offers...
            $offers = $CI->db->query('select * from mercoffers join players on players.player_id=mercoffers.player_id where game_id='.$game->game_id)->result();
            foreach($offers as $offer)
            {
                log_message('error', 'Deleting offer id '.$offer->offer_id);
                $CI->offermodel->delete($offer->offer_id);
            }            

            // unset card in play if it exists
            $CI->load->model('cardmodel');
            $card = $CI->cardmodel->get_hold($player->game_id);
            if (isset($card->card_id))
            {
                $cardupdate = new stdClass();
                $cardupdate->being_played = false;
                $cardupdate->target_id = null;
                $CI->cardmodel->update($card->card_id, $cardupdate);
            }

            // Email applicable players
            if ($game->phase == 'Mercenary Phase' && isset($winner->player_id))
                email_player($winner->player_id, 'Bidding is complete in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                        $game->title.
                        '</a>.  Please place your Mercenary.');
            else
            {
                foreach($players as $p)
                {
                    if ($game->phase == 'Combat' && !$p->combat_done)
                        email_player($p->player_id, 'Bidding is complete in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                                $game->title.
                                '</a>.  The Combat phase has resumed.');
                    else if ($game->phase != 'Combat' && $p->player_id == $game->player_id_playing)
                        email_player($p->player_id, 'Bidding is complete in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                                $game->title.
                                '</a>.  Your turn has resumed.');
                }
            }  // end if applicable players 
        }
        // Output if a force
        if($force)
        {
            // Nothing more to see
            $page['notice'] = 'Bid forced to zero!';
            $CI->load->view('templatexml', $page);
            return;
        }            
    }
}

/**
 * 
 */
if ( ! function_exists('periphery_bid'))
{
    function periphery_bid($player, $periphery, $offer, $game, $force)
    {
        $CI =& get_instance();
        
        // Is this periphery open for bidding? (there are bids for the nation)
        $CI->load->model('peripherymodel');
        $bids = $CI->peripherymodel->get_by_territory($periphery->territory_id);
        
        if (count($bids) == 0)
        {
            // If not, must be the players turn
            if ($player->player_id != $game->player_id_playing)
            {
                $page['error'] = 'You can only open bidding on a Periphery nation when it is your turn!';
                $CI->load->view('templatexml', $page);
                return;
            }

            // Check for game hold...
            $error = check_game_hold($game->game_id);
            if ($error !== false)
            {
                $page['error'] = $error;
                $CI->load->view('templatexml', $page);
                return;
            }

            // Create a bid for each player
            $CI->load->model('playermodel');
            $players = $CI->playermodel->get_by_game($periphery->game_id);
            foreach($players as $p)
            {
                $bid = new stdClass();
                $bid->player_id = $p->player_id;
                $bid->nation_id = $periphery->territory_id;
                if ($p->player_id == $player->player_id)
                    $bid->offer = $offer;
                
                // Defeated players bid is zero
                if ($p->turn_order == 0)
                    $bid->offer = 0;
                
                $CI->peripherymodel->create($bid);
            }
            game_message($game->game_id, $periphery->name.' has been opened for bidding!  Place your bid to gain control.');

            // Email other players that action is required
            email_game($periphery->game_id, 'The periphery nation '.$periphery->name.' is up for bid in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$periphery->game_id.'">'.
                    $game->title.
                    '</a>.');
            
            $CI->load->model('gamemodel');
            $gameupdate                 = new stdClass();
            $gameupdate->game_id        = $game->game_id;
            $gameupdate->last_action    = null;
            $CI->gamemodel->update($game->game_id, $gameupdate);
        }
        else
        {
            // Update this players bid
            $allbids = true;    // Checking for all bids in
            $highbids;          // Tracking the highest bids
            $highoffer = 0;     // The high offer
            foreach($bids as $b)
            {
                if ($b->player_id == $player->player_id)
                {
                    $b->offer = $offer;
                    $CI->peripherymodel->update($b->bid_id, $b);
                }
                else
                {
                    if (!isset($b->offer) )
                        $allbids = false;
                }

                if ($b->offer > $highoffer)
                {
                    $highoffer = $b->offer;
                    unset($highbids);
                    $highbids[] = $b;
                }
                else if ($b->offer == $highoffer && $b->offer > 0)
                {
                    $highbids[] = $b;
                }
            }

            // Are all bids placed?
            if ($allbids)
            {
                // If yes, the highest bid wins (randomly determine ties)
                $numbids = count($highbids);
                if ($numbids > 0)
                {
                    if ($numbids > 1)
                    {
                        $roll = roll_dice(1, count($highbids)) - 1;     // -1 because we want an index
                        $winner = $highbids[$roll];
                    }
                    else
                    {
                        $winner = $highbids[0];
                    }

                    // Update the territory
                    $p = new stdClass();
                    $p->player_id = $winner->player_id;
                    $p->is_periphery = false;
                    $p->territory_id = $periphery->territory_id;
                    $CI->territorymodel->update($periphery->territory_id, $p);

                    // Pay for it
                    $winner = $CI->playermodel->get_by_id($winner->player_id);
                    $winner->money -= $highoffer;
                    $CI->playermodel->update($winner->player_id, $winner);

                    // Update all combat units
                    $CI->load->model('combatunitmodel');
                    $units = $CI->combatunitmodel->get_by_location($periphery->territory_id);
                    foreach($units as $u)
                    {
                        $uu = new stdClass();
                        $uu->combatunit_id = $u->combatunit_id;
                        $uu->owner_id = $winner->player_id;
                        $CI->combatunitmodel->update($uu->combatunit_id, $uu);
                    }

                    // Update all jumpships
                    $CI->load->model('jumpshipmodel');
                    $jumpships = $CI->jumpshipmodel->get_by_territory($periphery->territory_id);
                    foreach($jumpships as $j)
                    {
                        $jj = new stdClass();
                        $jj->jumpship_id = $j->jumpship_id;
                        $jj->owner_id = $winner->player_id;
                        $CI->jumpshipmodel->update($jj->jumpship_id, $jj);
                    }

                    update_territory($periphery->territory_id);
                    game_message($game->game_id, $winner->faction.' pays '.$highoffer.' and gains control of '.$periphery->name.'!');
                }
                else
                {
                    // If nobody bid, the periphery nation remains neutral
                    game_message($game->game_id, 'No bids! '.$periphery->name.' remains neutral!');
                }

                // Delete all bids
                foreach($bids as $b)
                {
                    $CI->peripherymodel->delete($b->bid_id);
                }
                
                // Email applicable players
                $CI->load->model('playermodel');
                $players = $CI->playermodel->get_by_game($periphery->game_id);
                foreach($players as $p)
                {
                    if ($game->phase == 'Combat' && !$p->combat_done)
                        email_player($p->player_id, 'Bidding is complete in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                                $game->title.
                                '</a>.  The Combat phase has resumed.');
                    else if ($game->phase != 'Combat' && $p->player_id == $game->player_id_playing)
                        email_player($p->player_id, 'Bidding is complete in the game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game->game_id.'">'.
                                $game->title.
                                '</a>.  Your turn has resumed.');
                }

            }  // end if all bids

        }
        
        // Output if a force
        if($force)
        {
            // Nothing more to see
            $page['notice'] = 'Bid forced to zero!';
            $CI->load->view('templatexml', $page);
            return;
        }
    }  
}  // end periphery bid

if ( ! function_exists('tech_mod'))
{
    function tech_mod($player, $tech_adjustment)
    {
        // Grab a CI object
        $CI =& get_instance();
        
        // Don't adjust tech level for eliminated players
        if ($player->turn_order == 0)
            return;
        
        // Fetch a fresh copy of this player to make sure we always have the latest tech level
        $CI->load->model('playermodel');
        $player = $CI->playermodel->get_by_id($player->player_id);
        
        $playerupdate = new stdClass();
        $playerupdate->player_id = $player->player_id;
        $playerupdate->tech_level = $player->tech_level + $tech_adjustment;
        
        if ($tech_adjustment > 0)
        {
            if ( $playerupdate->tech_level > 25 )
                    $playerupdate->tech_level = 25;

            // Remove the individual 'Mech Tech bonuses  "Bug fix 116"
            if ($player->tech_level <= 23 && $playerupdate->tech_level > 23)
            {
                game_message($player->game_id, $player->faction.'\'s individual \'Mech technology bonus(es) removed.');
                $CI->db->query('UPDATE players SET tech_bonus=0 WHERE player_id='.$player->player_id);
                $CI->db->query('DELETE FROM combatbonus WHERE source_type=2 AND source_id='.$player->player_id);
            }
        }
        else  // Tech is being lowered
        {
            if ( $playerupdate->tech_level < -10 )
                $playerupdate->tech_level = -10;
            if (($player->tech_level >= 12 && $playerupdate->tech_level < 12) || ($player->tech_level >= 7 && $playerupdate->tech_level < 7))
            {
                $CI->db->query('UPDATE players SET tech_bonus=0 WHERE player_id='.$player->player_id);
                $CI->db->query('DELETE FROM combatbonus WHERE source_type=2 AND source_id='.$player->player_id);
            }
        }
        
        $CI->playermodel->update($playerupdate->player_id, $playerupdate);
        game_message($player->game_id, $player->faction.'\'s technology adjusted from '.$player->tech_level.' to '.$playerupdate->tech_level.'.');
    }
}

if ( ! function_exists('eliminate_player'))
{
    function eliminate_player($player)
    {
        // Grab a CI object
        $CI =& get_instance();
        
        if ($CI->debug>2) log_message('error', 'eliminate_player('.$player->player_id.')');
        
        // Discard all cards
        $CI->load->model('cardmodel');
        $cards = $CI->cardmodel->get_by_player($player->player_id);
        foreach($cards as $card)
        {
            discard($card);
        }
        
        // Burn the money
        $playerupdate = new stdClass();
        $playerupdate->money = 0;

        // Set this player to be eliminated!
        $playerupdate->eliminate = TRUE;
        $playerupdate->tech_level = 0;
        $playerupdate->combat_done = 1;
        $CI->load->model('playermodel');
        $CI->playermodel->update($player->player_id, $playerupdate);        
        
        // Check for House Interdict timer(s)
        $players = $CI->playermodel->get_by_game($player->game_id);
        $CI->load->model('territorymodel');
        $terra = $CI->territorymodel->get_by_game_name( $player->game_id, 'Terra' );
        $CI->load->model('gamemodel');
        $game = $CI->gamemodel->get_by_id($player->game_id);
        foreach($players as $check_player)
        {
            // Need to also check for ownership of terra
            if ($check_player->house_interdict > 0)
            {
                if ($check_player->house_interdict == 1)
                {
                    if ($terra->player_id != $check_player->player_id  || !$game->use_terra_interdict)
                    {
                        $check_player_update = new stdClass();
                        $check_player_update->house_interdict = $check_player->house_interdict - 1;
                        $CI->playermodel->update($check_player->player_id, $check_player_update);
                    }
                    else
                    {
                        // This is here to make sure the game message is correct on the fall through condition
                        $check_player_update = new stdClass();
                        $check_player_update->house_interdict = $check_player->house_interdict;
                    }
                }
                else
                {
                    $check_player_update = new stdClass();
                    $check_player_update->house_interdict = $check_player->house_interdict - 1;
                    $CI->playermodel->update($check_player->player_id, $check_player_update);
                }
                                              
                if ($check_player_update->house_interdict == 0)
                    game_message($player->game_id, $check_player->faction.' is no longer under House Interdict.');
                else
                    game_message($player->game_id, $check_player->faction.'\'s House Interdict is reduced from '.$check_player->house_interdict.' turns to '.$check_player_update->house_interdict.'.');
            
            }  // end house interdict > 0
            
        }  // end foreach($players as $check_player)
    }
}

/**
 * Check to see if a house has been captured
 * 
 * Should be called whenever a leader is captured, killed, bribed, or traded
 */
if ( ! function_exists('check_house_capture'))
{
    function check_house_capture($player)
    {
        // Grab a CI object
        $CI =& get_instance();
        
        if ($CI->debug>2) log_message('error', 'Checking for House Capture '.(isset($player->player_id) ? $player->player_id: 'na'));
        
        // Return if player does not exist
        if (!isset($player->player_id))
        {
            log_message('error', 'No such player!');
            return;
        }
        
        // Get all of the player's original leaders who are still controlled by the player
        $leaders = $CI->db->query('select * from leaders where original_house_id='.$player->player_id.' and controlling_house_id='.$player->player_id)->result();
            // Note: Bribed original leaders who are re-captured will not have allegiance to original house... unwilling leaders :)
                
        if ($CI->debug>2) log_message('error', 'Count of leaders is '.count($leaders));
        
        if (count($leaders) == 0)
        {
            // We may have a problem here...
            // Regardless the house is eliminated from play if not already eliminated
            
            // If this is causing the player to be eliminated
            if ($player->turn_order != 0  &&  $player->eliminate == FALSE)
            {
                game_message($player->game_id, $player->faction.' has lost all of their leaders and has been eliminated!');
                eliminate_player($player);                
            }
            
            // Are all leaders controlled by a single house?
            $leaders = $CI->db->query('select * from leaders where original_house_id='.$player->player_id)->result();
            log_message('error', $CI->db->last_query());
            
            $controlled_by = array();
            foreach($leaders as $leader)
            {
                if (!isset($controlled_by[$leader->controlling_house_id]))
                {
                    $controlled_by[$leader->controlling_house_id] = true;
                }
            }
            
            log_message('error', 'Count of controlled by is '.count($controlled_by));
            if (count($controlled_by) == 1)
            {
                if ($CI->debug>2) log_message('error', 'House Captured!');
                
                // House has been captured!
                $CI->load->model('playermodel');
                $capturing_player = $CI->playermodel->get_by_id(key($controlled_by));
                
                // Swap all territories
                $CI->db->query('update territories set player_id='.$capturing_player->player_id.' where player_id='.$player->player_id);
                
                // Swap allegiance of 'Merc leaders (both those captured by the capturing player and those by other players)
                $CI->db->query('update leaders set allegiance_to_house_id='.$capturing_player->player_id.' where allegiance_to_house_id='.$player->player_id.' AND associated_units IS NOT NULL');                
                
                // Swap all combat units
                $CI->db->query('update combatunits set owner_id='.$capturing_player->player_id.' where original_owner_id = '.$player->player_id);
                $CI->db->query('update combatunits set owner_id='.$capturing_player->player_id.' where owner_id = '.$player->player_id);
                
                // Swap all jumpships
                $CI->db->query('update jumpships set owner_id='.$capturing_player->player_id.' where owner_id = '.$player->player_id);
                
                // There is no need to track 'captured' that I can see.
                //$playerupdate = new stdClass();
                //$playerupdate->captured = true;
                //$CI->playermodel->update($player->player_id, $playerupdate);
                
                // Send game message
                game_message($player->game_id, $player->faction.' has been captured by '.$capturing_player->faction.'!');
            }
        }
        else {
            // At least one more leader under house control
            // Nothing to see here...           
        }
    }
    
}  // end check_house_capture

?>
