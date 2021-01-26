// Track the last timestamp
var lastChatTime;

// Update timeout
var ticks_since_last = 0;
var update_timeout = 150;

// Document load
$(document).ready(function() 
{
   
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


});  // end of document ready

$(document).ready(function() 
{       
    // Load the last few chat messages
    $.post( $loadChatUrl,
        function(xml)
        {   // On success, do something with the data

            // Grab the last time
            var time = $("chattime", xml).text();
            lastChatTime = time;

            // Append messages to the div
            var msgs = $("chats", xml).html();
            $("#globalchatdiv").append( msgs );
            
            var users = $("users", xml).html();
            $("#globalwhodiv").html( users );

            // Set scroll bars
            var chatdivscroll = document.getElementById("globalchatdiv");
            chatdivscroll.scrollTop = chatdivscroll.scrollHeight;

            // Execute update loop after loading is complete
            update();
        }
    );
});

/**
 * Reset the update timeout to 0
 */
function reset_update_timer()
{
    ticks_since_last = 0;
    $("#update").text("Running");
}

/**
 * Check back with the server for new messages every so often
 */ 
function update()
{
    if (ticks_since_last < update_timeout)
    {
        ticks_since_last++;  

    $.post( $updateUrl, {chattime: lastChatTime},
        function(xml)
        {                    
            // On success, do something with the data
            // Grab the last time
            var time = $("time",xml).text();
            lastChatTime = time;

            // Append chat messages to the div
            var doChatScroll = true;
            var chatdivscroll = document.getElementById("globalchatdiv");

            if ( chatdivscroll.scrollHeight - chatdivscroll.scrollTop > 150)
                doChatScroll = false;

            var msgs = $("chats",xml).html();
            $("#globalchatdiv").append( msgs );

            var users = $("users", xml).html();
            $("#globalwhodiv").html( users );

            chatdivscroll.scrollTop = chatdivscroll.scrollHeight;
        }
    );
    }
    else
    {
        $("#update").text("Stopped");
    }

    setTimeout("update()", 5000);

};  // end update