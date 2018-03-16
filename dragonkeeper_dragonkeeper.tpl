{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: (c) Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- dragonkeeper implementation : (c) Antonio Soler morgalad.es@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    dragonkeeper_dragonkeeper.tpl
    
    This is the HTML template of your game.
    
    Everything you are writing in this file will be displayed in the HTML page of your game user interface,
    in the "main game zone" of the screen.
    
    You can use in this template:
    _ variables, with the format {MY_VARIABLE_ELEMENT}.
    _ HTML block, with the BEGIN/END format
    
    See your "view" PHP file to check how to set variables and control blocks
    
    Please REMOVE this comment before publishing your game on BGA
-->

<div id="playareascaler">
  <div id="playArea">
	<div id="nowhere"></div>
	<div id="tables"></div>
	<div id="helpower">
		<div id="helpowerinner">
			 <b>{LABEL_POWER_TITLE}</b>
			 <hr>
			 <div class="stairs"></div>{LABEL_HELP_POWER1} <br> 
			 <div class="power2"></div>{LABEL_HELP_POWER2} <br> 
			 <div class="power3"></div>{LABEL_HELP_POWER3} <br> 
			 <div class="power4"></div>{LABEL_HELP_POWER4} <br> 
			 <div class="power5"></div>{LABEL_HELP_POWER5} <br> 
		     
		</div>	 
	</div>
    <div id="sky"></div>
<!-- BEGIN playerboard -->                
				<div id="playerboard_{PLAYER_ID}" class="playerboard player{PLAYER_COUNT} {ACTIVEPLAYER}">
				<h2 class="boardHeader" style="color:#{PLAYER_COLOR};"  >{PLAYER_NAME}</h2>
                    <div id="store_{PLAYER_ID}_0" class="store0 cardstore"></div>
					<div id="store_{PLAYER_ID}_1" class="store1 cardstore"></div>
                    <div id="store_{PLAYER_ID}_2" class="store2 cardstore"></div>
                    <div id="store_{PLAYER_ID}_3" class="store3 cardstore"></div>
                    <div id="store_{PLAYER_ID}_4" class="store4 cardstore"></div>
					<div id="store_{PLAYER_ID}_0_counter" class="counter0"></div>
					<div id="store_{PLAYER_ID}_1_counter" class="counter1"></div>
					<div id="store_{PLAYER_ID}_2_counter" class="counter2"></div>
					<div id="store_{PLAYER_ID}_3_counter" class="counter3"></div>
					<div id="store_{PLAYER_ID}_4_counter" class="counter4"></div>
					<div id="flames_{PLAYER_ID}"  class="flamesdiv"></div>
                </div>
<!-- END playerboard -->
 </div>
</div>

<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${id}"></div>';

*/

var jstpl_player_board = '<br>\<div class="cp_board"></b>\<div id="gold_p${id}" class="goldcounter"> <div class="coin"><span id="goldcount_p${id}">0</span></div></div> <div id="guild_p${id}" class="guildback" ></div></div>';

</script>  

{OVERALL_GAME_FOOTER}
