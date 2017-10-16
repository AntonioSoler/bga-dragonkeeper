<?php
/**
 *------
 * BGA framework: (c) Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * dragonkeeper implementation : (c) Antonio Soler morgalad.es@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * dragonkeeper game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
    1 => array( "card_name" => ...,
                ...
              )
);

*/


$this->cardpowers = array(
    1   => clienttranslate("Stairs"       ),
    2   => clienttranslate("Secret Path" ),
	3   => clienttranslate("Freedom"      ),
    4   => clienttranslate("Exchange"     ),
    5   => clienttranslate("Remote Trap"  ),
	"score_window_title" => clienttranslate('FINAL SCORE'),
	"win_condition" => clienttranslate('The player with the most points wins')
);

$this->cardcolors = array(
    0   => clienttranslate("red"    ),
    1   => clienttranslate("blue"   ),
	2   => clienttranslate("yellow" ),
    3   => clienttranslate("green"  ),
    4   => clienttranslate("purple" )
	
);

$this->card_types = array(
	1  => array( 'type_id' =>  1  ,'amount' => 2 ,  'color' => 0,  'value' => 1,  'power' => 1  ),
	2  => array( 'type_id' =>  2  ,'amount' => 3 ,  'color' => 0,  'value' => 2,  'power' => 0  ),
	3  => array( 'type_id' =>  3  ,'amount' => 4 ,  'color' => 0,  'value' => 3,  'power' => 0  ),
	4  => array( 'type_id' =>  4  ,'amount' => 3 ,  'color' => 0,  'value' => 4,  'power' => 0  ),
	5  => array( 'type_id' =>  5  ,'amount' => 2 ,  'color' => 0,  'value' => 5,  'power' => 0  ),
	6  => array( 'type_id' =>  6  ,'amount' => 1 ,  'color' => 0,  'value' => 6,  'power' => 0  ),
	7  => array( 'type_id' =>  7  ,'amount' => 4 ,  'color' => 1,  'value' => 1,  'power' => 0  ),
	8  => array( 'type_id' =>  8  ,'amount' => 1 ,  'color' => 1,  'value' => 1,  'power' => 2  ),
	9  => array( 'type_id' =>  9  ,'amount' => 3 ,  'color' => 1,  'value' => 2,  'power' => 0  ),
	10 => array( 'type_id' =>  10 ,'amount' => 1 ,  'color' => 1,  'value' => 2,  'power' => 3  ),
	11 => array( 'type_id' =>  11 ,'amount' => 2 ,  'color' => 1,  'value' => 3,  'power' => 0  ),
	12 => array( 'type_id' =>  12 ,'amount' => 1 ,  'color' => 1,  'value' => 3,  'power' => 4  ),
	13 => array( 'type_id' =>  13 ,'amount' => 1 ,  'color' => 1,  'value' => 4,  'power' => 0  ),
	14 => array( 'type_id' =>  14 ,'amount' => 1 ,  'color' => 1,  'value' => 4,  'power' => 5  ),
	15 => array( 'type_id' =>  15 ,'amount' => 1 ,  'color' => 1,  'value' => 5,  'power' => 0  ),
    16 => array( 'type_id' =>  16 ,'amount' => 4 ,  'color' => 2,  'value' => 1,  'power' => 0  ),
	17 => array( 'type_id' =>  17 ,'amount' => 1 ,  'color' => 2,  'value' => 1,  'power' => 2  ),
	18 => array( 'type_id' =>  18 ,'amount' => 3 ,  'color' => 2,  'value' => 2,  'power' => 0  ),
	19 => array( 'type_id' =>  19 ,'amount' => 1 ,  'color' => 2,  'value' => 2,  'power' => 3  ),
	20 => array( 'type_id' =>  20 ,'amount' => 2 ,  'color' => 2,  'value' => 3,  'power' => 0  ),
	21 => array( 'type_id' =>  21 ,'amount' => 1 ,  'color' => 2,  'value' => 3,  'power' => 4  ),
	22 => array( 'type_id' =>  22 ,'amount' => 1 ,  'color' => 2,  'value' => 4,  'power' => 0  ),
	23 => array( 'type_id' =>  23 ,'amount' => 1 ,  'color' => 2,  'value' => 4,  'power' => 5  ),
	24 => array( 'type_id' =>  24 ,'amount' => 1 ,  'color' => 2,  'value' => 5,  'power' => 0  ),
	25 => array( 'type_id' =>  25 ,'amount' => 4 ,  'color' => 3,  'value' => 1,  'power' => 0  ),
	26 => array( 'type_id' =>  26 ,'amount' => 1 ,  'color' => 3,  'value' => 1,  'power' => 2  ),
	27 => array( 'type_id' =>  27 ,'amount' => 3 ,  'color' => 3,  'value' => 2,  'power' => 0  ),
	28 => array( 'type_id' =>  28 ,'amount' => 1 ,  'color' => 3,  'value' => 2,  'power' => 3  ),
	29 => array( 'type_id' =>  29 ,'amount' => 2 ,  'color' => 3,  'value' => 3,  'power' => 0  ),
	30 => array( 'type_id' =>  30 ,'amount' => 1 ,  'color' => 3,  'value' => 3,  'power' => 4  ),
	31 => array( 'type_id' =>  31 ,'amount' => 1 ,  'color' => 3,  'value' => 4,  'power' => 0  ),
	32 => array( 'type_id' =>  32 ,'amount' => 1 ,  'color' => 3,  'value' => 4,  'power' => 5  ),
	33 => array( 'type_id' =>  33 ,'amount' => 1 ,  'color' => 3,  'value' => 5,  'power' => 0  ),
    34 => array( 'type_id' =>  34 ,'amount' => 4 ,  'color' => 4,  'value' => 1,  'power' => 0  ),
	35 => array( 'type_id' =>  35 ,'amount' => 1 ,  'color' => 4,  'value' => 1,  'power' => 2  ),
	36 => array( 'type_id' =>  36 ,'amount' => 3 ,  'color' => 4,  'value' => 2,  'power' => 0  ),
	37 => array( 'type_id' =>  37 ,'amount' => 1 ,  'color' => 4,  'value' => 2,  'power' => 3  ),
	38 => array( 'type_id' =>  38 ,'amount' => 2 ,  'color' => 4,  'value' => 3,  'power' => 0  ),
	39 => array( 'type_id' =>  39 ,'amount' => 1 ,  'color' => 4,  'value' => 3,  'power' => 4  ),
	40 => array( 'type_id' =>  40 ,'amount' => 1 ,  'color' => 4,  'value' => 4,  'power' => 0  ),
	41 => array( 'type_id' =>  41 ,'amount' => 1 ,  'color' => 4,  'value' => 4,  'power' => 5  ),
	42 => array( 'type_id' =>  42 ,'amount' => 1 ,  'color' => 4,  'value' => 5,  'power' => 0  )
);                                                                                                                                        
