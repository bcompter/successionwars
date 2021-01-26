
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--

	Nameless Geometry by nodethirtythree + Templated.org
	http://templated.org/ | @templatedorg
	Released under the Creative Commons Attribution 3.0 License.
	
	Note from the author: These templates take quite a bit of time to conceive,
	design, and finally code. So please, support our efforts by respecting our
	license: keep our footer credit links intact so people can find out about us
	and what we do. It's the right thing to do, and we'll love you for it :)
	
-->
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="keywords" content="" />
		<meta name="description" content="" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Succession Wars : ScrapYardArmory.com</title>
		<link href="http://fonts.googleapis.com/css?family=Yanone+Kaffeesatz" rel="stylesheet" type="text/css" />
                <link rel="stylesheet" href="http://24.218.159.241:8888/successionwars/trunk/successionwars/style_ng.css" type="text/css" />
                <script type="text/javascript" src="http://24.218.159.241:8888/successionwars/trunk/successionwars/jquery-1.7.1.min.js"></script>
                <script type="text/javascript" src="http://24.218.159.241:8888/successionwars/trunk/successionwars/jquery-ui-1.8.16.custom.min.js"></script>
	</head>
	<body>
                <div id="statusbar">
    <p class="left">The Succession Wars
 | <a href="http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/game">Dashboard</a></p><p class="right">Logged in as Brian | <a href="http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/auth/logout">Log Out</a></p></div>		<div id="bg">
			<div id="outer">
				<div id="header">
					<div id="logo">
						<h1>
							<a href="#">Succession Wars</a>
						</h1>
					</div>
					
				</div>
				<div id="main">
									
					<div id="content">
						<div class="box1">
                                                    <script type="text/javascript">                                         
    
    // Track the last timestamp
    var lastChatTime;
    var lastMsgTime;
    var lastMapTime;
   
    // testing jquery...
    $(document).ready(function() 
    {   
        // Map functionality
        $(function(){$("#map").draggable();});
        var $loadurl = 'http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/map/load/5';        
        $.post( $loadurl,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#map").html( msgs );
                
                // Push CSS to newly created elements
                $css = $(".territory").attr("css");
                //$(".territory").css( $(".territory").attr("css") );
            }
            );
        
        // Handle Map Clicks        
        $("body").delegate(".territory","click", function(event)
        {
            // Form the link to be used...
            $url = $(this).attr('url');
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );            
        }); 
        
        $("body").delegate(".territory","hover", 
        function(event)
        {  
            if (event.type == 'mouseenter')
                $(this).fadeTo('fast', 1.0);
            else
                $(this).fadeTo('fast', 0.8);
        });
                
        
        // Chat Submit Button
        $("#chat").submit(function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Get data from form
            var $msg = $(this).find('input[name="message"]').val();
            var $url = $(this).attr('action');
            
            // Send to post
            $.post( $url, {msg: $msg} );
                
            // Clear text from chat input
            $(this).find('input[name="message"]').val('');
            
            // Regain focus to text input
            $(this).find('input[name="message"]').focus();
        });   
        
        
        // Menu links
        $("body").delegate(".menu","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            $url = $(this).attr('href');
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        }); 
        
        
        // Produce links requiring a single text box input
        $("body").delegate(".textinput", "click", function(event){
           
           // Stop default operation
           event.preventDefault();
           
           // Form url
           var $url = $("input:text").attr("url");
           var $offer = $("input:text").val();
           $url = $url + $offer;
           
           // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
           
           
        });
        
        // Produce manufacturing center links
        $("body").delegate(".mc","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            var $url = $("#territory").val();
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        }); 
        
        // Produce combatunit links
        $("body").delegate(".cu","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            var $url = $("#combatunit").val();
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        });
        
        // Death commando link
        $("body").delegate(".dc","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            var $url = $("#option").val();
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        });
        
        // Handles clicks requiring two combobox options
        $("body").delegate(".doubleoption","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            var $url = $("#option").val();
            $url = $url + $("#option2").val();
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        });
        
        // Handles clicks requiring one combobox and one textfield
        $("body").delegate(".combotext","click", function(event)
        {
            // Stop default operation
            event.preventDefault();

            // Form the link to be used...
            var $url = $("#option").val();

            // Get text field value
            var $text = $("input:text").val();

            // Send to server, handle xml response    
            $.post( $url, {data:$text},
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );

        });
        
        // Produce jumpship links
        $("body").delegate(".js","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Form the link to be used...
            var $url = $("#jumpship").val();
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Set info content to the server response
                var msgs = $("info",xml).html();
                $("#info").html( msgs );
            }
            );
            
        });
        
        // Done link
        $("body").delegate(".done","click", function(event)
        {
            // Stop default operation
            event.preventDefault();
            
            // Hide done link
            $("#done").hide();
            
            // Form the link to be used...
            $url = $(this).attr('href');
            
            // Send to server, handle xml response    
            $.post( $url,
            function(xml)
            {               
                // Display errors if present...
                var msgs = $("info",xml).html();
                
                $("#info").html( msgs );
            }
            );
            
        });
        
    });
    
    
    
    
    $(document).ready(function() 
    {       
        // Hide the done div on load just in case
        $("#done").hide();
        
        // Load the last few chat messages
        var $loadurl = 'http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/chat/load_chat/5';        
        $.post( $loadurl,
            function(xml)
            {   // On success, do something with the data
                
                // Grab the last time
                var time = $("chattime",xml).text();
                lastChatTime = time;
                time = $("msgtime",xml).text();
                lastMsgTime = time;
                time = $("maptime",xml).text();
                    
                // Append messages to the div
                $("#chatdiv").append( 'loading ...<br />' );
                var msgs = $("chats",xml).html();
                $("#chatdiv").append( msgs );
                
                msgs = $("messages",xml).html();
                $("#gamemsgs").append( msgs );
                
                // Set scroll bars
                var chatdivscroll = document.getElementById("chatdiv");
                chatdivscroll.scrollTop = chatdivscroll.scrollHeight;
                
                // Set scroll bars
                var msgdivscroll = document.getElementById("gamemsgs");
                msgdivscroll.scrollTop = msgdivscroll.scrollHeight;
                
                // Execute update loop after loading is complete
                update();
            }
        );
    });
    
    // Check back with the server for new messages every so often
    function update()
    {
        var $updateurl = 'http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/sw/update/5';        
        $.post( $updateurl, {chattime: lastChatTime, msgtime: lastMsgTime, maptime: lastMapTime},
            function(xml)
            {                    
                // On success, do something with the data
                // Grab the last time
                var time = $("chattime",xml).text();
                lastChatTime = time;
                time = $("msgtime",xml).text();
                lastMsgTime = time;
                time = $("maptime",xml).text();
                if (time != "")
                {
                    lastMapTime = time;
                }
                // Append chat messages to the div
                var doChatScroll = true;
                var chatdivscroll = document.getElementById("chatdiv");
                var doMsgScroll = true;
                var msgdivscroll = document.getElementById("gamemsgs");
             
                if ( chatdivscroll.scrollHeight - chatdivscroll.scrollTop > 150)
                    doChatScroll = false;
                if ( msgdivscroll.scrollHeight - msgdivscroll.scrollTop > 150)
                    doMsgScroll = false;
             
                var msgs = $("chats",xml).html();
                $("#chatdiv").append( msgs );
                
                var gamemsgs = $("messages",xml).html();
                $("#gamemsgs").append( gamemsgs );
                
                var maps = $("maps",xml).text();
                maps = jQuery.parseJSON(maps);
                for (m in maps)
                {
                    // add in html characters
                    maps[m].html = maps[m].html.replace(/-br-/g, "<br />");
                    
                    maps[m].html = maps[m].html.replace("*p*", "<p>");      
                    maps[m].html = maps[m].html.replace("*zp*", "</p>");
                    
                    // Display
                    var id = "#"+maps[m].id;
                    $(id).html(maps[m].html);
                    $(id).css("background-color",maps[m].css);
                    $(id).effect("highlight", {}, 2500);
                }
                
                
                // Set scroll bars
                // Check to see if the user is viewing another part of the chat
                if ( doChatScroll )
                    chatdivscroll.scrollTop = chatdivscroll.scrollHeight;
                if ( doMsgScroll )
                    msgdivscroll.scrollTop = msgdivscroll.scrollHeight;
                
                // Set data as required
                if ( $("year",xml).text() != $("#year").text() )
                    $("#year").text( $("year",xml).text() );
                
                if ( $("turn",xml).text() != $("#turn").text() )
                    $("#turn").text( $("turn",xml).text() );
                
                if ( $("current_player",xml).text() != $("#current_player").text() )
                    $("#current_player").text( $("current_player",xml).text() );
                
                if ( $("phase",xml).text() != $("#phase").text() )
                    $("#phase").text( $("phase",xml).text() );
                
                if ( $("cbills",xml).text() != $("#cbills").text() )
                    $("#cbills").text( $("cbills",xml).text() );
                
                if ( $("tech",xml).text() != $("#tech").text() )
                    $("#tech").text( $("tech",xml).text() );
                
                if ( $("enabledone",xml).text() == "true" )
                {
                    $("#done").show();
                }
                else if ( $("enabledone",xml).text() == 'false' && $("#done").is(':visible') )
                {
                    $("#done").hide();
                }

            }
        );
        //setTimeout("update()", 5000);

    };
    
    var oldscale = 50;
    var newscale = 50;
    
    // Map Zoom Code
    $(document).ready(function(){
    
        $(".zoom").click(function(event){
            event.preventDefault();
            
            var zoomtype = 0;
            if ($(this).attr("name") == "zoomin")
            {
                $("#info").text("Zoom IN!");
                zoomtype = 1;
            }
            else if ($(this).attr("name") == "zoomout")
            {
                $("#info").text("Zoom Out!");
                zoomtype = -1;
            }

            $("#info").effect("highlight");
            
            newscale = oldscale + (zoomtype * 50);
            
            $(".territory").each(function(){
                
                // Old values
                var oldheight = $(this).height() / oldscale;
                var oldwidth = $(this).width() / oldscale;
                var oldtop = $(this).position().top / oldscale;
                var oldleft = $(this).position().left / oldscale;
                
                // New values
                var newheight = (oldheight) * newscale;
                var newwidth = (oldwidth) * newscale;
                var newtop = (oldtop) * newscale;
                var newleft = (oldleft) * newscale;
                
                // Animate
                $(this).animate({height:newheight, width:newwidth, top:newtop, left:newleft}, 500)
                
            });
            oldscale = newscale;
            
        });

    
    });

</script>

<div class="box1">
    <div id="playerinfo">
        <h3>DEFAULT</h1>
        <table>
            <tr><td>CBills: <div id="cbills" class="inline"> 110</div></td></tr>
            <tr><td>Technology: <div id="tech" class="inline">-5</div></td></tr>
        </table>
        <div id="done"><a href="http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/sw/done/5" class="done">Done</a></div>
    </div>

    <div id="gamestatus">
        <h3>Brothers Battle</h1>
        <table>
            <tr><td>Year: <div id="year" class="inline">3027</div></td></tr>
            <tr><td>Turn: <div id="turn" class="inline"> 12</div></td></tr>
            <tr><td>Current Player: <div id="current_player" class="inline">
                Steiner</div></td></tr>
            <tr><td>Phase: <div id="phase" class="inline"> Movement</div></td></tr>

        </table>
    </div>
</div>
    
<ul class="menub">
    
    <li class="top">
        <a href="#" class="top_link zoom" name="zoomin"><span>Zoom In</span></a></li>
    <li class="top">
        <a href="#" class="top_link zoom" name="zoomout"><span>Zoom Out</span></a></li>
</ul>
    <div class="box1 fluid">
    <div id="mapcontainer">
        <div id="map">

        </div>
    </div>

    <div id="info"></div>
</div>

<div id="chatdiv"></div><div id="gamemsgs"></div>
<p>
<form action="http://24.218.159.241:8888/successionwars/trunk/successionwars/index.php/chat/ajax_input/5" method="post" accept-charset="utf-8" id="chat"><input type="text" name="message" value=""  /><input type="submit" name="chat" value="Send"  /></form></p>


 
						</div>
						
						
						<br class="clear" />
					</div>
					<br class="clear" />
				</div>
				<div id="footer">
					<br class="clear" />
				</div>
			</div>
			<div id="copyright">
                            <p>MechWarrior, BattleMech, Mech and AeroTech are registered trademarks of Topps, Inc. All Rights Reserved. </p>
                            <p>ScrapYardArmory.com | Design: <a href="http://templated.org/free-css-templates/namelessgeometry/">Nameless Geometry</a> by <a href="http://nodethirtythree.com">nodethirtythree</a> + <a href="http://templated.org/">Templated.org</a> | Sponsor: <a href="http://www.4templates.com/">Business Website Templates</a></p>
                            <p>Page rendered in 0.0304 seconds</p>
                        </div>
		</div>
	</body></html>