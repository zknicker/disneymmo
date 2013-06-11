/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages events triggered on the discussions page.
 *
 *	Zach Knickerbocker
 */

/* Discussion Filters
 * --------------------------------------------------------------------------------- */
dmmo.discussion_events = {

    scrollHidesTopPostReply: function() {
        
        $(window).scroll(function() {

            if($('.triggers-utilitybar-minimize').visible(true)) {

                $('.ui-utilitybar-actions').addClass('minimized');

            } else {

                $('.ui-utilitybar-actions').removeClass('minimized');
            }
        });
    },
    
    init: function() {
		
        dmmo.discussion_events.scrollHidesTopPostReply();
    }
}

/* Make the magic happen. */
$(function() {

    dmmo.discussion_events.init();
});