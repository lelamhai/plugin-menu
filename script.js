jQuery(document).ready(function ($) { 
    var urlCurrent  = window.location.href;
    $('.menu-item').click(function () {
        if ($(this).is(':checked')) {
            var menu_id     = $(this).data('idmenu');
            var slug_role   = $(this).data('slugrole');
            // check insert item
            $.ajax({
                method: "POST",
                url: urlCurrent,
                data: {
                    action: "create",
                    slug_role : slug_role,
                    menu_id: menu_id
                }, 
                success: function(result) {
                    window.location.href = window.location.href;
                },
                error: function(result) {
                    console.log(result);
                }
            });
        }else {
            var menu_id     = $(this).data('idmenu');
            var slug_role   = $(this).data('slugrole');
            // uncheck delete item
            $.ajax({
                method: "POST",
                url: urlCurrent,
                data: {
                    action: "delete",
                    slug_role : slug_role,
                    menu_id: menu_id
                }, 
                success: function(result) {
                    window.location.href = window.location.href;
                },
                error: function(result) {
                    console.log(result);
                }
            });
        }
    });
});