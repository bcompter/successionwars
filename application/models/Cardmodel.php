<?php

/*
 * This model deals with cards.  The cards database table will track all cards
 * in the deck, discard pile, or in players hands.
 * 
 * The owner_id column tells where the card is.  0 is the discard pile, null
 * is the deck and any other integer is a player's hand
 */

Class Cardmodel extends MY_Model {
    
    
    /**
     * Default constructor
     */
    function __construct()
    {
        parent::__construct();
        $this->table_id = 'card_id';
        $this->table = 'cards';
    }
    
    /**
     * Get a single card.
     * 
     * Overrides the default implementation in MY_Model
     * 
     * @param type $card_id 
     */
    function get_by_id( $card_id )
    {
        $this->db->join('card_types', 'card_types.type_id=cards.type_id');
        $this->db->where('card_id', $card_id);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Get a card type.
     * Used to edit a deck before a game starts
     */
    function get_by_type( $type_id )
    {
        $this->db->where('type_id', $type_id);
        $this->db->limit(1);
        return $this->db->get('card_types')->row();
    }
    
    /**
     * Get all of the available card types to add to a game
     */
    function get_all_types()
    {
        return $this->db->get('card_types')->result();
    }

    /**
     * Get all the cards in a player's hand
     * 
     * @param type $player_id 
     */
    function get_by_player( $player_id )
    {
       $this->db->join('card_types', 'card_types.type_id=cards.type_id');
       $this->db->where('owner_id', $player_id);
       return $this->db->get($this->table)->result();
    }
    
    /**
     * Get all the cards in the game, used during game builds
     * 
     * @param type $player_id 
     */
    function get_by_game( $game_id )
    {
       $this->db->join('card_types', 'card_types.type_id=cards.type_id');
       $this->db->where('game_id', $game_id); 
       $this->db->order_by('cards.type_id','asc');
       return $this->db->get($this->table)->result();
    }
    
    /**
     * If the game is on hold due to a card being reolved
     * @param type $game_id
     * @return type 
     */
    function get_hold($game_id)
    {
        $this->db->join('card_types', 'card_types.type_id=cards.type_id');
        $this->db->where('game_id', $game_id);
        $this->db->where('being_played', true);
        $this->db->limit(1);
        return $this->db->get($this->table)->row();
    }
    
    /**
     * Draw a random card from the deck
     * 
     * @param type $game_id The game
     */
    function draw( $game_id )
    {
        // Check to see if a shuffle is required
        
        $this->db->where('game_id', $game_id);
        $this->db->where('owner_id', null);
        
        $card;
        if ( $this->db->count_all_results($this->table) == 0)
        {
            $this->db->where('game_id', $game_id);
            $this->db->where('owner_id', 0);
            $this->db->order_by('card_id','random');
            $this->db->limit(1);
            $card = $this->db->get($this->table)->row();
            $this->shuffle( $game_id );
        }
        else
        {
            $this->db->where('game_id', $game_id);
            $this->db->where('owner_id', null);
            $this->db->order_by('card_id','random');
            $this->db->limit(1);
            $card = $this->db->get($this->table)->row();
        }

        return $card;
    }
    
    /**
     * Shuffle the discard pile back into the deck
     * Change owner_id from 0 to null
     * 
     * @param type $game_id The game
     */
    private function shuffle( $game_id )
    {
        // Gat all the cards in the game that are in the discard
        $this->db->where('owner_id', 0);
        $this->db->where('game_id', $game_id);
        $cards = $this->db->get($this->table)->result();
        
        // Update all...
        foreach($cards as $card)
        {
            $card->owner_id = null;
            $card->traded=0;
            $this->update($card->card_id, $card);
        }
        game_message($game_id, 'The deck has been shuffled.');
    }
    
}

?>
