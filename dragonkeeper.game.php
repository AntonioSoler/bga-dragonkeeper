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
  * dragonkeeper.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class dragonkeeper extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
               "stairs_used" => 10,
               "level" => 11,
               "drakepos" => 12,
               "cardpicked" => 13,
			   "cardtypepicked" => 14,
			   "cardpower" => 15 ,
			   "playerpicked" => 16
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );  
	$this->cards = self::getNew( "module.common.deck" );
	$this->cards->init( "cards" );
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "dragonkeeper";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar, player_gold , player_guild ) VALUES ";
        $values = array();
		$guilds=array(1,2,3,4);
		shuffle($guilds);
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."','".addslashes( 4 )."','". array_pop($guilds)."')";
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        
		self::setGameStateInitialValue( 'level', 3 );
		self::setGameStateInitialValue( 'stairs_used', 0 );
		self::setGameStateInitialValue( 'drakepos', 22 );
		self::setGameStateInitialValue( 'playerpicked', 0 );        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        
		self::initStat( 'table', 'turns_number', 1 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // TODO: setup the initial game situation here
        $cards = array();
        $stairs = array();
		
        foreach( $this->card_types as $cardType)
        {
			if ($cardType['type_id']  == 1)
            {
				$card = array( 'type' => $cardType["type_id"], 'type_arg' => $cardType["value"]  , 'nbr' => $cardType["amount"]);
				array_push($stairs, $card);
            }
			else
			{
            	$card = array( 'type' => $cardType["type_id"], 'type_arg' => $cardType["value"]  , 'nbr' => $cardType["amount"]);
				array_push($cards, $card);
            }
        }
        
        $this->cards->createCards( $cards, 'deck' );   
	    $this->cards->createCards( $stairs, 'stairs' );
        $this->cards->shuffle( 'deck' );		
	   
	    for ($j=0 ; $j<=4 ; $j++ )
		{
			for ($i=0 ; $i<=4 ; $i++ )
			{
				$this->cards->pickCardForLocation( "deck", "table1" , $j*10+$i );
			}
		}
		
		for ($j=0 ; $j<=4 ; $j++ )
		{
			for ($i=0 ; $i<=4 ; $i++ )
			{
				if (($j*10+$i)== 22 ) 
					{
						$this->cards->pickCardForLocation( "stairs", "table2" , $j*10+$i );
					}
				else 
					{
						$this->cards->pickCardForLocation( "deck", "table2" , $j*10+$i );
					}
			}
		}
		
		for ($j=0 ; $j<=4 ; $j++ )
		{
			for ($i=0 ; $i<=4 ; $i++ )
			{
				if (($j*10+$i)== 22 ) 
					{
						$this->cards->pickCardForLocation( "stairs", "table3" , $j*10+$i );
					}
				else 
					{
						$this->cards->pickCardForLocation( "deck", "table3" , $j*10+$i );
					}
			}
		}

        // Activate first player (which is in general a good idea :) )
         $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        $sql = "SELECT player_guild from player WHERE  player_id=".  $current_player_id ;
        $result['player_guild'] = self::getUniqueValueFromDb( $sql );
        
		// Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score , player_gold gold FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
        $result['level'] = self::getGameStateValue('level');
		$result['drakepos'] = self::getGameStateValue('drakepos');
		
		$sql = "SELECT card_id id, card_location_arg location_arg, card_type type , card_type_arg type_arg , card_location location from cards WHERE card_location like 'table%' ORDER BY card_location_arg DESC";
		
        $result['table'] = self::getCollectionFromDb( $sql );
		$players = self::loadPlayersBasicInfos();
        
        $sql = "SELECT card_id id, card_location_arg location_arg, card_type type , card_type_arg type_arg , card_location location from cards WHERE card_location like 'store%' ORDER BY card_location_arg DESC";
		$result['playercards'] = self::getCollectionFromDb( $sql );		
		
        // TODO: Gather all information about current game situation (visible by player $current_player_id).
  
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        // TODO: compute and return the game progression
		
		$sql = "SELECT count(card_id) from cards WHERE card_location like 'table%'  ";
		
        $result = 100 - ( self::getUniqueValueFromDb( $sql ) * 100 ) / 75 ;

        return $result;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */

    function getCardcolor ($cardType)
    {
        return ($this->card_types[$cardType]['color']);
    }
	
	function getCardvalue ($cardType)
    {
        return ($this->card_types[$cardType]['value']);
    }

	function getActivePlayers()
    {
        $playersIds = array();
		$sql = "SELECT player_id id, player_name playerName , player_color playerColor , player_gold gold , player_guild guild FROM player WHERE player_eliminated=0";
        //$playersIds = self::getObjectListFromDB( $sql );
		$playersIds = self::getCollectionFromDB( $sql );	
		return $playersIds;
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in dragonkeeper.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    function pickCard( $card_id)
    {
		self::checkAction( 'pickCard' );
		$player_id = self::getActivePlayerId();
		$thiscard= $this->cards->getCard( $card_id );
        $thiscardtype=$thiscard['type'];
        $thiscardcolor= $this->getCardcolor( $thiscardtype );
		
		//clienttranslate( '${player_name} moves the dragon' )
        self::notifyAllPlayers( "movedrake", "" , array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'drakepos' => 'table'.self::getGameStateValue('level').'field'.$thiscard['location_arg']
            ) );
        self::setGameStateValue('drakepos', $thiscard['location_arg'] );
        $this->cards->insertCardOnExtremePosition( $card_id , "store_".$player_id."_".$thiscardcolor , true );
        self::setGameStateValue("cardpicked", $card_id );
		self::setGameStateValue("playerpicked", $player_id );
		self::setGameStateValue("cardtypepicked", $thiscardtype );
		self::setGameStateValue("cardpower", $this->card_types[ $thiscardtype]['power'] );
		
        self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} takes a tile' ), array(
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                    'card_id' => $card_id,
                    'destination' => "store_".$player_id."_".$thiscardcolor
                    ) );
		if ( self::getGameStateValue("cardpower") == 1 )
		{
			self::setGameStateValue("stairs_used", $player_id );
		}
        if ( ( self::getGameStateValue("cardpower") == 4 ) OR ( self::getGameStateValue("cardpower") == 5 ) )
		{
			$this->gamestate->nextState( "activatePower" );    
		}
		else
		{
			$this->gamestate->nextState( "pickCard" );    
		}
		
    }
	
	function pickcardPower( $card_id)
    {
		self::checkAction( 'pickcardPower' );                                //   1   => clienttranslate("Stairs"       ),
		$player_id = self::getActivePlayerId();                             
		$thiscard= $this->cards->getCard( $card_id );                        //	3   => clienttranslate("Freedom"      ),
        $thiscardtype=$thiscard['type'];                                   
        $thiscardcolor= $this->getCardcolor( $thiscardtype );                //  *  5   => clienttranslate("Remote Trap"  ),
		$cardPower=self::getGameStateValue("cardpower");
		switch ($cardPower) {
		case 2:	   //  * 2   => ("Secret Path" ),
			$oldcard=self::getGameStateValue("cardpicked");
			self::notifyAllPlayers( "movedrake", clienttranslate( '${player_name} moves the dragon using the Secret Path power' ), array(
				'player_id' => $player_id,
				'player_name' => self::getActivePlayerName(),
				'drakepos' => 'table'.self::getGameStateValue('level').'field'.$thiscard['location_arg']
				) );
			self::notifyAllPlayers( "discard", clienttranslate( '${player_name} discards the Secret Path tile' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $oldcard
						) );
			
			$this->cards->insertCardOnExtremePosition( $oldcard , "discard" , true );			
			self::setGameStateValue('drakepos', $thiscard['location_arg'] );
			$this->cards->insertCardOnExtremePosition( $card_id , "store_".$player_id."_".$thiscardcolor , true );
			   	
			self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} takes a tile' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $card_id,
						'destination' => "store_".$player_id."_".$thiscardcolor
						) );
			if ( $this->card_types[$thiscardtype]['power'] == 1 )
			{
				self::setGameStateValue("stairs_used", $player_id );
			}
		break;
		case 4:	    // *  4   => clienttranslate("Exchange"     ),
			
			$targetplayer= explode('_', $thiscard['location'])[1];
			$oldcard= self::getGameStateValue("cardpicked");
			$oldcardcolor=$this->getCardcolor( self::getGameStateValue('cardtypepicked') ); 
			self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} gives the Prisoners Exchange tile to the other player' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $oldcard,
						'destination' => "store_".$targetplayer."_".$oldcardcolor
						) );
			 
			$this->cards->insertCardOnExtremePosition( $oldcard , "store_".$targetplayer."_".$oldcardcolor , true );	

			$this->cards->insertCardOnExtremePosition( $card_id , "store_".$player_id."_".$thiscardcolor , true );
			   	
			self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} takes a tile in exchange' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $card_id,
						'destination' => "store_".$player_id."_".$thiscardcolor
						) );
			if ( $this->card_types[$thiscardtype]['power'] == 1 )
			{
				self::setGameStateValue("stairs_used", $player_id );
			}
		break;
		case 5:	   //  * 2   => ("Remote Trap" ),
			
			$oldcard= self::getGameStateValue("cardpicked");
			self::notifyAllPlayers( "discard", clienttranslate( '${player_name} discards the Remote Trap tile' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $oldcard
						) );
			
			$this->cards->insertCardOnExtremePosition( $oldcard , "discard" , true );			

			$this->cards->insertCardOnExtremePosition( $card_id , "store_".$player_id."_".$thiscardcolor , true );
			   	
			self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} takes a tile' ), array(
						'player_id' => $player_id,
						'player_name' => self::getActivePlayerName(),
						'card_id' => $card_id,
						'destination' => "store_".$player_id."_".$thiscardcolor
						) );
			if ( $this->card_types[$thiscardtype]['power'] == 1 )
			{
				self::setGameStateValue("stairs_used", $player_id );
			}
		break;
		}	
		if ( (self::getGameStateValue("cardpower") == 4) OR   (self::getGameStateValue("cardpower") == 5) )
		{
			$this->gamestate->nextState( "playerDonate" ); 
		}
		else
		{	
			$this->gamestate->nextState( "pickCard" ); 
		}
    }

    function donateCard( $card_id)
    {
		self::checkAction( 'donateCard' );
        $player_id = self::getActivePlayerId();
        $nextplayer_id=self::getPlayerBefore( $player_id );
		$thiscard= $this->cards->getCard( $card_id );
        $thiscardtype=$thiscard['type'];
        $thiscardcolor= $this->getCardcolor( $thiscardtype );
        
        $nextplayer_name=self::getUniqueValueFromDB( "SELECT player_name FROM player where player_id=".$nextplayer_id );
          
		  //clienttranslate( '${player_name} moves the dragon' )
        self::notifyAllPlayers( "movedrake", "" , array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'drakepos' => 'table'.self::getGameStateValue('level').'field'.$thiscard['location_arg']
            ) );
        self::setGamestateValue('drakepos', $thiscard['location_arg'] );

        $this->cards->insertCardOnExtremePosition( $card_id , "store_".$nextplayer_id."_".$thiscardcolor , true );
                                    
        self::notifyAllPlayers( "movecard", clienttranslate( '${player_name} gives a tile to ${nextplayer_name}' ), array(
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                    'nextplayer_name' => $nextplayer_name,
                    'card_id' => $card_id,
                    'destination' => "store_".$nextplayer_id."_".$thiscardcolor
                    ) );

        if ( $this->card_types[$thiscardtype]['value'] >2 )
        {
            self::DbQuery( "UPDATE player set player_gold = player_gold + 1 WHERE Player_id = $player_id" );	
            
            self::notifyAllPlayers( "playergetgold", clienttranslate( '${player_name} gets ${amount} <div class="goldlog"></div> from the treasure as the value of the tile donated is 3 or more' ), array(
                            'player_id' => $player_id,
                            'player_name' => self::getActivePlayerName(),
                            'amount' => 1 ,  
                            'source' => "counter"
                    ) );
        }
		if ( $this->card_types[$thiscardtype]['power'] == 1 )
		{
			self::setGameStateValue("stairs_used", $nextplayer_id );
		}
	
        if ( (self::getGameStateValue("cardpower") == 2)  )
		{
			$this->gamestate->nextState( "activatePower" ); 
		}
		else
		{
			$this->gamestate->nextState( "nextPlayer" );
		}
			
    }

    function playPower()
    {
		self::checkAction( 'playPower' );
		$player_id = self::getActivePlayerId(); 
        $cardPower=self::getGameStateValue("cardpower");
		
		self::notifyAllPlayers( "playerplaypower", clienttranslate( '${player_name} chooses to play ${powername} <div class="power${power}"></div>' ), array(
                            'player_id' => $player_id,
                            'player_name' => self::getActivePlayerName(),
                            'power' =>$cardPower,
							'powername' => $this->cardpowers[$cardPower]
                    ) );
        $this->gamestate->nextState( "playPower" );    
    }
    
    function pass()
    {
		self::checkAction( 'pass' );
		$player_id = self::getActivePlayerId();
        if ( ( self::getGameStateValue("cardpower") == 4 ) OR ( self::getGameStateValue("cardpower") == 5 ) )
		{
			$this->gamestate->nextState( "playerDonate" );    
		}
        else
		{
			$this->gamestate->nextState( "pass" );   
		}
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

    function argPossiblePicks()
    {   
		$player_id = self::getActivePlayerId();
        $level=self::getGameStateValue( 'level');
		$drake_pos=self::getGameStateValue( 'drakepos');
		$Xdrakepos= $drake_pos % 10 ;
		$Ydrakepos=  ($drake_pos - $drake_pos % 10) / 10; 
        $result=  array( 'possibledestinations' => array() );
        $sql="SELECT concat('card_',card_id) id from cards where ( card_location like 'table".$level."' ) and ( ". $Ydrakepos ." = floor( card_location_arg / 10 ))";
		$result["possibledestinations"] = self::getObjectListFromDB( $sql );	
		/*if (sizeof($result["possibledestinations"]) < 1)
        {
			$this->gamestate->nextState( "nextLevel" );
		}*/
        return $result ;	
    }

    function argPossibleDonations()
    {   
		$player_id = self::getActivePlayerId();
        $level=self::getGameStateValue( 'level');
		$drake_pos=self::getGameStateValue( 'drakepos');
		$Xdrakepos= $drake_pos % 10 ;
		$Ydrakepos=  ($drake_pos - $drake_pos % 10) / 10; 
        $result=  array( 'possibledestinations' => array() );
        
		$sql="SELECT concat('card_',card_id) id from cards where ( card_location like 'table".$level."' ) and ( ". $Xdrakepos ." = mod( card_location_arg , 10 ))";
	    $result["possibledestinations"] = self::getObjectListFromDB( $sql );	
		
		/* if (sizeof($result["possibledestinations"]) < 1)
        {
			$this->gamestate->nextState( "nextLevel" );
	    }*/
        return $result ;	
    }
	
	function argPossibleTargets()
    {                                                                                 
		$player_id = self::getActivePlayerId();                                       
        $level=self::getGameStateValue( 'level');                                     
		$drake_pos=self::getGameStateValue( 'drakepos');                              
		$Xdrakepos= $drake_pos % 10 ;                                                 
		$Ydrakepos=  ($drake_pos - $drake_pos % 10) / 10;                   //   1   => clienttranslate("Stairs"       ),
        $result=  array( 'possibledestinations' => array() );               //  * 2   => clienttranslate("Secret Path" ),
        switch ( self::getGameStateValue("cardpower") )                     //	3   => clienttranslate("Freedom"      ),
		{                                                                   // *  4   => clienttranslate("Exchange"     ),
			case "2":                                                       //  *  5   => clienttranslate("Remote Trap"  ),
				$sql="SELECT concat('card_',card_id) id from cards where ( card_location like 'table".$level."' ) and ( ". $Ydrakepos ." = floor( card_location_arg / 10 ))";
			    $result["possibledestinations"] = self::getObjectListFromDB( $sql );	
			break;
			case "4":
				$sql="SELECT concat('card_',card_id) id from cards where (card_location , card_location_arg) in ( SELECT card_location,max(card_location_arg) FROM cards WHERE card_location like 'store_%' and not (card_location LIKE 'store_".$player_id."_%' ) group by card_location)";
			    $result["possibledestinations"] = self::getObjectListFromDB( $sql );	 
			break;
			case "5":
				$sql="SELECT concat('card_',card_id) id from cards where card_location like 'table".$level."' ";
			    $result["possibledestinations"] = self::getObjectListFromDB( $sql );
			
			break;
		}
		/*if (sizeof($result["possibledestinations"]) < 1)
        {
			$this->gamestate->nextState( "nextLevel" );
		}*/
        return $result ;	
    }
	function argPowertype()
    {   
		$result=array ( 'cardpower' => self::getGameStateValue( 'cardpower'));
		return $result ;	
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":
    */
    
    function stNextPlayer()
    {
        // Do some stuff ...
        self::setGameStateValue("cardpower",0);
		self::setGameStateValue("cardpicked",0);
		self::setGameStateValue("cardtypepicked",0);

        // (very often) go to another gamestate
	
		$this->activeNextPlayer();
		$player_id = self::getActivePlayerId();
		self::giveExtraTime($player_id);
		self::incStat( 1 ,"turns_number" );
		self::incStat( 1 ,"turns_number", $player_id );
		$this->gamestate->nextState( "nextPlayer" );
	
    }   

	function stNextLevel()
    {
        self::setGameStateValue("cardpower",0);
		self::setGameStateValue("cardpicked",0);
		self::setGameStateValue("cardtypepicked",0);
        $lastplayer= self::getGameStateValue( 'playerpicked' );
		$level=self::getGameStateValue( 'level' );
		$sql = "SELECT count(card_id) from cards WHERE card_location like 'table".$level."' ";		
        $cardCount = self::getUniqueValueFromDb ($sql);
		$stairsPlayer=self::getGameStateValue( 'stairs_used' );
		self::setGameStateValue("stairs_used",0);
		$players = self::loadPlayersBasicInfos();
		if ($cardCount > 8 ){
			$tribute=3;
		} elseif  ($cardCount > 5 ){
			$tribute=2;
		} elseif ($cardCount > 0 ){
			$tribute=1;
		} else {
			$tribute=0;
		}	
		
		$activePlayers=$this->getActivePlayers();
		foreach($activePlayers as $playerId => $player )
		{
			$thisid = $player['id'] ;
			$thisPlayerName = $players[$thisid]['player_name'];
			$gold=$player['gold'];
			
			if (( $lastplayer == $thisid ) )
			{
				$gold=$gold - 1 ;
				self::notifyAllPlayers( "playerpaysgold", clienttranslate( '${player_name} pays ${amount} <div class="goldlog"></div> for taking the last tile of the level ' ), array(
                            'player_id' => $thisid,
                            'player_name' => $thisPlayerName,
                            'amount' => 1 
                    ) );
				
			}
			
			if ( $stairsPlayer == $thisid ) 
			{
				if ( self::getActivePlayerId() != $thisid  )
				{
					$this->gamestate->changeActivePlayer( $thisid );
				}
				self::notifyAllPlayers( "message", clienttranslate( '${player_name} is now the first player as this player has the <div class="stairs"></div> tile from previous level' ), array(
						'player_name' => $thisPlayerName
					) );
			}
			
			if ($tribute > 0)
			{
				$gold=$gold - $tribute ;
				self::notifyAllPlayers( "playerpaysgold", clienttranslate( '${player_name} pays ${amount} <div class="goldlog"></div> as tribute as there were ${cardcount} tiles not picked in this level' ), array(
                            'player_id' => $thisid,
                            'player_name' => $thisPlayerName,
                            'amount' => $tribute ,
							'cardcount' => $cardCount 
                    ) );
			}
			self::DbQuery( "UPDATE player set player_gold = ".$gold." WHERE Player_id = $thisid" );
			if ($gold < 0 )
			{
				if (self::getActivePlayerId() == $thisid) {
					$this->activeNextPlayer();
					}
				
				//self::eliminatePlayer( $thisid );
				self::DbQuery( "UPDATE player set player_eliminated = 1 WHERE Player_id = $thisid" );
				
				self::notifyAllPlayers( "message", clienttranslate( '${player_name} <b>is now eliminated from the game as this player cannot pay the tribute!</b>' ), array(
						'player_name' => $thisPlayerName
					) );
			}	
		}
		
	   if (( $level > 1 ) AND ( sizeof($this->getActivePlayers()) >1 ) )
        {
			self::setGameStateValue( 'drakepos', 22 );
			self::DbQuery( "UPDATE cards set card_location = 'discard' WHERE card_location = 'table". $level ."'" );
            self::notifyAllPlayers( "levelchange", clienttranslate( '<b> We move to the next level of the dungeon! </b>' ), array(
						'level' => $level,
						'drakepos' => 'table'. ( $level - 1 ) .'field'. 22
					) ); 
			$level=$level-1;
            self::setGameStateValue( 'level', $level );
			
			
	        $this->gamestate->nextState( "nextPlayer" );
        }
        else
		{
			$this->gamestate->nextState( "endGameScoring" );
		}
    }   
	
	function stCheckpick()
    {
        // Do some stuff ...
        $result=$this->argPossiblePicks();
		//var_dump ( sizeof($result["possibledestinations"]) );
        if (sizeof($result["possibledestinations"]) < 1)
        {
			$this->gamestate->nextState( "nextLevel" );
		}
    }
	
	function stCheckdonate()
    {
        // Do some stuff ...
        $result=$this->argPossibleDonations();
        if (sizeof($result["possibledestinations"]) == 0)
        {
			$this->gamestate->nextState( "nextLevel" );
		}
    }
	
	function stCheckpower()
    {
        // Do some stuff ...
        $result=$this->argPossibleTargets();
        if (sizeof($result["possibledestinations"]) == 0)
        {
			if ( (self::getGameStateValue("cardpower") == 4) OR  (self::getGameStateValue("cardpower") == 5) )
			{
				$this->gamestate->nextState( "playerDonate" ); 
			}
			else
			{	
				$this->gamestate->nextState( "pickCard" ); 
			}
		}
    }
	
	function displayScores()
    {
        $players = self::loadPlayersBasicInfos();
		      
        $table[] = array();
        
        //left hand col
		
        $table[0][0] = array( 'str' => 'Players:', 'args' => array(), 'type' => 'header');
        $table[0][1] = array( 'str' => "<div class='header1'></div>" .clienttranslate($this->cardcolors[1]), 'args' => array(), 'type' => 'header');
        $table[0][2] = array( 'str' => "<div class='header3'></div>" .clienttranslate($this->cardcolors[3]), 'args' => array(), 'type' => 'header');
		$table[0][3] = array( 'str' => "<div class='header0'></div>" .clienttranslate($this->cardcolors[0]), 'args' => array(), 'type' => 'header');
        $table[0][4] = array( 'str' => "<div class='header2'></div>" .clienttranslate($this->cardcolors[2]), 'args' => array(), 'type' => 'header');
		$table[0][5] = array( 'str' => "<div class='header4'></div>" .clienttranslate($this->cardcolors[4]), 'args' => array(), 'type' => 'header');
        $table[0][6] = array( 'str' => "<div class='coin'></div>".clienttranslate($this->resources["gold"])    , 'args' => array(), 'type' => 'header');
		
		$table[0][] = clienttranslate($this->resources["score_window_title"]);
		
		$i = 1 ;
		
        foreach( $players as $player_id => $player )
        {
            $score=0;
			$sql = "SELECT player_guild from player WHERE  player_id=". $player_id ;
			$player_guild = self::getUniqueValueFromDb( $sql );
			$sql = "SELECT player_gold from player WHERE  player_id=". $player_id ;
			$player_gold  = self::getUniqueValueFromDb( $sql );
			$sql = "SELECT max(player_gold) from player WHERE  player_eliminated=0 " ;
			$max_player_gold  = self::getUniqueValueFromDb( $sql );
			$thisplayername=$player['player_name'];
			$score_gold = 0 ;
			if ( $player_gold == $max_player_gold )
			{ 
				$score_gold = 2 ; 
			}
			
			$table[$i][] = array( 'str' => '${player_name} <div class="guildtile guild${player_guild}"></div>',
                                 'args' => array( 'player_name' => $player['player_name'], 
								                  'player_guild' => $player_guild         ),
                                 'type' => 'header'
                               );
							   
            $cards_picked = array( );
			
			$cards_picked[0]=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'_0") ');
			$cards_picked[1]=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'_1") ');
			$cards_picked[2]=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'_2") ');
			$cards_picked[3]=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'_3") ');
			$cards_picked[4]=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'_4") ');
			
			$biggest_stack= max( $cards_picked );
			
			$r = array();
			foreach ($cards_picked as $key => $value ) {
				if ($cards_picked[$key] ==  $biggest_stack ) {
                $r[] = $key;
				}
			}
            
			$cards_values = array();   
			$cards_values[0]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_0") ');
			$cards_values[1]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_1") ');
			$cards_values[2]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_2") ');
			$cards_values[3]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_3") ');
			$cards_values[4]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_4") ');
			
			self::setStat( $cards_picked[0], 'red_picked'   , $player['player_id'] );
			self::setStat( $cards_picked[1], 'blue_picked'  , $player['player_id'] );
			self::setStat( $cards_picked[2], 'yellow_picked', $player['player_id'] );
			self::setStat( $cards_picked[3], 'green_picked' , $player['player_id'] );
			self::setStat( $cards_picked[4], 'purple_picked', $player['player_id'] );
			self::setStat( $player_guild ,   'player_guild' , $player['player_id'] );
			self::setStat( $player_gold ,    'player_gold' ,  $player['player_id'] );
			
			$biggest_stack= max( $cards_picked ); 
		   
			$r = array();
			
			foreach ($cards_picked as $key => $value ) {      //What are the biggest stacks now? 
				if ($cards_picked[$key] ==  $biggest_stack ) {
				$r[] = $key;
				}
			}
			
			if (in_array( $player_guild, $r))    /// Is the player guild the biggest stack?
			{   
				$cards_picked[$player_guild]=0;   // Remove the player guild cards
				
				self::notifyAllPlayers( "message" , clienttranslate( '${player_name} discards the highest stack <div class="header${color}"></div> and it matches this players guild : <div class="guildtile guild${color}"></div> ' ), 
						array(			
							'player_name' =>  $thisplayername,
							'color' =>  $player_guild
							) ) ;
				
				$cards_values[$player_guild]=9999 ;
				
				$biggest_stack= max( $cards_picked ); 
			   
				$r = array();
				
				foreach ($cards_picked as $key => $value ) {      //What are the biggest stacks now? 
					if ($cards_picked[$key] ==  $biggest_stack ) {
					$r[] = $key;
					}
				}
				$min_value = 9999;
				for ( $j=0 ; $j < count($r); $j++ )     // What has the smaller value?
				{
				   if ( $cards_values[$r[$j]] < $min_value) {
					   $min_value_stack = $r[$j];
					   $min_value = $cards_values[$r[$j]];
				   }
				}
				$cards_values[$player_guild]=0 ;
				$cards_values[$min_value_stack]=0 ;
				self::notifyAllPlayers( "message" , clienttranslate( '${player_name} discards also a second stack: <div class="header${color}"></div>' ), 
						array(			
							'player_name' =>  $thisplayername,
							'color' =>  $min_value_stack
							) ) ;
				
				self::setStat( $cards_values[0], 'red_score'   , $player['player_id'] );
			    self::setStat( $cards_values[1], 'blue_score'  , $player['player_id'] );
			    self::setStat( $cards_values[2], 'yellow_score', $player['player_id'] );
			    self::setStat( $cards_values[3], 'green_score' , $player['player_id'] );
			    self::setStat( $cards_values[4], 'purple_score', $player['player_id'] );
			    self::setStat( $score_gold ,    'score_gold' ,  $player['player_id'] );
			}
			else
			{
				
				$min_value = 9999;
				for ( $j=0 ; $j < count($r); $j++ )    // What has the smaller value?
				{
				   // self::dump( ' *********** $cards_values[$r[$j]', $cards_values[$r[$j]] );
				   if ( $cards_values[$r[$j]] <= $min_value) {
					   $min_value_stack = $r[$j];
					   $min_value = $cards_values[$r[$j]];
				   }
				}
				
				self::notifyAllPlayers( "message" , clienttranslate( '${player_name} discards the highest stack: <div class="header${color}"></div>' ), 
						array(			
							'player_name' =>  $thisplayername,
							'color' =>  $min_value_stack
							) ) ;
				
				self::DbQuery( "UPDATE cards set card_location = 'discard' WHERE card_location like 'store_".$player_id."_".$min_value_stack."'");
				 // 10 19 28 37   Free
				$Release_cards=self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE  ( card_location like "store_'.$player_id.'%") AND (card_type in ( 10 ,19 , 28, 37)) ');
				
				for ($j=0 ; $j < $Release_cards ; $j++ )
				{
					if ( self::getUniqueValueFromDB('SELECT count(*) FROM cards WHERE ( card_location like "store_'.$player_id.'_'.$player_guild.'") AND (card_type_arg > 3 )') > 1 )
					{
						self::notifyAllPlayers( "message" , clienttranslate( '${player_name} uses a Release power tile <div class="power3"></div> the highest valued tile from this player guild does not score negatively' ), 
						array(			
							'player_name' =>  $thisplayername 	
							) ) ;
						self::DbQuery( "UPDATE cards set card_location = 'discard' WHERE ( card_location like 'store_".$player_id."_".$player_guild."' ) AND (card_type_arg > 3 ) ORDER by card_type_arg DESC LIMIT 1");
						self::DbQuery( "UPDATE cards set card_location = 'discard' WHERE ( card_location like 'store_".$player_id."%') AND (card_type in ( 10 ,19 , 28, 37)) LIMIT 1");
					}
				}
				
				$cards_values = array();   // Recalculate stack values
				$cards_values[0]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_0") ');
				$cards_values[1]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_1") ');
				$cards_values[2]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_2") ');
				$cards_values[3]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_3") ');
				$cards_values[4]=self::getUniqueValueFromDB('SELECT coalesce( sum( card_type_arg ),0) FROM cards WHERE ( card_location like "store_'.$player_id.'_4") ');
                
				$cards_values[$player_guild] =  -1 * $cards_values[$player_guild] ;   // GUILD CARDS are negative
				
				self::setStat( $cards_values[0], 'red_score'   , $player['player_id'] );
			    self::setStat( $cards_values[1], 'blue_score'  , $player['player_id'] );
			    self::setStat( $cards_values[2], 'yellow_score', $player['player_id'] );
			    self::setStat( $cards_values[3], 'green_score' , $player['player_id'] );
			    self::setStat( $cards_values[4], 'purple_score', $player['player_id'] );
			    self::setStat( $score_gold ,    'score_gold' ,  $player['player_id'] );
			}
			
			$table[$i][] = self::getStat('blue_picked'  ,$player['player_id'])." ". clienttranslate ("tiles") ." = ". self::getStat('blue_score'  ,$player['player_id'])."<div class='fa fa-star'></div>";
			$table[$i][] = self::getStat('green_picked' ,$player['player_id'])." ". clienttranslate ("tiles") ." = ". self::getStat('green_score' ,$player['player_id'])."<div class='fa fa-star'></div>";
			$table[$i][] = self::getStat('red_picked'   ,$player['player_id'])." ". clienttranslate ("tiles") ." = ". self::getStat('red_score'   ,$player['player_id'])."<div class='fa fa-star'></div>";
			$table[$i][] = self::getStat('yellow_picked',$player['player_id'])." ". clienttranslate ("tiles") ." = ". self::getStat('yellow_score',$player['player_id'])."<div class='fa fa-star'></div>";
			$table[$i][] = self::getStat('purple_picked',$player['player_id'])." ". clienttranslate ("tiles") ." = ". self::getStat('purple_score',$player['player_id'])."<div class='fa fa-star'></div>";
			
			$table[$i][] = $player_gold ." <div class='goldlog'></div> = ". $score_gold ." <div class='fa fa-star'></div>";
			
			$score= $score_gold + self::getStat('red_score'   ,$player['player_id']) 
								+ self::getStat('blue_score'  ,$player['player_id']) 
								+ self::getStat('yellow_score',$player['player_id']) 
								+ self::getStat('green_score' ,$player['player_id']) 
								+ self::getStat('purple_score',$player['player_id']);
			
			if 	(self::getUniqueValueFromDb( "SELECT player_eliminated from player WHERE  player_id=". $player_id  ) == 1 )				
			{
				$score=0;
			}
			$table[$i][] = "<b>". $score ."</b><div class='fa fa-star'></div>"; ;
			
			$sql = "UPDATE player SET player_score = ".$score." WHERE player_id=".$player['player_id'];
            self::DbQuery( $sql ); 
            $i++;			
        }
        $players_score = self::getCollectionFromDB( "SELECT player_id id, player_score score FROM player" );
		
		$this->notifyAllPlayers( "notif_finalScore", '', array(
            "id" => 'finalScoring',
            "title" => $this->resources["score_window_title"],
            "table" => $table,
			"header" =>$this->resources["win_condition"],
			"footer" =>$this->resources["end_condition"],
			"closing" => clienttranslate( "OK" ),
			'players' => $players_score ,
           'i18n' => array( 'header' , 'footer')
           
        ) ); 
    }

	
    function stendGameScoring()
    {
        // Do some stuff ...
        $this->displayScores();
        // (very often) go to another gamestate
        $this->gamestate->nextState( "endGame" );
    }    


//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            $sql = "ALTER TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            $sql = "CREATE TABLE xxxxxxx ....";
//            self::DbQuery( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
