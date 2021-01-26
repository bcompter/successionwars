<?php

/**
 * Handle logic related to victory conditions
 */
class Victory_conditions extends MY_Controller {
    
    /**
     * Add a new victory condition to a game
     */
    function add($game_id=0)
    {
        $page = $this->page;
        
        // Must be the game owner, unless you are an admin
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        if ($game->creator_id != $page['user']->id && !$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        
        // Away we go...
        $page['game'] = $game;
        $this->load->model('playermodel');
        $this->load->model('victorymodel');
        $players = $this->playermodel->get_by_game($game_id);
        $page['players'] = $players;
        $factions[] = 'Everyone';
        foreach($players as $p)
        {
            $factions[$p->faction] = $p;
        }
        
        $faction = $this->input->post('who');
        if ($faction == '')
        {
            $page['content'] = 'victory_condition_form';
            $this->load->view('template', $page);
            return;
        }
        if (!isset($factions[$faction]) && $faction != 'Everyone')
        {
            $page['error'] = 'Error 1.';
            $page['content'] = 'victory_condition_form';
            $this->load->view('template', $page);
            return;
        }
        
        $conditions[] = 'capital';
        
        $conditions[] = 'regional';
        $conditions[] = 'territory';
        $conditions[] = 'military';
        $conditions[] = 'economic';
        $conditions[] = 'technology';
        $conditions[] = 'industrial';
        $conditions[] = 'leader';
        $conditions[] = 'survive';        
        
        $validation = false;
        foreach($conditions as $cond)
        {
            $use = $this->input->post($cond);
            if ($use == 'on')
            {
                $validation = true;
                $threshold = (int)$this->input->post($cond.'_threshold');
                $duration = (int)$this->input->post($cond.'_duration');

                if (!is_int($duration) || !is_int($threshold))
                {
                    $page['error'] = 'Error 2.';
                    $page['content'] = 'victory_condition_form';
                    $this->load->view('template', $page);
                    return;
                }
                if ($duration < 0 || $threshold < 0)
                {
                    $page['error'] = 'Error 3.';
                    $page['content'] = 'victory_condition_form';
                    $this->load->view('template', $page);
                    return;
                }
                $new = new stdClass();
                $new->game_id = $game_id;
                $new->type = ucwords($cond);
                $new->threshold = $threshold;
                $new->duration = $duration;
                if ($faction == 'Everyone')
                {
                    foreach($players as $p)
                    {
                        $new->player_id = $p->player_id;
                        $this->victorymodel->create($new);
                    }
                }
                else 
                {
                    $new->player_id = $factions[$faction]->player_id;
                    $this->victorymodel->create($new);
                }
                
                game_message($game_id, 'Game owner added a new alternate victory condition for '.$faction.'; '.$cond.' Threshold '.$threshold.', Duration, '.$duration);
            }
        }

        $this->session->set_flashdata('notice', 'Victory Condition(s) Created.');
        redirect('game/view/'.$game->game_id, 'refresh');
            
    }  // end add
    
    /**
     * Delete a victory condition
     */
    function delete($condition_id=0)
    {
        $page = $this->page;
        
        $this->load->model('victorymodel');
        $condition = $this->victorymodel->get_by_id($condition_id);
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($condition->player_id);
        
        // Condition must exist
        if (!isset($condition->condition_id))
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        
        // Must be the game owner, unless you are an admin
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($condition->game_id);
        if (!isset($game->game_id))
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        if ($game->creator_id != $page['user']->id && !$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        
        // Away we go...
        $this->victorymodel->delete($condition_id);
        game_message($game->game_id, 'Game owner has deleted a victory condition for '.$player->faction.', '.$condition->type.'.');
        $this->session->set_flashdata('notice', 'Victory Condition Deleted.');
        redirect('game/view/'.$game->game_id, 'refresh');
        
    }  // end delete
    
    /**
     * Edit a victory condition
     */
    function edit($condition_id=0)
    {
        $page = $this->page;
        
        $this->load->model('victorymodel');
        $condition = $this->victorymodel->get_by_id($condition_id);
        $this->load->model('playermodel');
        $player = $this->playermodel->get_by_id($condition->player_id);
        
        // Condition must exist
        if (!isset($condition->condition_id))
        {
            $page['error'] = 'Error 1.';
            $this->load->view('template', $page);
            return;
        }
        
        // Must be the game owner, unless you are an admin
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($condition->game_id);
        if (!isset($game->game_id))
        {
            $page['error'] = 'Error 2.';
            $this->load->view('template', $page);
            return;
        }
        if ($game->creator_id != $page['user']->id && !$this->ion_auth->is_admin())
        {
            $page['error'] = 'Error 3.';
            $this->load->view('template', $page);
            return;
        }
        
        // Away we go...
        $this->load->library('form_validation');
        $this->form_validation->set_rules('threshold', 'Threshold', 'required|integer');
        $this->form_validation->set_rules('duration', 'Duration', 'required|integer');

        if ($this->form_validation->run() == true)
        { 
            // Edit the condition in question
            $cond_update = new stdClass();
            $cond_update->threshold = $this->input->post('threshold');
            $cond_update->duration = $this->input->post('duration');
            $this->victorymodel->update($condition_id, $cond_update);
            
            $this->session->set_flashdata('notice', 'Victory Condition Updated.');
            redirect('game/view/'.$game->game_id, 'refresh');
        }
        else
        {
            // Show the form
            $page['game'] = $game;
            $page['condition'] = $condition;
            $page['player'] = $player;
            $page['content'] = 'victory_condition_form_single';
            $this->load->view('template', $page);
        }
        
    }  // end edit
    
    /**
     * Delete all victory conditions in a game
     */
    function delete_all($game_id=0)
    {
        $page = $this->page;
        
        // Must be the game owner, unless you are an admin
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        if (!isset($game->game_id))
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        if ($game->creator_id != $page['user']->id && !$this->ion_auth->is_admin())
        {
            $this->page['error'] = 'Error.';
            $this->load->model('template', $page);
            return;
        }
        
        // Away we go...
        $this->db->query('DELETE FROM victory_conditions WHERE game_id='.$game_id);
        game_message($game_id, 'Game owner has deleted ALL alternate victory conditions from the game.');
        $this->session->set_flashdata('notice', 'Victory Conditions Deleted.');
        redirect('game/view/'.$game->game_id, 'refresh');
        
    }  // end delete all
    
}
    