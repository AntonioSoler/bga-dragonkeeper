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
 * dragonkeeper.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in dragonkeeper_dragonkeeper.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
  require_once( APP_BASE_PATH."view/common/game.view.php" );
  
  class view_dragonkeeper_dragonkeeper extends game_view
  {
    function getGameName() {
        return "dragonkeeper";
    }    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );
		
		$this->page->begin_block( "dragonkeeper_dragonkeeper", "playerboard" );
		
		$playercount=0;
		
		global $g_user;
        
		$current_player_id = $g_user->get_id();
		
		

        /*********** Place your code below:  ************/

        foreach( $players as $player )	
		{
            if  ( $current_player_id  == $player['player_id'] )
			{
				$playercount=$playercount + 1;
				$this->page->insert_block( "playerboard", array( "PLAYER_ID" => $player['player_id'],
			                                            "PLAYER_NAME" => $player['player_name'],
														"PLAYER_COLOR" => $player['player_color'],
														"PLAYER_COUNT" => $playercount ,
														"ACTIVEPLAYER" => "activeplayer"
                                                      ));
			}
		}
		foreach( $players as $player )	
		{
            if  ( $current_player_id  != $player['player_id'] )
			{
				$playercount=$playercount + 1;
				$this->page->insert_block( "playerboard", array( "PLAYER_ID" => $player['player_id'],
			                                            "PLAYER_NAME" => $player['player_name'],
														"PLAYER_COLOR" => $player['player_color'],
														"PLAYER_COUNT" => $playercount,
														"ACTIVEPLAYER" => " "
                                                      ));
			}
		}
		
		$this->tpl['LABEL_POWER_TITLE'] = self::_(' TILE SPECIAL POWERS:') ;
		$this->tpl['LABEL_HELP_POWER1'] = self::_(' Stairs: the owner of this tile will be the first player on the next level') ;
		$this->tpl['LABEL_HELP_POWER2'] = self::_(' Secret path: At the end of your turn you have an extra move') ;
		$this->tpl['LABEL_HELP_POWER3'] = self::_(' Release: At the end of the game if this color stack was not discarded you can use this to release one of you guild color tiles');
		$this->tpl['LABEL_HELP_POWER4'] = self::_(' Prisoners Exchange: You can exchange this tile with one on top of other players stacks') ;
		$this->tpl['LABEL_HELP_POWER5'] = self::_(' Remote Trap: You can exchange this tile for any other available on the board') ;
				
        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "dragonkeeper_dragonkeeper", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */



        /*********** Do not change anything below this line  ************/
  	}
  }
  

