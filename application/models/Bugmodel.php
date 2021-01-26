<?php

Class Bugmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'bug_id';
        $this->table = 'bugs';
    }
    
    function get_by_id($bug_id)
    {
        $this->db->select('bugs.*, count(*) as number_of_votes, sum(bug_karma.value) as karma');
        $this->db->join('bug_karma', 'bug_karma.bug_id=bugs.bug_id', 'left');
        $this->db->where('bugs.bug_id', $bug_id);
        $this->db->group_by('bugs.bug_id');
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    function get_by_user($user_id)
    {
        $this->db->select('bugs.*, sum(bug_karma.value) as karma');
        $this->db->join('bug_karma', 'bug_karma.bug_id=bugs.bug_id', 'left');
        $this->db->where('bugs.user_id', $user_id);
        $this->db->group_by('bugs.bug_id');
        return $this->db->get($this->table)->result();
    }
	
    function get_all()
    {
        $this->db->select('bugs.*, sum(bug_karma.value) as karma');
        $this->db->join('bug_karma', 'bug_karma.bug_id=bugs.bug_id', 'left');
        $this->db->group_by('bugs.bug_id');
        $this->db->order_by('karma', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_all_bugs()
    {
        $this->db->select('bugs.*, sum(bug_karma.value) as karma');
        $this->db->join('bug_karma', 'bug_karma.bug_id=bugs.bug_id', 'left');
        $this->db->where('is_bug', 1);
        $this->db->group_by('bugs.bug_id');
        $this->db->order_by('karma', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_all_features()
    {
        $this->db->select('bugs.*, sum(bug_karma.value) as karma');
        $this->db->join('bug_karma', 'bug_karma.bug_id=bugs.bug_id', 'left');
        $this->db->where('is_bug', 0);
        $this->db->group_by('bugs.bug_id');
        $this->db->order_by('karma', 'desc');
        return $this->db->get($this->table)->result();
    }
    
    function get_comments($bug_id)
    {
        $this->db->select("bug_comments.*, users.username, DATE_FORMAT(bug_comments.created_on, 'YYYY-MM-DD') ", false);
        $this->db->where('bug_id', $bug_id);
        $this->db->join('users', 'users.id=bug_comments.user_id');
        $this->db->order_by('bug_comments.bug_comment_id', 'asc');
        return $this->db->get('bug_comments')->result();
    }
    
    function get_karma($bug_id, $user_id)
    {
        $this->db->where('bug_id', $bug_id);
        $this->db->where('user_id', $user_id);
        $this->db->limit(1);
        return $this->db->get('bug_karma')->row();
    }
    
    /**
     * Get all trackers NOT voted on by a user
     */
    function get_not_voted($user_id)
    {
        return $this->db->query('select * from bugs where status!="Completed" and
            bug_id not in (select bugs.bug_id from bugs
            join bug_karma on bug_karma.bug_id=bugs.bug_id
            where bug_karma.user_id='.$user_id.')')->result();
    }
}

?>