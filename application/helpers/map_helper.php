<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if ( ! function_exists('jumpExists'))
{
    function jumpExists($RID1, $RID2)
    {
        $CI =& get_instance();
log_message('error', 'function jumpExists('.$RID1.', '.$RID2.')');
        $CI->load->model('pathmodel');
        $jumpexistsresult = $CI->pathmodel->map_jumpexists($RID1, $RID2);
        if ($jumpexistsresult)
            return TRUE;
        else
            return FALSE;
    }
}

if ( ! function_exists('createjump'))
{
    function createjump($RID1, $RID2)
    {
        $CI =& get_instance();
        $CI->load->model('pathmodel');
        $path = new stdClass();
        $path->origin_id = $RID1;
        $path->destination_id = $RID2;
        $CI->pathmodel->create($path);
    }
}