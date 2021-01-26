<?php

/*
 * Handles ajax chat traffic
 * 
 * 
 */

class Chat extends MY_Controller {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
    }   
    
    /**
     * Send a public chat message
     */
    function chat_public($game_id=0)
    {
        // Make sure an id is provided
        if ( $game_id == 0 )
            return;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
            return;
        
        // Make sure the user is playing in the game
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
            return;
        
        // Extract data from post
        $page['msg'] = $this->input->post('msg');
        if ($page['msg'] == "")
            return;
        // Strip html and other bad stuff
        $page['msg'] = strip_tags($page['msg']);
        
        // Upload Data
        $chat['message'] = $page['msg'];
        $chat['player_id'] = $page['player']->player_id;
        $chat['game_id'] = $game_id;
        $this->load->model('chatmodel');
        $this->chatmodel->create($chat);  
    }
    
    /**
     * Send a private chat message
     * @param type $game_id
     * @return type 
     */
    function chat_private($game_id=0)
    {
        // Make sure an id is provided
        if ( $game_id == 0 )
            return;
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        
        if ( !isset($page['game']->game_id) )
            return;
        
        // Make sure the user is playing in the game
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
            return;
        
        // Extract data from post
        $page['msg'] = $this->input->post('msg');
        if ($page['msg'] == "")
            return;
        // Strip html and other bad stuff
        $page['msg'] = strip_tags($page['msg']);
        
        $sendTo = $this->input->post('sendTo');
        if ($sendTo === false)
            return;
        
        // Must be a valid destination
        $sendTo = (int)$sendTo;
        if ($sendTo != 0)
        {
            $valid = false;
            foreach($page['players'] as $p)
            {
                if ( $p->player_id == $sendTo )
                {
                    $valid = true;
                    break;
                }
            }
            if (!$valid)
                return;
        }
        else
        {
            return;
        }
        
        // Upload Data
        $chat['message'] = $page['msg'];
        $chat['player_id'] = $page['player']->player_id;
        $chat['game_id'] = $game_id;
        $chat['to_player_id'] = $sendTo;
        $this->load->model('chatmodel');
        $this->chatmodel->create($chat);
        
        // Send email if required, needs testing first!
        if ($this->playermodel->email_on_private_message($sendTo))
        {
            email_player($sendTo, 'You have received a private message from '.$page['player']->faction.' in game <a href="http://www.scrapyardarmory.com/successionwars/index.php/game/play/'.$game_id.'">'.$page['game']->title.'</a>: '.$page['msg'], 'Succession Wars PM - '.$page['game']->title);
        }
    }
    
    function load_chat($game_id=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( !isset($page['game']->game_id) )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Get the player if playing in the game
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
        }
        
        $this->load->model('chatmodel');
        $page['chats'] = $this->chatmodel->get_last_ten($game_id);
        
        $this->load->model('gamemsgmodel');
        $page['messages'] = $this->gamemsgmodel->get_last_ten($game_id);
        
        $this->load->view('ajax_load', $page);
    }
    
    function update($game_id=0)
    {
        // Make sure an id is provided
        if ( $game_id == 0 )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure $time is a valid sql timestamp
        //...
        
        // Make sure the user is signed in
        if ( !$this->ion_auth->logged_in() )
        {
            redirect($this->config->item('base_url'), 'refresh');
        }
        
        // Make sure the game exists
        $this->load->model('gamemodel');
        $page['game'] = $this->gamemodel->get_by_id($game_id);
        if ( count($page['game']) != 1 )
            redirect($this->config->item('base_url'), 'refresh');
        
        // Make sure the user is playing in the game
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
            redirect($this->config->item('base_url'), 'refresh');
        
        $page['time'] = $this->input->post('time');
        $this->load->model('chatmodel');
        $page['messages'] = $this->chatmodel->get_new($game_id, $page['time']);
        
        $this->load->view('ajax_update', $page);
    }
    
    /**
     * Fetch a log of chats
     */
    function chat_log($game_id=0, $offset=0)
    {
        $page = $this->page;
        
        // Make sure an id is provided
        if ( $game_id == 0 )
        {
            $page['error'] = 'ERROR! cannot chat without providing game ID.';
            $this->load->view('template', $page);
            return;
        }
        
        // Load the game
        $this->load->model('gamemodel');
        $game = $this->gamemodel->get_by_id($game_id);
        
        // Make sure the game exists        
        if ( !isset($game->game_id) )
        {
            $page['error'] = 'ERROR! Cannot chat in non existant game.';
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
            $page['error'] = 'ERROR! You cannot chat in a gmae you are not participating in.';
            $this->load->view('templatexml', $page);
            return;
        }
        
        // Away we go
        $page['content'] = 'chat_log';
        $page['players'] = $players;
        $page['game'] = $game;
        
        $this->load->model('chatmodel');
        $page['num_chats'] = $this->chatmodel->get_num_chats($game_id);
        $page['chats'] = $this->chatmodel->get_last($game_id, $offset);
        $page['offset'] = $offset;
        
        $this->load->view('templatexml', $page);
        
    }  // end chat_log
    
}