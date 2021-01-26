<?php

Class Buglogmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'log_id';
        $this->table = 'bug_log';
    }
    
}

?>