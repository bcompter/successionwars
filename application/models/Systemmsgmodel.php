<?php

Class Systemmsgmodel extends MY_Model {
        
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'msg_id';
        $this->table = 'system_msg';
    }
 
    
}
