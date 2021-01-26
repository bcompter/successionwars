<?php

Class Blogmodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'post_id';
        $this->table = 'blog_posts';
    }
    
    /**
     * Get a blog post by its id
     */
    function get_by_id($id)
    {
        $this->db->select('blog_posts.*, users.username as author_name');
        $this->db->join('users', 'users.id=blog_posts.author_id');
        $this->db->where('post_id', $id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Get a blog post by its id as a result
     */
    function get_by_id_result($id)
    {
        $this->db->select('blog_posts.*, users.username as author_name');
        $this->db->join('users', 'users.id=blog_posts.author_id');
        $this->db->where('post_id', $id);
        $this->db->limit(1);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get the last 10 posts for viewing
     */
    function get_last()
    {
        $this->db->select('blog_posts.*, users.username as author_name');
        $this->db->join('users', 'users.id=blog_posts.author_id');
        $this->db->order_by('published_on', 'desc');
        $this->db->where('status', 'Published');
        $this->db->limit(10);
        return $this->db->get($this->table)->result();
    }
    
    /**
     * Get a single comment by it's id
     */
    function get_comment_by_id($id)
    {
        $this->db->where('comment_id', $id);
        $this->db->limit(1);
        return $this->db->get('blog_comments')->row();
    }
    
    function get_drafts()
    {
        $this->db->join('users', 'users.id=blog_posts.author_id');
        $this->db->where('status', 'Draft');
        return $this->db->get($this->table)->result();
    }
    
    function get_all()
    {
        $this->db->join('users', 'users.id=blog_posts.author_id');
        return $this->db->get($this->table)->result();
    }
}

?>