<?php

Class Gamestatmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'stat_id';
        $this->table = 'gamestats';
    }

}

?>