/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages the discussion filters found on the discussion 
 *  selection page.
 *
 *	Zach Knickerbocker
 */

/* Discussion Filters
 * --------------------------------------------------------------------------------- */
dmmo.discussionfilters = {

    configToggleClickEvents: function() {
        
        // Toggle invisible checkboxes and click of filter buttons.
        $('.discussions-filter-block').click(function() {

            // Toggle class.
            $(this).toggleClass('filtered');
            
            // Toggle checkbox.
            var li_id = $(this).attr('id');
            $('#' + li_id + '-input').prop('checked', function(i, val) {
            
                return !val;
            });
        });
    },
    
    configResetClickEvents: function() {
    
        // Support resetting of filter choices when the reset form button is clicked.
        $('.discussions-filter-menu-control button[type="reset"]').click(function() {
        
            $(this).parent().siblings('input').attr('checked', false);
            $(this).closest('.discussions-filter-form').submit();
        });
    },
    
    init: function() {
		
        dmmo.discussionfilters.configToggleClickEvents();
        dmmo.discussionfilters.configResetClickEvents();
    }
}

/* Make the magic happen. */
$(function() {

    dmmo.discussionfilters.init();
});