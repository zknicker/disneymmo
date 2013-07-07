/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages events triggered on the usercp.
 *
 *	Zach Knickerbocker
 */

dmmo.usercp_events = {

    pmFolderChanger: function() {
        
        $('.pm-folder-changer .changer-activate').click(function() {
         
            var $this = $(this).closest('.pm-folder-changer');

            $this.find('.changer-preshow').css('display', 'none');
            $this.find('.changer-controls').css('display', 'block');
         });
    },
    
    init: function() {
		
        dmmo.usercp_events.pmFolderChanger();
    }
}

/* Make the magic happen. */
$(function() {

    dmmo.usercp_events.init();
});