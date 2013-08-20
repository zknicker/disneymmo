/*
 *    ___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *    |  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *    |__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *    Core DisneyMMO EOS javascript. Global scripts are initialized.
 *  
 *    If you want to include a modulized script, include it with
 *    html script tags and it will initialize itself. In the future,
 *    this file should support loading modules.
 *
 *    Our script suite does not currently use a proper module
 *    pattern (largely because it's completely unnecessary). Maybe
 *    such a pattern will be implemented in the future.
 *
 *    External equirements: Modernizr, jQuery
 *
 *    Zach Knickerbocker
 */

var _window, _document, _body, _htmlBody;
$(function() {

    _window = $(window);
    _document = $(document);
    _body = $("body");
    _htmlBody = $("html, body");
});
 
dmmo = {
    
    game_prefixes: ['cp', 'po', 'ph', 'tt'],
    
    init: function() {

        // Extend Modernizr
        dmmo.modernizr.cssAppearance();
    
        // Initialize global modules.
        dmmo.notifications.init();
        dmmo.tooltips.init();
        dmmo.utilitybar.init();
        dmmo.form.init();
        
        // Do some general maintenance work.
        dmmo.maintenance.centerAvatars();
        dmmo.maintenance.setupFooter();
        dmmo.maintenance.fullSidebarFixer();
        dmmo.maintenance.fauxFileInputEvents();
        
    },
    
    /* Image preloader with callback.
     * ------------------------------------------------------------ */
    loadImage: function(imgElem, callBackFunction) {
    
        imgElem.load(function() {
        
            if(typeof callBackFunction == 'function') {
            
                callBackFunction();
            }
        });
    },
    
    /* Cookie management functions.
     * ------------------------------------------------------------ */
    createCookie: function(name, value, days)
    {
        if (days) {
        
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        }
        else var expires = "";
        _document[0].cookie = name + "=" + value+expires + "; path=/";
    },

    readCookie: function(name)
    {
        var nameEQ = name + "=";
        var ca = _document[0].cookie.split(';');
        for(var i=0; i < ca.length; i++) {
        
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    },

    eraseCookie: function(name) {
    
        dmmo.createCookie(name,"",-1);
    },

    /* Retrieve random game prefix.
     * ------------------------------------------------------------ */
    getRandomGamePrefix: function() {
    
        var length = dmmo.game_prefixes.length;
        return dmmo.game_prefixes[Math.floor(Math.random() * length)];
    }
}

/* Notifications [GLOBAL MODULE]
 * --------------------------------------------------------------------------------- */
dmmo.notifications = {

    container: 0,            // notifications container element.
    currID: 1,               // Incrementing unique ID for notifications.
    
    post: function(message) {
    
        var notificationID = dmmo.notifications.currID++;
        
        // Post notification HTML.
        $(dmmo.notifications.container).prepend(
        
            '<div id="NID' + notificationID + '" class="notification normal">' +
                message + '<a href="javascript: dmmo.notifications.clear(' + notificationID + ');" class="notification-close"></a>' +
            '</div>'
        );
            
        // Animate notification onto screen.
        var notificationHeight = $('#NID' + notificationID).height();
        $('#NID' + notificationID).animate({ top: '-=100', opacity: '1' }, 500, 'easeOutBack');
    
        // Queue a clear event for the notification.
        window.setTimeout('dmmo.notifications.clear(' + notificationID + ')', 3500000);
    },
    
    clear: function(notificationID) {
    
        $('#NID' + notificationID).animate({ top: '+=100', opacity: '0' }, 300, function() {
        
            $(this).remove();
        });
    },

    init: function() {
    
        $('body').prepend(
            
            '<div class="notifications-container"></div>'
        );
        
        dmmo.notifications.container = $('.notifications-container');
    }
}

/* Tooltips [GLOBAL MODULE]
 * --------------------------------------------------------------------------------- */
dmmo.tooltips = {

    OFFSETX: 27,
    OFFSETY: -3,

    createEvents: function() {
    
        $('[data-tooltip]').each(function(i, el) {
        
            var title = $(el).attr('title') ? 'undefined' : '';
            var tooltipMsg = $(el).data('tooltip');
            var tooltip = $('<div class="tooltip">' + tooltipMsg + '</div>');
        
            $(el).mouseover(function(e) {
                 
                // Append the tooltip to the body.
                $(tooltip).hide().appendTo('body').stop(true, true).fadeIn(250);
                
                // Remove the element's title attribute to prevent default browser hover effect.
                $(el).removeAttr('title');
                
                // Position the tooltip.
                $(tooltip).css({
                
                  top: e.pageY + dmmo.tooltips.OFFSETY,
                  left: e.pageX + dmmo.tooltips.OFFSETX
                });
                 
            }).mousemove(function(e) {
             
                // Reposition the tooltip.
                tooltip.css({
                
                  top: e.pageY + dmmo.tooltips.OFFSETY,
                  left: e.pageX + dmmo.tooltips.OFFSETX
                });
                 
            }).mouseout(function() {
         
                // Restore the element's title attribute.
                $(el).attr('title', title);
             
                // Remove the tooltip from the DOM.
                tooltip.stop(true, true).fadeOut(100).remove();
            });
        });
    },

    init: function() {
    
        dmmo.tooltips.createEvents();
    }
}

/* Utility Bar [GLOBAL MODULE]
 * --------------------------------------------------------------------------------- */
dmmo.utilitybar = {

    createFloatEvent: function() {
    
        $('.ui-utilitybar-container').each(function() {
            
            var $ub = $(this);
            var $ub_actions = $ub.children('.ui-utilitybar-actions-container');
        
            // Set the parent height to preserve the page layout on float.
            $ub.css('height', $ub.outerHeight(true));
            
            // Gather trigger - pixel offset to trigger floating.
            var trigger = $ub_actions.offset().top;
        
            // If trigger is exceeded by scroll, update positioning.
            _window.scroll(function() {
            
                if (_window.scrollTop() >= trigger)
                {
                    $ub_actions.css({position:'fixed'})
                     .addClass('floating');
                }
                else
                {
                    $ub_actions.css({position:'static'})
                     .removeClass('floating');
                }
            });
        });
    },
    
    init: function() {      
    
        dmmo.utilitybar.createFloatEvent();
    }
}

/* Form Management [GLOBAL MODULE]
 * --------------------------------------------------------------------------------- */
dmmo.form = {
 
    fauxFileInputEvents: function() // DEPRECATED
    {
        var faux_file_viewers = $('.faux-file-viewer');
        
        faux_file_viewers.children('a').mousemove(function(e)
        {
            e.preventDefault();
            var offset = $(this).offset(); 
            var relX = e.pageX - offset.left;
            var relY = e.pageY - offset.top;
            
            $(this).children('input[type=file]').css({'left' : relX, 'top' : relY});
        });
        
        faux_file_viewers.children('a').children('input[type=file]').change(function()
        {
            var root = $(this).parent().parent();
            
            root.addClass('file-selected');
            root.children('.faux-file-value').html($(this).val());
        });
    },
    
    init: function()
    {
        dmmo.form.fauxFileInputEvents();
    }
 }

/* Maintenance Tasks [GLOBAL MODULE] [UTILITIES - NO INIT]
 * --------------------------------------------------------------------------------- */
dmmo.maintenance = {
 
    centerAvatars: function() {
    
        $('.avatar-large, .poster-avatar, .centered-avatar').each(function() {
    
            $(this).imagesLoaded(function() {

                $img = $(this).children('img');
                $img.css({
                    marginTop: $img.height() / 2 * -1,
                    marginLeft: $img.width() / 2 * -1
                });
            });
        });
    },
    
    setupFooter: function() {
    
        var $footer = $('footer');
        var footerHeight = $footer.outerHeight(true);
        var footerTop = (_window.scrollTop() + _window.height() - footerHeight) + 'px';
        
        if (_body.height() < _window.height()) {
        
            $footer.css({
                position: "absolute",
                top: footerTop
           })
        } else {
           $footer.css({
                position: "static"
           })
        }
    },
    
    /* The sidebar on the full-sidebar css setup has the position fixed property with a large
     * top padding to avoid occluding sidebar contents by the site banner. As such, when the page
     * is scrolled, this top padding is adjusted to avoid display error. */
    fullSidebarFixer: function() {
        
        // Default padding-top on the full-sidebar sidebar.
        var full_sidebar_top = $('.full-sidebar').children('aside').css('paddingTop');
        full_sidebar_top = parseInt(full_sidebar_top, 10);
        
        // After scrolling banner out of view, this is padding-top the sidebar should have.
        var distance_from_top = 26;
        
        _window.scroll(function() {
        
            if(_document.scrollTop() < full_sidebar_top - distance_from_top) {
                var adjusted_padding_top = Math.min(full_sidebar_top, full_sidebar_top - _document.scrollTop());
                $('.full-sidebar.fix-scroll').children('aside').css('paddingTop', adjusted_padding_top);
                
            } else {
            
                $('.full-sidebar.fix-scroll').children('aside').css('paddingTop', distance_from_top);
            }
        });
    },

    fauxFileInputEvents: function() {

        $('.faux-file-input').each(function() {

            var that = $(this);
            $(this).find('input[type=file]').on('change', function() {

                var filename = $(this).val().split('\\').pop();
                that.children('span:first-child').text(filename);
            })
        });
    },
    
    init: function() { }
 }
 
/* Modernizr Extensions
 * --------------------------------------------------------------------------------- */
dmmo.modernizr = {

    cssAppearance: function() {
        if (Modernizr.testProp('webkitAppearance')) {
            $('html').addClass('cssappearance');
        }
    }
}
 
/* Make the magic happen baby. */
$(function() {
    dmmo.init();
});