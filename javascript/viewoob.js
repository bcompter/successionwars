// Document load
$(document).ready(function() 
{
    $(".combat_unit_type").change(function(){
        if ($(".combat_unit_type").val() === "Conventional")
        {
            $(".strength").val(3);
        }
        else if ($(".combat_unit_type").val() === "Elemental")
        {
            $(".strength").val(2);
        }
    });
});
