<?php 
defined( 'ABSPATH' ) || exit;

Class Recipe_Admin {

	public function __construct() {
		$this->register_recipe_taxonomy();
	}

	public function register_recipe_taxonomy() {
        register_taxonomy('recipe_cat', array('recipe'), array(
            'hierarchical' => true, 
            'label' => 'Category', 
            'singular_label' => 'Category', 
            'rewrite' => array('slug' => 'recipe_cat', 'with_front'=> true),
            'show_in_rest' => true
        ));
        register_taxonomy('allergen', array('recipe'), array(
            'hierarchical' => true, 
            'label' => 'Allergens', 
            'singular_label' => 'Allergen', 
            'rewrite' => array('slug' => 'allergens', 'with_front'=> true),
            'show_in_rest' => true
        ));
        register_taxonomy('meat', array('recipe'), array(
            'hierarchical' => true, 
            'label' => 'Meat Type', 
            'singular_label' => 'Meat Type', 
            'rewrite' => array('slug' => 'meat', 'with_front'=> true),
            'show_in_rest' => true
        ));
        register_taxonomy('difficulty', array('recipe'), array(
            'hierarchical' => true, 
            'label' => 'Difficulty', 
            'singular_label' => 'Difficulty', 
            'rewrite' => array('slug' => 'difficulty', 'with_front'=> true),
            'show_in_rest' => true
        ));
	}
}

$recipe_obj = new Recipe_Admin();
