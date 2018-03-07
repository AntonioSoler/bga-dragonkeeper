/**
 *------
 * BGA framework: (c) Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * dragonkeeper implementation : (c) Antonio Soler morgalad.es@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dragonkeeper.js
 *
 * dragonkeeper user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.dragonkeeper", ebg.core.gamegui, {
        constructor: function(){
            console.log('dragonkeeper constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

            if (!dojo.hasClass("ebd-body", "mode_3d")) {
                    dojo.addClass("ebd-body", "mode_3d");
                    //dojo.addClass("ebd-body", "enableTransitions");
                    $("globalaction_3d").innerHTML = "3D";   // controls the upper right button 
                    this.control3dxaxis = 10;  // rotation in degrees of x axis (it has a limit of 0 to 80 degrees in the frameword so users cannot turn it upsidedown)
                    this.control3dzaxis = 0;   // rotation in degrees of z axis
                    this.control3dxpos = -100;   // center of screen in pixels
                    this.control3dypos = -100;   // center of screen in pixels
                    this.control3dscale = 0.8;   // zoom level, 1 is default 2 is double normal size, 
                    this.control3dmode3d = true ;  			// is the 3d enabled	
                     //    transform: rotateX(10deg) translate(-100px, -100px) rotateZ(0deg) scale3d(0.7, 0.7, 0.7);
    
                    $("game_play_area").style.transform = "rotatex(" + this.control3dxaxis + "deg) translate(" + this.control3dypos + "px," + this.control3dxpos + "px) rotateZ(" + this.control3dzaxis + "deg) scale3d(" + this.control3dscale + "," + this.control3dscale + "," + this.control3dscale + ")";
                }

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );
            this.param=new Array();
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_player_board', player ), player_board_div );
				dojo.byId("goldcount_p"+player_id).innerHTML=player['gold'];
				if (parseInt(player_id)== this.player_id) {
					dojo.byId("guild_p"+player_id).className= "guildtile guild"+gamedatas.player_guild ;
				    this.addTooltipToClass("guildtile", _('This is your guild color, the tiles of this color that you collect will take away points '),"");
					} 
            }
		
			for( var i=1 ; i<=  gamedatas.level; i++ )
            {
                dojo.place("<div id='table"+i+"'><div id='tableinner"+i+"' class='tableinner'><div class='side1'></div><div class='side2'></div><div class='side3'></div><div class='side4'></div></div></div>", "tables" , "last" ) ;
                for (r=0; r<=4 ; r++)
				{
					for (c=0; c<=4 ; c++)
					{
						dojo.place("<div id='table"+i+"field"+(r*10+c)+"' class='field' style='transform: translate3d("+ (c*122+15)+"px , "+ (r*122+15)+"px, 0px);'></div>", "tableinner"+i , "last" ) ;
					}	
				}
			}
            // TODO: Set up your game interface here, according to "gamedatas"
            
			for( var i in this.gamedatas.table )
			{
					var card = this.gamedatas.table[i];
					
					this.placecard(card['location']+'field'+card['location_arg'] ,card['id'],card['type'],'tables', 1 );
			}
            
            for( var i in this.gamedatas.playercards )
			{
					var card = this.gamedatas.playercards[i];
					
					this.placecard(card['location'] ,card['id'],card['type'],'playArea',card['location_arg']);
			}

            
			dojo.place ( "<div id='drake'><div id='drakefoot'><div id='drakebody'></div><div id='drakewings'></div></div></div>", "table"+gamedatas.level+"field"+gamedatas.drakepos );
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

			this.addTooltipToClass("guildback", _("Your opponent unknown guild color"), "");
			
			this.addTooltipToClass("flamesdiv", _("At the end of each level a number of coins have to be paid as tribute depending on the remaining cards on the level, 1 coin for 1-5 cards, 2 coins for 6-8 cards and 3 coins for more than 9 cards"), "")
			
			
			
            console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            
            /* Example:
            
            case 'myGameState':
            
                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );
                
                break;
           */
           
            case 'playerPick':
              if (this.isCurrentPlayerActive() )
              {   

                  list = args.args.possibledestinations;
                  this.gameconnections=new Array();
                  for (var i = 0; i < list.length; i++)
                  {
                      var thiselement = list[i];
                      thistarget=dojo.query("#"+thiselement.id ).addClass( 'borderpulse' ) ;
                      this.gameconnections.push( dojo.connect(thistarget[0], 'onclick' , this, 'pickcard'))
                  }
              }
            break;
            case 'playerDonate':
            
                  if (this.isCurrentPlayerActive() )
                  {
                      list = args.args.possibledestinations;
                      this.gameconnections=new Array();
                      for (var i = 0; i < list.length; i++)
                      {
                          var thiselement = list[i];
                          thistarget=dojo.query("#"+thiselement.id ).addClass( 'borderpulse' ) ;
                          this.gameconnections.push( dojo.connect(thistarget[0], 'onclick' , this, 'donatecard'))
                      }
                  }
                break;
			case 'playPower':
              if (this.isCurrentPlayerActive() )
              {
                  list = args.args.possibledestinations;
                  this.gameconnections=new Array();
                  for (var i = 0; i < list.length; i++)
                  {
                      var thiselement = list[i];
                      thistarget=dojo.query("#"+thiselement.id ).addClass( 'borderpulse' ) ;
                      this.gameconnections.push( dojo.connect(thistarget[0], 'onclick' , this, 'pickcardPower'))
                  }
              }
            break;
			case 'activatePower':  
				
				if (this.isCurrentPlayerActive() )                             //  1   => clienttranslate("Stairs"       ),
				{                                                              //  2   => clienttranslate("Secret Path" ),
				  switch(args.args.cardpower)                                  //  3   => clienttranslate("Freedom"      ),
					{                                                          //  4   => clienttranslate("Prisoners Exchange"),
																			   //  5   => clienttranslate("Remote Trap"  ),
						  
						 case '2':
						  this.gamedatas.gamestate.descriptionmyturn = _('Do you want to activate the power of the card Secret Path <div class="power2"></div> ') ;
						  break; 
						
						 case '4':
						  this.gamedatas.gamestate.descriptionmyturn = _('Do you want to activate the power of the card Prisoners Exchange <div class="power4"></div> ') ;
						  break; 
						case '5':
						  this.gamedatas.gamestate.descriptionmyturn = _('Do you want to activate power of the card Remote Trap <div class="power5"></div> ') ;
						  break;
					}
					this.updatePageTitle();
				}
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
                case 'playerDonate':
					if (this.isCurrentPlayerActive() )
					{
						dojo.forEach(this.gameconnections, dojo.disconnect);
						dojo.query(".borderpulse").removeClass("borderpulse");
						this.gameconnections=new Array();
					}
                break;
				case 'playerPick':
					if (this.isCurrentPlayerActive() )
					{
						dojo.forEach(this.gameconnections, dojo.disconnect);
						dojo.query(".borderpulse").removeClass("borderpulse");
						this.gameconnections=new Array();
					}
                break;
				case 'playPower':
					if (this.isCurrentPlayerActive() )
					{
						dojo.forEach(this.gameconnections, dojo.disconnect);
						dojo.query(".borderpulse").removeClass("borderpulse");
						this.gameconnections=new Array();
					}
                break;
          
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
/*               
                 Example:
 
                 case 'myGameState':
                    
                    // Add 3 action buttons in the action status bar:
                    
                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                    break;
*/
                case 'activatePower':
                this.addActionButton( 'yes_button', _('Activate Power'), 'playPower' );
                this.addActionButton( 'no_button', _('Pass'), 'pass' );
                break;

                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
		giveGold: function ( source, destination ,amount) 
		{
			var animspeed=200;
			for (var i = 1 ; i<= amount ; i++)
			{
				this.slideTemporaryObjectAndIncCounter( '<div class="coin spining"></div>', 'overall-content', source, destination, 1000 , animspeed );
				animspeed += 200;
			}
        },
		
		payGold: function ( source, destination ,amount) 
		{
			var animspeed=200;
			for (var i = 1 ; i<= amount ; i++)
			{
				dojo.byId(source).innerHTML=eval(dojo.byId(source).innerHTML) - 1;
				this.MySlideTemporaryObject( '<div class="coin spining"></div>', 'overall-content', source, destination, 1000 , animspeed );
				animspeed += 200;
			}
        },
		
		placecard: function ( destination, card_id ,card_type , origin , location_arg )
		{
			xpos= -100*((card_type - 1 )%7 );
			ypos= -100*(Math.floor( (card_type -1 ) / 7 ));
			position= xpos+"px "+ ypos+"px ";
			
			//dojo.style('stile_back_'+location_arg , "background-position", position);
            innercard="<div id='card_"+card_id+"' class='card' style='background-position:"+position+"; ";
            //innercard+=" transform: translateZ("+5*location_arg+"px)";  
            innercard+="'></div>";
			dojo.place( innercard , destination, "last");
            
		},
		
		attachToNewParentNoDestroy : function(mobile, new_parent) {
            if (mobile === null) {
                console.error("attachToNewParent: mobile obj is null");
                return;
            }
            if (new_parent === null) {
                console.error("attachToNewParent: new_parent is null");
                return;
            }
            if (typeof mobile == "string") {
                mobile = $(mobile);
            }
            if (typeof new_parent == "string") {
                new_parent = $(new_parent);
            }

            var src = dojo.position(mobile);
            dojo.style(mobile, "position", "absolute");
            dojo.place(mobile, new_parent, "last");
            return;
        },

		computeVertexData: function (elem) {
			var w = elem.offsetWidth / 2,
				h = elem.offsetHeight / 2,
				v = {
					  a: { x: 0, y: 0, z: 0 }
				},
				transform;
			// Walk up the DOM and apply parent element transforms to each vertex
			while (elem.id != "overall-content" ) {
				transform = this.getTransform(elem);
				v.a = this.addVectors( v.a , transform.translate );
				elem = elem.parentNode;		
			}
			return v;
			
		},
		
	getTransform :	function (elem) {
    var computedStyle = getComputedStyle(elem, null),
        val = computedStyle.transform ||
            computedStyle.webkitTransform ||
            computedStyle.MozTransform ||
            computedStyle.msTransform,
        matrix = this.parseMatrix(val),
        rotateY = Math.asin(-matrix.m13),
        rotateX, 
        rotateZ;
        position = computedStyle.position;
        rotateX = Math.atan2(matrix.m23, matrix.m33);
        rotateZ = Math.atan2(matrix.m12, matrix.m11);
    return {
        transformStyle: val,
        matrix: matrix,
        rotate: {
            x: rotateX,
            y: rotateY,
            z: rotateZ
        },
        translate: {
            x: matrix.m41,
            y: matrix.m42,
            z: matrix.m43
        },
		position: position
    };
},


/* Parses a matrix string and returns a 4x4 matrix
---------------------------------------------------------------- */

parseMatrix: function  (matrixString) {
    var c = matrixString.split(/\s*[(),]\s*/).slice(1,-1),
        matrix;

    if (c.length === 6) {
        // 'matrix()' (3x2)
        matrix = {
            m11: +c[0], m21: +c[2], m31: 0, m41: +c[4],
            m12: +c[1], m22: +c[3], m32: 0, m42: +c[5],
            m13: 0,     m23: 0,     m33: 1, m43: 0,
            m14: 0,     m24: 0,     m34: 0, m44: 1
        };
    } else if (c.length === 16) {
        // matrix3d() (4x4)
        matrix = {
            m11: +c[0], m21: +c[4], m31: +c[8], m41: +c[12],
            m12: +c[1], m22: +c[5], m32: +c[9], m42: +c[13],
            m13: +c[2], m23: +c[6], m33: +c[10], m43: +c[14],
            m14: +c[3], m24: +c[7], m34: +c[11], m44: +c[15]
        };

    } else {
        // handle 'none' or invalid values.
        matrix = {
            m11: 1, m21: 0, m31: 0, m41: 0,
            m12: 0, m22: 1, m32: 0, m42: 0,
            m13: 0, m23: 0, m33: 1, m43: 0,
            m14: 0, m24: 0, m34: 0, m44: 1
        };
    }
    return matrix;
},

/* Adds vector v2 to vector v1
---------------------------------------------------------------- */

addVectors: function (v1, v2) {
    return {
        x: v1.x + v2.x,
        y: v1.y + v2.y,
        z: v1.z + v2.z
    };
},


/* Rotates vector v1 around vector v2
---------------------------------------------------------------- */

		rotateVector: function  (v1, v2) {
			var x1 = v1.x,
				y1 = v1.y,
				z1 = v1.z,
				angleX = v2.x / 2,
				angleY = v2.y / 2,
				angleZ = v2.z / 2,

				cr = Math.cos(angleX),
				cp = Math.cos(angleY),
				cy = Math.cos(angleZ),
				sr = Math.sin(angleX),
				sp = Math.sin(angleY),
				sy = Math.sin(angleZ),

				w = cr * cp * cy + -sr * sp * -sy,
				x = sr * cp * cy - -cr * sp * -sy,
				y = cr * sp * cy + sr * cp * sy,
				z = cr * cp * sy - -sr * sp * -cy,

				m0 = 1 - 2 * ( y * y + z * z ),
				m1 = 2 * (x * y + z * w),
				m2 = 2 * (x * z - y * w),

				m4 = 2 * ( x * y - z * w ),
				m5 = 1 - 2 * ( x * x + z * z ),
				m6 = 2 * (z * y + x * w ),

				m8 = 2 * ( x * z + y * w ),
				m9 = 2 * ( y * z - x * w ),
				m10 = 1 - 2 * ( x * x + y * y );

			return {
				x: x1 * m0 + y1 * m4 + z1 * m8,
				y: x1 * m1 + y1 * m5 + z1 * m9,
				z: x1 * m2 + y1 * m6 + z1 * m10
			};
		},
		
        delayedExec : function(onStart, onEnd, duration, delay) {
            if (typeof duration == "undefined") {
                duration = 500;
            }
            if (typeof delay == "undefined") {
                delay = 0;
            }
            if (this.instantaneousMode) {
                delay = Math.min(1, delay);
                duration = Math.min(1, duration);
            }
            if (delay) {
                setTimeout(function() {
                    onStart();
                    if (onEnd) {
                        setTimeout(onEnd, duration);
                    }
                }, delay);
            } else {
                onStart();
                if (onEnd) {
                    setTimeout(onEnd, duration);
                }
            }
        },
        /**
         * This method is similar to slideToObject but works on object which do not use inline style positioning. It
         * also attaches object to new parent immediately, so parent is correct during animation
         */
 
		slideToObjectAbsolute : function(token, finalPlace, x, y, duration,delay,onEnd) {
            if (typeof token == 'string') {
                token = $(token);
            }
			if (typeof finalPlace == 'string') {
                finalPlace = $(finalPlace);
            }
            
            var self = this;
				
             this.delayedExec(function() {
                self.stripTransition(token);

				
				origin=self.computeVertexData(token);
				destination=self.computeVertexData(finalPlace);	
				
				x += origin.a.x - destination.a.x;
				y += origin.a.y - destination.a.y;
				z = origin.a.z - destination.a.z;
				dojo.style (token , { transform: "translate3D("+ x +"px, "+ y +"px, "+ z +"px)" });
				self.setTransition(token, "all " + duration + "ms ease-in-out");
				self.attachToNewParentNoDestroy (token,finalPlace);
            
            }, function() {
                self.stripPosition(token);
				if (onEnd) {
                    setTimeout(onEnd, duration);
                }
            }, duration, delay);
			
        },
		
		stripPosition : function(token) {
			// console.log(token + " STRIPPING");
			// remove any added positioning style
			dojo.style(token, "display", null);
			dojo.style(token, "top", null);
			dojo.style(token, "left", null);
			dojo.style(token, "position", null);
			dojo.style (token , { transform: "" });
		},
		stripTransition : function(token) {
			this.setTransition(token, "");
		},
		setTransition : function(token, value) {
			dojo.style(token, "transition", value);
			dojo.style(token, "-webkit-transition", value);
			dojo.style(token, "-moz-transition", value);
			dojo.style(token, "-o-transition", value);
		},
		resetPosition : function(token) {
            // console.log(token + " RESETING");
            // remove any added positioning style
            dojo.style(token, "display", null);
            dojo.style(token, "top", "0px");
            dojo.style(token, "left", "0px");
            dojo.style(token, "position", null);
			dojo.style(token, "transform", null);
        },
		
		
		slideTemporaryObjectAndIncCounter: function( obj_html , obj_parent, from, to, duration, delay ) 
		{
			var obj = dojo.place(obj_html, obj_parent);
			dojo.style(obj, "position", "absolute");
			dojo.style(obj, "left", "0px");
			dojo.style(obj, "top", "0px");
			this.placeOnObject(obj, from);
			this.param.push(to);
			var anim = this.MySlideToObject(obj, to, duration, delay);
			onendF = dojo.hitch( this ,function(){ this.incAndDestroy(obj)});
			dojo.connect(anim, "onEnd", onendF);
			anim.play();
			return anim;			
		},
		
		incAndDestroy : function(node) 
		{				
				dojo.destroy(node);
				target=this.param.shift();
				dojo.byId(target).innerHTML=eval(dojo.byId(target).innerHTML) + 1;
		},
		
		MySlideToObject: function (mobile_obj, destination_obj, duration, delay)
		{
			if (mobile_obj === null)
			{
				console.error("slideToObject: mobile obj is null");
			}
			if (destination_obj === null)
			{
				console.error("slideToObject: target obj is null");
			}
			var tgt = dojo.position(destination_obj);
			var src = dojo.position(mobile_obj);
			if (typeof duration == "undefined")
			{
				duration = 500;
			}
			if (typeof delay == "undefined")
			{
				delay = 0;
			}
			if (this.instantaneousMode)
			{
				delay = Math.min(1, delay);
				duration = Math.min(1, duration);
			}
			var left = dojo.style(mobile_obj, "left");
			var top = dojo.style(mobile_obj, "top");
			left = left + tgt.x - src.x + (tgt.w - src.w) / 2;
			top = top + tgt.y - src.y + (tgt.h - src.h) / 2;
			return dojo.fx.slideTo(
			{
				node: mobile_obj,
				top: top,
				left: left,
				delay: delay,
				duration: duration,
				unit: "px"
			}
			);
		},
		MySlideTemporaryObject: function (obj_html, obj_parent, from, to, duration, delay)
		{
			var obj = dojo.place(obj_html, obj_parent);
			dojo.style(obj, "position", "absolute");
			dojo.style(obj, "left", "0px");
			dojo.style(obj, "top", "0px");
			this.placeOnObject(obj, from);
			var anim = this.MySlideToObject(obj, to, duration, delay);
			onendF = dojo.hitch( this ,function(){ dojo.destroy(obj)});
			dojo.connect(anim, "onEnd", onendF);
			anim.play();
			return anim;
		},
		////////////////////////////////////////////////


        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        
        /* Example:
        
        onMyMethodToCall1: function( evt )
        {
            console.log( 'onMyMethodToCall1' );
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );

            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'myAction' ) )
            {   return; }

            this.ajaxcall( "/dragonkeeper/dragonkeeper/myAction.html", { 
                                                                    lock: true, 
                                                                    myArgument1: arg1, 
                                                                    myArgument2: arg2,
                                                                    ...
                                                                 }, 
                         this, function( result ) {
                            
                            // What to do after the server call if it succeeded
                            // (most of the time: nothing)
                            
                         }, function( is_error) {

                            // What to do after the server call in anyway (success or failure)
                            // (most of the time: nothing)

                         } );        
        },        
        
        */

        pickcard: function( evt )
        {
            // Stop this event propagation
			
            dojo.stopEvent( evt );
			if( ! this.checkAction( 'pickCard' ) )
            {   return; }
			
			dojo.query(".borderpulse").removeClass("borderpulse");

            // Get the cliqued pos and Player field ID
            var cardpicked = evt.currentTarget.id;
			var card_id = cardpicked.split('_')[1];	
			dojo.forEach(this.gameconnections, dojo.disconnect);
			
			this.gameconnections=new Array();
		
            if( this.checkAction( 'pickCard' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/dragonkeeper/dragonkeeper/pickcard.html", {
                    card_id:card_id                    
                }, this, function( result ) {} );
            }        			
        },
		
		pickcardPower: function( evt )
        {
            // Stop this event propagation
			
            dojo.stopEvent( evt );
			if( ! this.checkAction( 'pickcardPower' ) )
            {   return; }
			
			dojo.query(".borderpulse").removeClass("borderpulse");

            // Get the cliqued pos and Player field ID
            var cardpicked = evt.currentTarget.id;
			var card_id = cardpicked.split('_')[1];
			dojo.forEach(this.gameconnections, dojo.disconnect);			
			this.gameconnections=new Array();
		
            if( this.checkAction( 'pickcardPower' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/dragonkeeper/dragonkeeper/pickcardPower.html", {
                    card_id:card_id                    
                }, this, function( result ) {} );
            }            
        },

        donatecard: function( evt )
        {
            // Stop this event propagation
			
            dojo.stopEvent( evt );
			if( ! this.checkAction( 'donateCard' ) )
            {   return; }
			
			dojo.query(".borderpulse").removeClass("borderpulse");

            // Get the cliqued pos and Player field ID
            var cardpicked = evt.currentTarget.id;
			var card_id = cardpicked.split('_')[1];
			
		/*	this.confirmationDialog( _('Are you sure you want to make this?'), dojo.hitch( this, function() {
            this.ajaxcall( '/mygame/mygame/makeThis.html', { lock:true }, this, function( result ) {} );
			} ) ); */
		
			dojo.forEach(this.gameconnections, dojo.disconnect);
			
			this.gameconnections=new Array();
		
            if( this.checkAction( 'donateCard' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/dragonkeeper/dragonkeeper/donatecard.html", {
                    card_id:card_id                    
                }, this, function( result ) {} );
            }            
        },
        
        playPower: function( evt )
        {
			dojo.stopEvent( evt );
			if( ! this.checkAction( 'playPower' ) )
            {  return; }
			
			if( this.checkAction( 'playPower' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/dragonkeeper/dragonkeeper/playPower.html", {
                }, this, function( result ) {} );
            }
			else
			{
				this.showMessage  ( _("This action is no longer possible"), "info")
			}	
        },

        pass: function( evt )
        {
			dojo.stopEvent( evt );
			if( ! this.checkAction( 'pass' ) )
            {  return; }
			
			if( this.checkAction( 'pass' ) )    // Check that this action is possible at this moment
            {            
                this.ajaxcall( "/dragonkeeper/dragonkeeper/pass.html", {
                }, this, function( result ) {} );
            }
			else
			{
				this.showMessage  ( _("This action is no longer possible"), "info")
			}	
        },
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your dragonkeeper.game.php file.
        
        */
        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 

            dojo.subscribe('movedrake', this, "notif_movedrake");
            this.notifqueue.setSynchronous('movedrake', 2000);
            dojo.subscribe('movecard', this, "notif_movecard");
            this.notifqueue.setSynchronous('movecard', 2000);
			dojo.subscribe('playergetgold', this, "notif_playergetgold");
            this.notifqueue.setSynchronous('playergetgold', 2000);
			dojo.subscribe('playerpaysgold', this, "notif_playerpaysgold");
            this.notifqueue.setSynchronous('playerpaysgold', 2000);
			dojo.subscribe('discard', this, "notif_discard");
            this.notifqueue.setSynchronous('discard', 2000);
			dojo.subscribe('levelchange', this, "notif_levelchange");
            this.notifqueue.setSynchronous('levelchange', 8000);
			dojo.subscribe('notif_finalScore', this, "notif_finalScore");
            this.notifqueue.setSynchronous('notif_finalScore', 8000);
            
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
        notif_movedrake : function(notif) {
            // console.log('notif_tokenMoved');
            // console.log(notif);
            var drakepos = notif.args.drakepos;
			
		setTimeout(function() 
			{
			dojo.style ($("drakefoot") ,{ transform: "translate3D(10px, 10px, 10px) rotateZ("+ Math.floor((Math.random() * 180) + 1) +"deg)" });
			} , 1);
        this.slideToObjectAbsolute('drake', drakepos, 0, 0, 1000 , 0 );
        },
        notif_movecard : function(notif) {
            var destination = notif.args.destination;
        this.slideToObjectAbsolute('card_'+notif.args.card_id, 'sky' ,0,0, 1000, 1 , dojo.hitch( this ,function(){ this.slideToObjectAbsolute('card_'+notif.args.card_id, destination,0,0, 1000 )}));
        },
		notif_playergetgold: function( notif )
        {
            console.log( 'notif_playergetgold' );
            console.log( notif );
            this.gamedatas.players[notif.args.player_id]['gold']+=notif.args.amount;
			this.giveGold ( "sky" , "goldcount_p"+notif.args.player_id, notif.args.amount );
        },
		notif_playerpaysgold: function( notif )
        {
            console.log( 'notif_playerpaysgold' );
            console.log( notif );
            this.gamedatas.players[notif.args.player_id]['gold']+=notif.args.amount;
			this.payGold ( "goldcount_p"+notif.args.player_id , "flames_"+notif.args.player_id , notif.args.amount );
        },
		notif_discard : function(notif) {
            var destination = notif.args.destination;
        this.slideToObjectAbsolute('card_'+notif.args.card_id, 'nowhere' ,0,0, 1000, 1 , dojo.hitch( this ,function(){ dojo.destroy('card_'+notif.args.card_id) }));
        },
		notif_levelchange : function(notif) {
            console.log('notif_levelchange');
			console.log( notif );
            //debugger;
            var drakepos = notif.args.drakepos;
			var level = notif.args.level ;
			this.gamedatas.level=level;
        this.slideToObjectAbsolute('drake', drakepos, 0, 0, 1000 , 0 );
		this.slideToObjectAbsolute('table'+level, 'nowhere' ,0,0, 2000, 1 , dojo.hitch( this ,function(){ dojo.destroy('table'+level) }));
        },
		notif_finalScore: function (notif) 
		{
            console.log('**** Notification : finalScore');
            console.log(notif);
			id     = _(notif.args.id);
			title  = _(notif.args.title);
			header = _(notif.args.header);
			footer = _(notif.args.footer);
			closing= _(notif.args.closing);
			
			for (i in this.gamedatas.players )
				{
					this.scoreCtrl[ i ].setValue( notif.args.players[i].score );
				}
			
            this.displayTableWindow( id, title, notif.args.table, header, footer, closing);
        }
		
   });             
});
