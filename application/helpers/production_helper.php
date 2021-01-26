<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('get_price'))
{
    // Get the price of any combat unit given a player's tech level
    function get_price($is_merc, $tech_level)
    {   
        if ( $tech_level < -9 )
        {
            if ( $is_merc )
                return 8;
            else
                return 10;
        }
        else if ( $tech_level < 10 )
        {
            if ( $is_merc )
                return 6;
            else
                return 8;
        }
        else if ( $tech_level < 20 )
        {
            if ( $is_merc )
                return 5;
            else
                return 7;
        }
        else
        {
            if ( $is_merc )
                return 5;
            else
                return 6;
        }
    }
}