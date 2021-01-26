// Track the last timestamp
var lastChatTime;
var lastMsgTime;
var lastMapTime;

// Update timeout
var ticks_since_last = 0;
var update_timeout = 15;

// Map zoom
var zoomLevel = 1.0;
var minZoom = 0.5;
var maxZoom = 2.0;
var zoomLock = false;
    
// Document load
$(document).ready(function() 
{
    // Map functionality
    $(function(){$("#map").draggable();});

    $.post( $loadMapUrl, function(xml)
    {               
        // Set info content to the server response
        var msgs = $("info",xml).html();
        $("#map").html( msgs );

        // Push CSS to newly created elements
        $css = $(".territory").attr("css");

    });

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

        reset_update_timer();    
    }); 
    
    // Handle zoom clicks
    $("body").delegate("#zoom_in","click", function(event)
    {
        event.preventDefault();
        if (zoomLock)
            return;
        zoomLock = true;
        zoom_in();
    });
    $("body").delegate("#zoom_out","click", function(event)
    {
        event.preventDefault();
        if (zoomLock)
            return;
        zoomLock = true;
        zoom_out();
    });

    $("body").on("mouseenter mouseleave", ".territory", 
    function(event)
    {  
        if (event.type === 'mouseenter')
            $(this).fadeTo('fast', 1.0);
        else
            $(this).fadeTo('fast', 0.8);
    });

    // Highlight territories on jumpship view hover over name
    $("body").on("mouseenter mouseleave", ".hoverlink", 
    function(event){
        if (event.type === 'mouseenter')
        {
            $($(this).attr("hoverid")).fadeTo('fast', 1.0);
        }
        else
        {
            $($(this).attr("hoverid")).fadeTo('fast', 0.8);
        }
    });

    // Public Chat Submit Button
    $("#public_chat").submit(function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Get data from form
        var $msg = $(this).find('input[name="public_message"]').val();
        var $url = $(this).attr('action');

        // Send to post
        $.post( $url, {msg: $msg} );

        // Clear text from chat input
        $(this).find('input[name="public_message"]').val('');

        // Regain focus to text input
        $(this).find('input[name="public_message"]').focus();

        reset_update_timer();

    });  
    
    // Private Chat Submit Button
    $("#private_chat").submit(function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Get data from form
        var $msg = $(this).find('input[name="private_message"]').val();
        var $url = $(this).attr('action');
        var $sendTo = $("#option").val();

        // Send to post
        $.post( $url, {msg: $msg, sendTo: $sendTo} );

        // Clear text from chat input
        $(this).find('input[name="private_message"]').val('');

        // Regain focus to text input
        $(this).find('input[name="private_message"]').focus();

        reset_update_timer();

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
            $(".sortable").tablesorter();
        }
        );

        reset_update_timer();
    }); 

    // Kill Links
    $("body").delegate(".kill","click", function(event)
    {
        // Stop default operation
        event.preventDefault();
        
        // Hide all other kill links on the page
        $(".kill").fadeOut("fast", function(){});

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

        reset_update_timer();
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

       reset_update_timer(); 
    });

    // Produce links requiring a single text box input
    // Jumpship name links
    $("body").delegate(".textin", "click", function(event){

       // Stop default operation
       event.preventDefault();

       // Form url
       var $url = $("input:text").attr("url");
       var $input = $("input:text").val();

       // Send to server, handle xml response    
        $.post( $url,{input:$input},
        function(xml)
        {               
            // Set info content to the server response
            var msgs = $("info",xml).html();
            $("#info").html( msgs );
        }
        );

       reset_update_timer(); 
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
        reset_update_timer(); 
    }); 

    // Produce combatunit links
    $("body").delegate(".cu","click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        var $url = $("#combatunit").val();

        if ($url == '0')
            $url = $("#conventional").val();

        // Send to server, handle xml response    
        $.post( $url,
        function(xml)
        {               
            // Set info content to the server response
            var msgs = $("info",xml).html();
            $("#info").html( msgs );
        });
        reset_update_timer(); 
    });

    // Death commando link, one combobox
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
        });
        reset_update_timer(); 
    });
    
    // One Combobox link, the second combobox
    $("body").delegate(".dc2","click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        var $url = $("#option2").val();

        // Send to server, handle xml response    
        $.post( $url,
        function(xml)
        {               
            // Set info content to the server response
            var msgs = $("info",xml).html();
            $("#info").html( msgs );
        });
        reset_update_timer(); 
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
        reset_update_timer(); 
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
        reset_update_timer();
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
       reset_update_timer();  
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

        reset_update_timer(); 
    });

});  // end of document ready

$(document).ready(function() 
{       
    // Hide the done div on load just in case
    $("#done").hide();

    // Load the last few chat messages

    $.post( $loadChatUrl,
        function(xml)
        {   // On success, do something with the data

            // Grab the last time
            var time = $("chattime",xml).text();
            lastChatTime = time;
            time = $("msgtime",xml).text();
            lastMsgTime = time;
            time = $("maptime",xml).text();
            lastMapTime = time;

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

/**
 * Zoom in on the map
 */
function zoom_in()
{
    if (zoomLevel < maxZoom)
        zoomLevel += 0.1;
    else
    {
        zoomLock = false;
        return;
    }
    var translationX = 30 * (2-zoomLevel);
    var translationY = 10 * (2-zoomLevel);
    $("#map").animate({'zoom': zoomLevel, left:"-="+translationX, top:"-="+translationY}, 100, function () {zoomLock = false;});
}  // end zoom_in

/**
 * Zoom out on the map
 */
function zoom_out()
{
    if (zoomLevel > minZoom)
        zoomLevel -= 0.1;
    else
    {
        zoomLock = false;
        return;
    }
    var translationX = 30 * (2-zoomLevel);
    var translationY = 10 * (2-zoomLevel);
    $("#map").animate({'zoom': zoomLevel, left:"+="+translationX, top:"+="+translationY}, 100, function () {zoomLock = false;});
}  // end zoom_out

/**
 * Reset the update timeout to 0
 */
function reset_update_timer()
{
    ticks_since_last = 0;
    $("#update").text("Running");
}

// Check back with the server for new messages every so often
function update()
{
    if (ticks_since_last < update_timeout)
    {
        ticks_since_last++;  

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
            if (maps !== "")
            {
                maps = JSON.parse(maps);
                for (m in maps)
                {
                    // add in html characters
                    maps[m].html = maps[m].html.replace(/-br-/g, "<br />");

                    maps[m].html = maps[m].html.replace("*p*", "<p>");      
                    maps[m].html = maps[m].html.replace("*zp*", "</p>");

                    maps[m].html = maps[m].html.replace("*cap*", '<img src="' + $capitalImg + '">');
                    maps[m].html = maps[m].html.replace("*reg*", '<img src="' + $regionalImg + '">');
                    maps[m].html = maps[m].html.replace("*fac*", '<img src="' + $factoryImg + '">');
                    maps[m].html = maps[m].html.replace("*facdmg*", '<img src="' + $factoryDmgImg + '">');
                    maps[m].html = maps[m].html.replace(/-span-/g, "<span class='bolder'>");
                    maps[m].html = maps[m].html.replace(/-endspan-/g, "</span>");

                    // Display
                    var id = "#"+maps[m].id;
                    $(id).html(maps[m].html);
                    $(id).css("background-color",maps[m].css);
                    $(id).effect("highlight", {}, 2500);
                }
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

            $("#waitingon").text( $("waitingon", xml).text() );

            if ( $("enabledone",xml).text() == "true" )
            {
                $("#done").show();
                $("#done a").text( $("donetext", xml).text() );
            }
            else if ( $("enabledone",xml).text() == 'false' && $("#done").is(':visible') )
            {
                $("#done").hide();
            }
            
            if ($("enableundo",xml).text() == "true")
            {
                $('#undo_move').show();
            }
            else
            {
                $('#undo_move').hide();
            }
            
            if ( $("timer",xml).text() != $("#timer").text() )
                $("#timer").text( $("timer",xml).text() );
        }
    );
    }
    else
    {
        $("#update").text("Stopped");
    }

    setTimeout("update()", 5000);

};