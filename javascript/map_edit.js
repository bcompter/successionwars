// Document load
$(document).ready(function() 
{
    // Map functionality
    $(function(){$("#map").draggable();});

    // Menu links
    $("body").delegate(".menu", "click", function(event)
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
            msgs = msgs.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            $("#info").html( msgs );
        }
        );  // end post

    });  // end menu links
    
    // Add Region links
    $("body").delegate(".add_region", "click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        $url = $(this).attr('href');

        // Collect the data
        var iname = $("input[name$='name']").val();
        var iresource = $("input[name$='resource']").val();
        var iheight = $("input[name$='height']").val();
        var iwidth = $("input[name$='width']").val();
        var itype = $("input[name=type]:checked").val();
        
        // Send to server, handle xml response    
        $.post( $url, {name:iname, resource:iresource, height:iheight, width:iwidth, type:itype},
        function(xml)
        {               
            // On success, add the new region to the map
            var msgs = $("info", xml).html();
            msgs = msgs.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            $("#info").html( msgs );
            
            var map = $("map", xml).html();
            map = map.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            if (map !== null)
                $("#map").append(map);
        }
        );  // end post

    });  // end add region
    
    // Edit Region links
    $("body").delegate(".edit_region", "click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        $url = $(this).attr('href');
        $id = $(this).attr('tid');
        $id = FormatId($id);

        // Collect the data
        var iname = $("input[name$='name']").val();
        var iresource = $("input[name$='resource']").val();
        var itype = $("input[name=type]:checked").val();
        
        // Send to server, handle xml response    
        $.post( $url, {name:iname, resource:iresource, type:itype},
        function(xml)
        {               
            // On success, remove old region and add the new region to the map
            var msgs = $("info", xml).html();
            msgs = msgs.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            $("#info").html( msgs );
            
            var map = $("map", xml).html();
            map = map.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            if (map !== null)
            {
                $("#" + $id).remove();
                $("#map").append(map);
            }
        }
        );  // end post

    });  // end edit region
    
    // Delete Region links
    $("body").delegate(".delete_region", "click", function(event)
    {
        // Stop default operation
        event.preventDefault();

        // Form the link to be used...
        $url = $(this).attr('href');
        $id = $(this).attr('tid');
        $id = FormatId($id);
        
        // Send to server, handle xml response    
        $.post( $url,
        function(xml)
        {               
            // On success, show our message
            var msgs = $("info", xml).html();
            msgs = msgs.replace("<!--[CDATA[", "").replace("]]&gt;", "");
            $("#info").html( msgs );
            
            // Delete the territory if the action was confirmed
             var confirm = $("confirmed", xml).html();
             if (confirm === "YES")
                $("#" + $id).remove();
        }
        );  // end post

    });  // end delete region
    
    // Map clicks
    $("body").delegate(".territory", "click", function(event)
    {
        // Form the link to be used...
        $url = $(this).attr('url');

        // Send to server, handle xml response    
        $.post( $url,
        function(xml)
        {               
            // Set info content to the server response
            var msgs = $("info", xml).html();
            $("#info").html( msgs );
        }
        );   
   
    });  // end map clicks
    
    // Edit location buttons
    $("body").delegate(".edit_location", "click", function(event)
    {
        // Stop default operation
        event.preventDefault();
        
        // Form the link to be used...
        $url = $(this).attr('href');
        $type = $(this).attr('name');
        $id = $(this).attr('tid');
        $id = FormatId($id);

        $top = $("#" + $id).position().top;
        $left = $("#" + $id).position().left;
        $height = $("#" + $id).height();
        $width = $("#" + $id).width();
        
        $modify_position = true;
        if ($type == "up")
        {
            $top = $top - 50;
        }
        else if ($type == "down")
        {
            $top = $top + 50;
        }
        else if ($type == "left")
        {
            $left = $left - 50;
        }
        else if ($type == "right")
        {
            $left = $left + 50;
        }
        else if ($type == "plus_h")
        {
            $height += 50;
            $modify_position = false;
        }
        else if ($type == "minus_h" && $height >= 100)
        {
            $height -= 50;
            $modify_position = false;
        }
        else if ($type == "plus_w")
        {
            $width += 50;
            $modify_position = false;
        }
        else if ($type == "minus_w" && $width >= 100)
        {
            $width -= 50;
            $modify_position = false;
        }
        else
        {
            // I have no clue what is going on, better fail out
            return;
        }
        
        // Send to server, handle xml response
        if ($modify_position)
            $url += $top + "/" + $left;
        else
            $url += $height + "/" + $width;
        $.post( $url,
        function(xml)
        {
            var success = $("success", xml).html();
            if (success === "YES")
            {
                // Move the territory to the requested location on success
                $("#" + $id).animate({top:$top, left:$left, height:$height, width:$width}, 300);
            } 
        });  // end post
   
    });  // end edit_locations
    
});  // end document ready

/**
 * Format a territory name to a compatible DOM id
 * Strip out spaces and periods
 */
function FormatId($name)
{    
    $retval = $name.replace(/ /g, "");
    $retval = $retval.replace(/\./g, "");
    return $retval;
}  // end FormatId