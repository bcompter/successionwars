<?php

if ( ! function_exists('create_movement_log'))
{
    function create_movement_log($type, $id, $game_id, $prev_location_id, $order)
    {
        $CI =& get_instance();
        $CI->load->model('movementlogmodel');
        
        $newlog 			= new stdClass();
        $newlog->object_type 		= $type;
        $newlog->object_id 		= $id;
        $newlog->game_id 		= $game_id;
        $newlog->prev_location_id	= $prev_location_id;
        $newlog->move_order	 	= $order;
        $CI->movementlogmodel->create($newlog);
        
    }
}  // end create_movement_log