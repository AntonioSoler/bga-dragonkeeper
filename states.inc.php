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
 * states.inc.php
 *
 * dragonkeeper game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),
    
    // Note: ID=2 => your first state

    2 => array(
    		"name" => "playerPick",
    		"description" => clienttranslate('${actplayer} must pick a card to take'),
    		"descriptionmyturn" => clienttranslate('${you} must pick a card to take'),
            "type" => "activeplayer",
            "args" => "argPossiblePicks",
			"action" => "stCheckpick",
    		"possibleactions" => array( "pickCard" ),
    		"transitions" => array( "pickCard" => 3, "nextLevel" =>7 , "activatePower" => 4  , "zombiePass" => 6 )
    ),

    3 => array(
        "name" => "playerDonate",
        "description" => clienttranslate('${actplayer} must pick a card to donate'),
        "descriptionmyturn" => clienttranslate('${you} must pick a card to donate'),
        "type" => "activeplayer",
        "args" => "argPossibleDonations",
		"action" => "stCheckdonate",
        "possibleactions" => array( "donateCard" ),
        "transitions" => array( "activatePower" => 4, "nextLevel" =>7 , "nextPlayer" => 6  , "zombiePass" => 6 )
    ),
    
    4 => array(
        "name" => "activatePower",
        "description" => clienttranslate('${actplayer} may activate now the special power of the card'),
        "descriptionmyturn" => clienttranslate('${you} may activate now the special power of the card'),
        "type" => "activeplayer",
        "args" => "argPowertype",
		"possibleactions" => array( "playPower" , "pass" ),
        "transitions" => array( "playPower" => 5 , "pass"=> 6 , "playerDonate" => 3 , "zombiePass" => 6 )
    ),

    5 => array(
        "name" => "playPower",
        "description" => clienttranslate('${actplayer} have to select a target card to use the power'),
        "descriptionmyturn" => clienttranslate('${you} have to select a target card to use the power'),
        "type" => "activeplayer",
		"args" => "argPossibleTargets",
		"action" => "stCheckpower",
        "possibleactions" => array( "pickcardPower" ),
        "transitions" => array( "pickCard" => 6 , "nextLevel" =>7 , "playerDonate" => 3, "zombiePass" => 6 )
    ),

    6 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGameScoring" => 90, "nextPlayer" => 2 )
    ),

	7 => array(
        "name" => "nextLevel",
		"description" => clienttranslate('There are no more possible moves in this level'),
        "description" => '',
        "type" => "game",
        "action" => "stNextLevel",
        "updateGameProgression" => true,   
        "transitions" => array( "endGameScoring" => 90, "nextPlayer" => 2 )
    ),
	
    90 => array(
        "name" => "endGameScoring",
        "description" => clienttranslate('Scoring'),
        "type" => "game",
        "action" => "stendGameScoring",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99 )
    ),

/*
    Examples:
    
    2 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 99, "nextPlayer" => 10 )
    ),
    
    10 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must play a card or pass'),
        "descriptionmyturn" => clienttranslate('${you} must play a card or pass'),
        "type" => "activeplayer",
        "possibleactions" => array( "playCard", "pass" ),
        "transitions" => array( "playCard" => 2, "pass" => 2 )
    ), 

*/    
   
    // Final state.
    // Please do not modify.
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



