<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('check_alt_victory'))
{
    function check_alt_victory($game, $player)
    {
        $CI =& get_instance();
        
        // Get all victory conditions for this player
        $CI->load->model('victorymodel');
        $conditions = $CI->victorymodel->get_by_player($player->player_id);
        $retval = new stdClass();
        $retval->result = false;
        foreach($conditions as $cond)
        {
            log_message('error', 'Checking for player id '.$player->player_id);
            if ($cond->type == 'Capital')
            {
                $capitals = $CI->territorymodel->get_by_player_capitals($player->player_id);
                log_message('error', count($capitals));
                if (count($capitals) >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->capital = true;
                        game_message($game->game_id, '***** Capital Win Condition *****');
                    }
                }
                else 
                {
                    reset_duration($cond);
                }
            }
            else if ($cond->type == 'Regional')
            {
                $regionals = $CI->territorymodel->get_by_player_regional($player->player_id);
                if (count($regionals) >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->regional = true;
                        game_message($game->game_id, '***** Regional Capital Win Condition *****');
                    }
                }
                else 
                {
                    reset_duration($cond);
                }
            }
            else if ($cond->type == 'Territory')
            {
                $territory = $CI->territorymodel->get_by_player($player->player_id);
                if (count($territory) >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->territory = true;
                        game_message($game->game_id, '***** Territory Win Condition *****');
                    }
                }
                else 
                {
                    reset_duration($cond);
                }
            }
            else if ($cond->type == 'Military')
            {
                $CI->load->model('combatunitmodel');
                $strength = $CI->combatunitmodel->get_by_player_strength($player->player_id);
                if ($strength >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->military = true;
                        game_message($game->game_id, '***** Military Win Condition *****');
                    }
                }
                else 
                {
                    reset_duration($cond);
                }
            }
            else if ($cond->type == 'Economic')
            {
                if ($player->money >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->economic = true;
                        game_message($game->game_id, '***** Economic Win Condition *****');
                    }
                }
            }
            else if ($cond->type == 'Technology')
            {
                if ($player->tech_level >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->technology = true;
                        game_message($game->game_id, '***** Technology Win Condition *****');
                    }
                }
            }
            else if ($cond->type == 'Industrial')
            {
                $CI->load->model('factorymodel');
                $factories = $CI->factorymodel->get_by_player($player->player_id);
                if (count($factories) >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->industrial = true;
                        game_message($game->game_id, '***** Industrial Win Condition *****');
                    }
                }
            }
            else if ($cond->type == 'Leaders')
            {
                $CI->load->model('leadermodel');
                $leaders = $CI->leadermodel->get_by_player_pow($player->player_id);
                if (count($leaders) >= $cond->threshold)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->industrial = true;
                        game_message($game->game_id, '***** Leadership Win Condition *****');
                    }
                }
            }
            else if ($cond->type == 'Survival')
            {
                if ($player->turn_order != 0)
                {
                    increment_duration($cond);
                    if ($cond->current_duration+1 >= $cond->duration)
                    {
                        $retval->result = true;
                        $retval->survival = true;
                        game_message($game->game_id, '***** Survival Win Condition *****');
                    }
                }
            }
        }  // end foreach condition
        
        // return consolidated return value
        return $retval;
    }
}

if ( ! function_exists('check_default_victory'))
{
    function check_default_victory($game, $player)
    {
        $CI =& get_instance();
        
        // Current player must control $game->capitals_to_win capitals at the start of their turn
        $capitals = $CI->territorymodel->get_by_player_capitals($player->player_id);
        if ( count($capitals) >= $game->capitals_to_win )
        {
            $retval = new stdClass();
            $retval->result = true;
            $retval->capital = true;
            return $retval;
        }
        else
        {
            $retval = new stdClass();
            $retval->result = false;
            return $retval;
        }
    }
}

if ( ! function_exists('increment_duration'))
{
    function increment_duration($condition)
    {
        log_message('error', 'Increment duration '.$condition->current_duration);
        $CI =& get_instance();
        $cond_update = new stdClass();
        $cond_update->current_duration = $condition->current_duration + 1;
        $CI->victorymodel->update($condition->condition_id, $cond_update);
    }
}

if ( ! function_exists('reset_duration'))
{
    function reset_duration($condition)
    {
        $CI =& get_instance();
        $cond_update = new stdClass();
        $cond_update->current_duration = 0;
        $CI->victorymodel->update($condition->condition_id, $cond_update);
    }
}