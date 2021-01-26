<?php

Class Forummodel extends MY_Model {
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'forum_id';
        $this->table = 'forum';
    }
    
    /**
     * Get all sections
     */
    function get_sections($user_id) // user_id should only be set for those logged in
    {
        $this->db->select('forum_sections.*');
        $this->db->select('count(distinct forum_topics.topic_id) as num_topics', FALSE);
        $this->db->select('count(distinct forum_posts.post_id) as num_posts', FALSE);
        $this->db->select('max( forum_posts.created_on) as last_created', FALSE);
        $this->db->select('max( forum_posts.modified_on) as last_modified', FALSE);
        if (isset($user_id))    // get last_read for any topic within section for user_id
            $this->db->select('max( forum_unread.last_read) as last_read', FALSE);    
        $this->db->join('forum_topics', 'forum_topics.section_id=forum_sections.section_id');
        $this->db->join('forum_posts', 'forum_posts.topic_id=forum_topics.topic_id');
        if (isset($user_id))    // get last_read for any topic within section for user_id
            $this->db->join('forum_unread', 'forum_topics.topic_id=forum_unread.topic_id and forum_unread.user_id ='.$user_id,'left');
        $this->db->group_by('forum_sections.section_id');
        $this->db->order_by('forum_sections.order','asc');
        return $this->db->get('forum_sections')->result();
        /*
        return $this->db->query('select forum_sections.*, count(distinct forum_topics.topic_id) as num_topics, count(distinct forum_posts.post_id) as num_posts from forum_sections
            left join forum_topics on forum_topics.section_id=forum_sections.section_id
            left join forum_posts on forum_posts.topic_id=forum_topics.topic_id
            group by forum_sections.section_id
            order by forum_sections.order')->result();         */
    }
    
    /**
     * Get a section
     */
    function get_section($section_id)
    {
        return $this->db->query('select * from forum_sections where section_id='.$section_id)->row();
    }

    /**
     * Get last read data
     */
    function get_last_read($topic_id, $user_id)
    {
        return $this->db->query('select * from forum_unread where user_id='.$user_id.' and topic_id='.$topic_id)->row();
    }
    
    /**
     * Insert / Update unread date
     */
    function update_unread($data, $id)
    {
        if (isset($id))
        {
            $this->db->where('forum_unread.id', $id);
            $this->db->update('forum_unread', $data);
        }
        else
            $this->db->insert('forum_unread', $data);
    }
    
    /**
     * get data of last post in a topic
     */
    function get_last_post_in_topic($topic_id)
    {
        $this->db->select('forum_posts.*');
        $this->db->where('forum_posts.topic_id', $topic_id);
        $this->db->order_by('forum_posts.created_on','desc');
        $this->db->limit(1);
        return $this->db->get('forum_posts')->row();
    }
    
    /**
     * Get all topics in a section
     */
    function get_topics($section_id, $offset, $user_id)
    {
        $this->db->select('*, topic_user.username as topic_by_username, forum_posts.created_on');
        $this->db->select('COUNT( DISTINCT forum_posts.post_id ) AS num_replies', FALSE);
        $this->db->select('max(greatest(forum_posts.created_on, forum_posts.modified_on)) as last_post_created_on', FALSE);
        $this->db->select('forum_posts.topic_id');
        $this->db->from('forum_posts');
        $this->db->join('forum_topics', 'forum_topics.topic_id = forum_posts.topic_id');
        $this->db->join('users AS topic_user', 'topic_user.id = forum_topics.creator_id');
        $this->db->join('users AS post_user', 'post_user.id = forum_posts.author_id');
        if (isset($user_id))    // get last_read for any topic within section for user_id
            $this->db->join('forum_unread', 'forum_unread.topic_id = forum_topics.topic_id AND forum_unread.user_id ='. $user_id,'left outer');
        $this->db->where('forum_topics.section_id =', $section_id);
        $this->db->order_by('last_post_created_on','desc');
        $this->db->select('post_user.username as last_post_by_username');   // ERROR!!!  NOT ACTUALLY THE LAST USER WHO POSTED TO THE TOPIC
        $this->db->group_by('forum_topics.topic_id');        
        $this->db->limit(30, $offset);
        return $this->db->get('forum_sections')->result();        
       
        
        /*return$this->db->query('select *,COUNT( DISTINCT last_post.post_id ) AS num_replies, topic_user.username, post_user.username as post_username, last_post.created_on as post_created_on
            from (select * from forum_posts order by created_on desc) as last_post
            JOIN forum_topics on forum_topics.topic_id=last_post.topic_id
            JOIN users AS topic_user ON topic_user.id = forum_topics.creator_id
            JOIN users AS post_user ON post_user.id = last_post.author_id
            where forum_topics.section_id='.$section_id.'
            GROUP BY forum_topics.topic_id 
            ORDER BY post_created_on desc
            limit '.$offset.', 30')->result();*/
    }
    
    /**
     * Get a topic
     */
    function get_topic($topic_id)
    {
        return $this->db->query('select * from forum_topics where topic_id='.$topic_id)->row();
    }
    
    /**
     * Get a topic
     */
    function get_post($post_id)
    {
        return $this->db->query('select * from forum_posts where post_id='.$post_id)->row();
    }
    
    /**
     * Delete a post
     */
    function delete_post($post_id)
    {
        $this->db->query('delete from forum_posts where post_id='.$post_id);
    }
    
    /**
     * Get posts in a topic (limited by $posts_per_page)
     */
    function get_posts($topic_id, $offset, $posts_per_page)
    {
        $this->db->select('forum_posts.*, users.username');
        $this->db->join('users', 'users.id=forum_posts.author_id');
        $this->db->where('forum_posts.topic_id =', $topic_id);
        $this->db->order_by('forum_posts.post_id','asc');
        $this->db->limit($posts_per_page, $offset);
        return $this->db->get('forum_posts')->result();
        /*
        return $this->db->query('select forum_posts.*, users.username from forum_posts 
            join users on users.id=forum_posts.author_id
            where forum_posts.topic_id='.$topic_id.'
            order by forum_posts.post_id asc
            limit '.$offset.', '.$posts_per_page)->result();*/
    }
    
    /**
     * Get the total number of topics in a section
     */
    function num_topics($section_id)
    {
        $topics = $this->db->query('select count(topic_id) as num_topics from forum_topics where section_id='.$section_id)->row();
        return $topics->num_topics;
    }
    
    /**
     * Get the total number of posts in a topic
     */
    function num_posts($topic_id)
    {
        $posts = $this->db->query('select count(post_id) as num_posts from forum_posts where topic_id='.$topic_id)->row();
        return $posts->num_posts;
    }
    
    /**
     * Create a new topic
     */
    function create_topic($obj)
    {
        $this->db->insert('forum_topics', $obj);
    }
    
    /**
     * Create a new post
     */
    function create_post($obj)
    {
        $this->db->insert('forum_posts', $obj);
    }
    
    /**
     * Update a topic
     */
    function update_topic($obj_id, $obj)
    {
        $this->db->where('topic_id', $obj_id);
        $this->db->update('forum_topics', $obj);
    }
    
    /**
     * Update a post
     */
    function update_post($obj_id, $obj)
    {
        $this->db->where('post_id', $obj_id);
        $this->db->update('forum_posts', $obj);
    }
    
}  // end forummodel

?>