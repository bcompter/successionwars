<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Phases is all of the phases in the game.
 * 
 * The draw, place units, and tax phases are skipped because they are done
 * automatically for the users.
 * 
 * Only phases that require user interaction are required.
 */
$config['phases'] = array('Production','Movement','Combat');