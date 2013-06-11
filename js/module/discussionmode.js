/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages the discussion mode toggle found in discussions.
 *
 *	Zach Knickerbocker
 */
 
dmmo.discussionmode = {

    setMode: function(type) {            // If invalid type supplied, just choose compact.
        if (type != 'compact' && type != 'detailed') {                    
            type = 'compact';        
        } 
        
        $('.posts-list').removeClass('compact detailed').addClass(type);
        dmmo.eraseCookie('dmmo-discussion-mode');
        dmmo.createCookie('dmmo-discussion-mode', type);
        
    },
    
    configClickEvents: function() {
        $('.discussions-mode-toggle .compact-mode').click(function() {
            dmmo.discussionmode.setMode('compact');
        });
        
        $('.discussions-mode-toggle .detailed-mode').click(function() {
            dmmo.discussionmode.setMode('detailed');
        });
    },
    
    init: function() {
    
    // Set the mode to the user's previously selected choice.
    var mode = dmmo.readCookie('dmmo-discussion-mode');
    dmmo.discussionmode.setMode(mode);
    // Initialize click events.
    dmmo.discussionmode.configClickEvents();
    }
}

/* Make the magic happen. */
$(function() {
    dmmo.discussionmode.init();})
;