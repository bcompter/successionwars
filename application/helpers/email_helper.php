<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('email_game'))
{
    function email_game($game_id, $message, $exclude_player_id=0)
    {
        $CI =& get_instance();
        
        // Get all players in the game
        $players = $CI->db->query('select player_id, turn_order from players where game_id='.$game_id)->result();
        foreach($players as $p)
        {
            if ($p->turn_order != 0 && $p->player_id != $exclude_player_id)
                email_player($p->player_id, $message);
        }
        
    }
}

if ( ! function_exists('email_player'))
{
    function email_player($player_id, $message)
    {
        $CI =& get_instance();
        
        //if ($CI->debug>2) log_message('error', 'helpers/email_helper.php email_player($player_id='.$player_id.', $message='.$message.')');
        $CI->load->library('email');
        
        // Grab user
        $user = $CI->db->query('select id, username, email, send_me_email from users
            join players on players.user_id=users.id where player_id='.$player_id)->row();
        if (isset($user->id))
        {
            if ($user->send_me_email)
            {
                if ($CI->debug>2) log_message('error', 'email going to be sent to '.$user->email);
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $config['protocol'] = 'sendmail';
                $CI->email->initialize($config);
                $CI->email->from('brian@scrapyardarmory.com', 'Succession Wars Game');
                $CI->email->to($user->email);
                $CI->email->subject('Succession Wars Game Notification');
                $CI->email->message($message);
                
                $CI->email->send();
            }
        }  // end if (isset($user->id))
        
    }
}

if ( ! function_exists('email_user'))
{
    function email_user($user, $message)
    {
        $CI =& get_instance();
        $CI->load->library('email');

        if (isset($user->id))
        {
            if ($user->send_me_email)
            {
                if ($CI->debug>2) log_message('error', 'email going to be sent to '.$user->email);
                $config['charset'] = 'utf-8';
                $config['wordwrap'] = TRUE;
                $config['mailtype'] = 'html';
                $config['protocol'] = 'sendmail';
                $CI->email->initialize($config);
                $CI->email->from('brian@scrapyardarmory.com', 'Succession Wars Game');
                $CI->email->to($user->email);
                $CI->email->subject('Succession Wars Game Notification');
                $CI->email->message($message);
                
                $CI->email->send();
            }
        }  // end if (isset($user->id))
        
    }
}
