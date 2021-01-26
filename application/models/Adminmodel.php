<?php

/*
 * This model grabs data from the admin table which tracks global settings and such.
 * 
 */

Class Adminmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'admin_id';
        $this->table = 'admin';
    }
}

?>