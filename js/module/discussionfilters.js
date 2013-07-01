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
        $('.discussion-filter-select-button').click(function() {

            // Toggle class.
            $(this).toggleClass('selected');
            
            // Toggle checkbox.
            var li_id = $(this).attr('id');
            $('#' + li_id + '-input').prop('checked', function(i, val) {
            
                return !val;
            });
        });
    },
    
    configResetClickEvents: function() {
    
        // Support resetting of filter choices when the reset form button is clicked.
        $('.reset-filter-choices').click(function() {
        
            $('input[name="efid[]"]').attr('checked', false);
            $(this).closest('form').submit();
        });
    },
    
    configSubmitClickEvents: function() {
    
        // Support submitting form when submit button is clicked.
        $('.submit-filter-choices').click(function() {
        
            $(this).closest('form').submit();
        });
    },
    
    init: function() {
		
        dmmo.discussionfilters.configToggleClickEvents();
        dmmo.discussionfilters.configResetClickEvents();
        dmmo.discussionfilters.configSubmitClickEvents();
    }
}

/* Make the magic happen. */
$(function() {

    dmmo.discussionfilters.init();
});