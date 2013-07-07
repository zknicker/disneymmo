/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Core DisneyMMO javascript initialization.  Initializes all global
 *	services managed by javascript.
 *
 *  Requires: Modernizr, jQuery
 *
 *	Xiris
 */

var _window, _document, _body, _htmlBody, dmmo;

$(document).ready(function() {

    _window = $(window);
    _document = $(document);
    _body = $("body");
    _htmlBody = $("html, body");
});
 
 dmmo = {
    
    game_prefixes: ['cp', 'po', 'ph', 'tt'],
    
	/*
	 * Initialize all DisneyMMO objects.
	 */
	initialize: function() {
    
        // Initialize components.
		dmmo.user_menu.initialize();
		dmmo.poll.initialize();
		dmmo.notifications.initialize();
		dmmo.tooltips.initialize();
		dmmo.forums.initialize();
        dmmo.utilities.initialize();
		dmmo.cleanup.initialize();
	},
	
	/*
	 * Load images with optional callback function.
	 */
	loadImage: function(imgElem, callBackFunction) {
	
		imgElem.load(function() {
		
			if(typeof callBackFunction == 'function') {
			
				callBackFunction();
			
			}
		});
	},
    
    /*
	 * Create cookie (courtesy: http://www.quirksmode.org/js/cookies.html).
	 */
    createCookie: function(name, value, days)
    {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    },

    /*
	 * Read cookie (courtesy: http://www.quirksmode.org/js/cookies.html).
	 */
    readCookie: function(name)
    {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    },

    /*
	 * Erase cookie (courtesy: http://www.quirksmode.org/js/cookies.html).
	 */
    eraseCookie: function(name)
    {
        dmmo.createCookie(name,"",-1);
    },

    /*
	 * Returns a random game prefix.
	 */
    getRandomGamePrefix: function()
    {
        var length = dmmo.game_prefixes.length;
        return dmmo.game_prefixes[Math.floor(Math.random() * length)];
    }
}

dmmo.utilities = {
 
    initialize: function()
    {
        dmmo.utilities.fileInputEvents();
    },
 
    fileInputEvents: function()
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
    }
 }

/*
* ==============================================================================
* User Menu
* ==============================================================================
* 
* Manages the user menu activated upon hovering over a user's
* profile in the top right hand corner of the banner.
*/
dmmo.user_menu = {

	_container: "#global-profile",
	_profile: "#user-display",
	_menu: "#user-menu",
	_closeTimeout: 100,
	_menuActive: false,
	
	
	profileEvent: function()
    {
		$(this._profile).hover(function()
        {
			$(dmmo.user_menu._container).addClass("active");
		});
	},
	
	menuEvent: function() 
    {
		$(this._container + "> *").hover(function()
        {
			dmmo.user_menu.active = true;
        }, 
        function() // hover out
        {
			dmmo.user_menu.active = false;
			window.setTimeout("dmmo.user_menu.closeMenu()", dmmo.user_menu._closeTimeout);
		});
	
	},
	
	closeMenu: function()
    {
		if(!this.active)
        {
			$(dmmo.user_menu._container).removeClass("active");
		}
	},
	
	initialize: function()
    {
		this.profileEvent();
		this.menuEvent();
	}
};

/*
* ==============================================================================
* Latest poll
* ==============================================================================
* 
* Manages the poll module on the homepage cms.
*/
 dmmo.poll = {
  
	vote: function(elem) {
	
        // Prevent more clicks.
        $('#poll-choices').find('li').unbind('click').css({ 'cursor' : 'auto' });
    
        update = $('#latest-poll').find('input[name="update"]').val();
        topic_id = $('#latest-poll').find('input[name="topic_id"]').val();
        forum_id = $('#latest-poll').find('input[name="forum_id"]').val();
        creation_time = $('#latest-poll').find('input[name="creation_time"]').val();
        form_token = $('#latest-poll').find('input[name="form_token"]').val();
        cur_voted_id = $('#latest-poll').find('input[name="cur_voted_id"]').val();
        vote_id = $(elem).data('option-id');
    
        $.ajax({
            type: 'POST',
            url: 'php/eos_poll.ajax.php',
            data: {
                
                update          : update,
                topic_id        : topic_id,
                forum_id        : forum_id,
                creation_time   : creation_time,
                form_token      : form_token,
                cur_voted_id    : cur_voted_id,
                vote_id         : [ vote_id ]
            
            },
            timeout: 5000,
            success: function(response) {
                
                if(response == 'success') {
                
                    dmmo.notifications.post("Your vote has been submitted.<br>Thanks for participating!");
                    
                    // Maintenance.
                    dmmo.poll.setVote(vote_id);
                    dmmo.poll.sabotage();
                    
                } else {
                
                    dmmo.notifications.post("ERROR: " + response);
                
                }
                
            },
            error: function(jqXHR, textStatus, errorThrown) {
            
                dmmo.notifications.post("Error submitting vote. Please try again later.");

            }
        });	
	},
	
    setVote: function(option_id) {
    
        $('.voted').removeClass('voted');
        $('#latest-poll').find('[data-option-id="' + option_id + '"]').addClass('voted');
    
    },
    
    sabotage: function() {
    
        $('#poll-choices').find('.option-bobble').animate({ 'width' : '12px', 'opacity' : 0 }, 800, 'easeOutBack', function() {
        
            $('#poll-choices').removeClass('can-vote');
        
        });
        
        $('form input').remove();
    
    },
    
	initialize: function() {
	
		$('#poll-choices.can-vote').children('li').each(function() {
		
			$(this).click(function() {
                
				dmmo.poll.vote(this);
			
			});
		
		});
	
	}
 
 }

/*
* ==============================================================================
* Articles
* ==============================================================================
* 
* Functions for controling and managing the articles stream on
* the homepage.
*/
dmmo.articles = {

    more_news_presses: 0, // Don't let the user request more news too many times.

    retrieve: function(more_qty) {
    
        // Add loader image so the user knows what's up.
        $('#more-news-button').append('<div class="loader"></div>');
    
        // Set variables for the AJAX call.
        begin = $('#news-stream article').length + 1;
        qty = more_qty;
    
        // Go on a mission.
        $.ajax({
            type: 'POST',
            url: 'php/eos_articles.ajax.php?style=4',
            data: {
                
                begin   : begin,
                qty     : qty
            },
            timeout: 5000,
            
            // Success! Add the news and clean up.
            success: function(articles_html) {
                
                $(articles_html).each(function() {
                
                    // Fade in each article.
                    var elem = $(this).hide();
                    $('#more-news-button').before(elem);
                    elem.fadeIn(1000);
                
                });
                
                $('#more-news-button .loader').remove();
                dmmo.articles.clean();
                
            },
            
            // Failure. Warn the user and clean up.
            error: function(jqXHR, textStatus, errorThrown) {
            
                $('#more-news-button .loader').remove();
                dmmo.notifications.post("Error encountered while retrieving news. Please try again later.");

            }
        });
    
    },
    
    clean: function() {
    
        dmmo.articles.more_news_presses++;
    
        // If the "more news" button has been pressed too many times, disable it.
        if(dmmo.articles.more_news_presses >= 4) {
        
            $('#more-news-button').addClass('disabled').click(function(event) {
            
                event.preventDefault();
            
            });
        
        }
    
    }
    
}
 
/*
* ==============================================================================
* Tooltips
* ==============================================================================
* 
* Spawns tooltips where requested. Requests made via the
* "tooltip" class. Data for tooltip provided by the
* data-tooltip attribute on the element.
*/
dmmo.tooltips = {

	OFFSETX: 27,
	OFFSETY: -3,

	/*
	 * Create events on applicable elements to spawn tooltips
	 * on mouse hover.
	 */
	createHoverEvents: function() {
	
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

	initialize: function() {
	
		dmmo.tooltips.createHoverEvents();
	
	}

}
 
/*
* ==============================================================================
* Notifications
* ==============================================================================
* 
* Enables the posting of notifications to the screen.
*/
dmmo.notifications = {

	container: 0,			// notifications container element.
	currID: 1,				// Incrementing unique ID for notifications.
	currTimeout: 0,			// Tracker for current notification clear timeout.
	
	/*
	 * Post a notification to the screen.
	 */
	post: function(message) {
	
		// No queueing logic present yet. Don't post unless no notification exists previously.
		if(dmmo.notifications.currTimeout == 0) {
	
			// Retrieve notification ID and increment for next notification.
			var notificationID = dmmo.notifications.currID++;
		
			// Post notification HTML.
			$(dmmo.notifications.container).prepend(
			
				'<div id="NID' + notificationID + '" class="notification normal">' +
					'<div class="notification-content">' + message + '</div>' +
					'<a href="javascript: dmmo.notifications.clear(' + notificationID + ');" class="notification-close"></a>' +
				'</div>'
			
			);
			
			// Animate notification onto screen.
			var notificationHeight = $('#NID' + notificationID).height();
			$('#NID' + notificationID).animate({ top: '-=111', opacity: '1' }, 500, 'easeOutBack');
		
			// Queue a clear event for the notification.
			dmmo.notifications.currTimeout = window.setTimeout('dmmo.notifications.clear(' + notificationID + ')', 3500);
		
		}
	
	},
	
	/*
	 * Clear a notification from the screen.
	 */
	clear: function(notificationID) {
	
		window.clearTimeout(dmmo.notifications.currTimeout);
		dmmo.notifications.currTimeout = 0;
		$('#NID' + notificationID).animate({ opacity: '0' }, 300, function() {
		
			$(this).remove();
	
		});
	},

	initialize: function() {
	
		$('body').prepend(
			
			'<div id="notifications-container"></div>'
	
		);
		
		dmmo.notifications.container = $('#notifications-container');
		
	}

}

/*
* ==============================================================================
* Discussion Filters
* ==============================================================================
* 
* Manages user interaction with the discussion filters on the forums page.
* Initialized per instance of discussion filters menu.
*/
dmmo.discussionFilters = {

    initialize: function(id) {
        
        // Toggle invisible checkboxes and click of filter buttons.
        $('#' + id).find('li').click(function() {

            // Toggle class.
            $(this).toggleClass('filtered');
            
            // Toggle checkbox.
            var sid = $(this).attr('id');
            $('#' + sid + '-input').prop('checked', function(i, val) {
            
                return !val;
            });
        });
        
        // Support resetting of filter choices when the reset form button is clicked.
        $('.discussions-filter-menu-control button[type="reset"]').click(function() {
        
            $(this).parent().siblings('input').attr('checked', false);
            $(this).closest('.discussions-filter-form').submit();
        });
    }
}

/*
* ==============================================================================
* Discussion Mode
* ==============================================================================
* 
* Manages user interaction with the discussion mode toggle.
*/
dmmo.discussionMode = {

    setMode: function(type) {
    
        // Set default for inappropriate type specifications.
        if (type != 'compact' && type != 'detailed') {
        
            type = 'compact';
        }
    
        $('.posts-list').removeClass('compact detailed').addClass(type);
        dmmo.eraseCookie('dmmo-discussion-mode');
        dmmo.createCookie('dmmo-discussion-mode', type); 
    
    },

    initialize: function(id) {
        
        // Set the mode to the user's previously selected choice.
        var mode = dmmo.readCookie('dmmo-discussion-mode');
        dmmo.discussionMode.setMode(mode);
    
        // On click of a mode toggle element, change discussion mode.
        $('#' + id).find('li').click(function() {

            if($(this).hasClass('compact-mode')) {
            
                dmmo.discussionMode.setMode('compact');
            
            } else if($(this).hasClass('detailed-mode')) {

                dmmo.discussionMode.setMode('detailed');
            }
        
        });
    }

}

/*
* ==============================================================================
* Forums
* ==============================================================================
* 
* Forum related functions that require javascript interaction.
*/
dmmo.forums = {

    /*
	 * Enable click functionality for entire width of area behind the page search GUI element on the
	 * forum and discussion selection pages.
	 */
	centerAvatar: function(img) {
    
        // DEPRECATED - REMOVAL IMMINENT
    },

    /*
     * Coordinate the floating of the utilitybar on those pages that contain it.
     */
    utilitybar: function() {
    
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
    
	initialize: function()
    {        
        dmmo.forums.utilitybar();
	}
}

/*
* ==============================================================================
* Cleanup
* ==============================================================================
* 
* Routine cleanups of dmmo for HTML5 support.
*/
dmmo.cleanup = {

	inputs: function() {

		if(!Modernizr.input.placeholder){

			$('input').each(function(){
				
				if($(this).val()=='' && $(this).attr('placeholder')!=''){
				
					$(this).val($(this).attr('placeholder'));
					$(this).focus(function(){
					
						if($(this).val()==$(this).attr('placeholder')) $(this).val('');
					});
					
					$(this).blur(function(){
					
						if($(this).val()=='') $(this).val($(this).attr('placeholder'));
					});
				}
			});
		}
	
	},

	initialize: function() {
	
		dmmo.cleanup.inputs();
	
	}

}

/*
* ==============================================================================
* Discussion Editor
* ==============================================================================
* 
* Supports creation of a discussion. Multiple HTML elements are required to
* exist.
*/
dmmo.discussionEditor = {

    submitForm: function() {
    
        $('#postform').submit();
    
    },
    
    // Pass in the jQuery objects themselves.
    initializeCategorySelector: function(category, categoryInput) {
        
        // Category selection management.
        if(category != null) {
        
            category.find('li').click(function() {
                
                $(this)
                    .addClass('selected')
                    .siblings('li').removeClass('selected');
                
                var fid = $(this).data('filter-id');
                categoryInput.val(fid);
            });
        }
    }
}

/*
* ==============================================================================
* Profiles
* ==============================================================================
* 
* Basic functions to facilitate the proper display and functionality of
* profiles.
*/
dmmo.profiles = {

    initialize: function() {
    
        // Set content height.
        $('#profile-content').height(
        
            Math.max(
                ($('#profile-sidebar').height() - 145),
                $('#profile-content').height()
            )
        );
    }
}

dmmo.showcaseAnimator = (function() {
  
    var CLASS_LEFT_2 = 'anim-left-hidden';
    var CLASS_LEFT_1 = 'anim-left';
    var CLASS_CENTER = 'anim-center';
    var CLASS_RIGHT_1 = 'anim-right';
    var CLASS_RIGHT_2 = 'anim-right-hidden';
    
    function showcaseAnimator(showcase) {
    
        var _this = this;
        this._slides = [1];
        this._curSlide = 1;
        this._numSlides = $('.showcase .slide').length;
        this._curSlideIndex = 0;
        this._isJumping = false;
        this._isHovered = false;
        
        this._timeOnSlide = 0;
        this._timePerSlide = 5000;    
        
        this.$showcase = $(showcase);
        
        this.assignPositions();
        this.setupUIBindings();
        window.setInterval(function() { _this.update(); }, 10);
    }
    
    showcaseAnimator.prototype.assignPositions = function() {
    
        this._slides[3] = CLASS_LEFT_2;
        this._slides[4] = CLASS_LEFT_1;
        this._slides[0] = CLASS_CENTER;
        this._slides[1] = CLASS_RIGHT_1;
        this._slides[2] = CLASS_RIGHT_2;
    }
    
    showcaseAnimator.prototype.setupUIBindings = function() {
    
        var _this = this;
        
        // Showcase hovering detection.
        this.$showcase.hover(function() {
            _this._isHovered = true;
        }, function() {
            _this._isHovered = false;
        });
        
        // Caption hovering changes slide.
        this.$showcase.find('.showcase-captions li').hover(function() {
            _this.jumpTo($(this).index());
        }, function() {});
    }
    
    showcaseAnimator.prototype.nextSlide = function() {
    
        this._curSlideIndex = (this._curSlideIndex + 1) % 5;
        this._slides.unshift(this._slides.pop());
    }
    
    showcaseAnimator.prototype.prevSlide = function() {
    
        this._curSlideIndex = (this._curSlideIndex - 1 + 5) % 5;
        this._slides.push(this._slides.shift());
    }
    
    showcaseAnimator.prototype.jumpTo = function(slideNum) {
    
        if(!this._isJumping) {
            
            this._isJumping = true;
            var _this = this;
            var s = slideNum - this._curSlideIndex;
        
            while(s > 0) {
            this.nextSlide();
            this.update();
            s--;
            }
            while(s < 0) {
            this.prevSlide();
            this.update();
            s++;
            }
            
            this.resetSlide();
            setTimeout(function() { _this._isJumping = false; }, 50);
        }    
    }
    
    showcaseAnimator.prototype.applySlideStyles = function() {
    
    var _this = this;
    this.$showcase.children('.slide').each(function(i) {
    
        var classes = $(this)[0].className;
        $(this)[0].className = classes.replace(/\anim-.*\b/g, '');
        $(this).addClass(_this._slides[i]);
    }); 
    }
    
    showcaseAnimator.prototype.updateCaptionProgress = function() {
    
    var $caption = $('.showcase-captions ul li:eq(' + this._curSlideIndex + ')');
    var $progress = $caption.children('.progress');
    var percent = this._timeOnSlide / this._timePerSlide * 100;
    $progress.width(percent + "%");
    }
    
    showcaseAnimator.prototype.resetSlide = function() {
    
    this._timeOnSlide = 0;
    this.$showcase.find('.progress').width(0);
    }
    
    showcaseAnimator.prototype.update = function() {
    
    // Adjust time on slide counter.
    if(this._isHovered) {
    this._timeOnSlide = Math.max(0, this._timeOnSlide - 50); 
    } else {
    this._timeOnSlide += 10; 
    }
        
    // Check if it's time for the next slide.
    if(this._timeOnSlide >= this._timePerSlide) {
        
        this.resetSlide();
        this.nextSlide();
    }
    
    this.applySlideStyles();
    this.updateCaptionProgress();
    }
    
    return showcaseAnimator;
})();

$(document).ready(function() {

    dmmo.initialize();

});

$(document).ready(function() {

    /*
     * -------------------------------------------------------------------------------- */
    $('.showcase').each(function() {
    
        var id = $(this).attr('id');
        new showcaseAnimator(id);
    });

    /* Center avatar images in their avatar containers.
     * -------------------------------------------------------------------------------- */
    $('.avatar-large').each(function() {
    
        $(this).imagesLoaded(function() {

            $img = $(this).children('img');
            $img.css({
                marginTop: $img.height() / 2 * -1,
                marginLeft: $img.width() / 2 * -1
            });
        });
    });
    
    /* Force the footer to the bottom of short pages.
     * -------------------------------------------------------------------------------- */
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

    /* Provide support to file inputs.
     * -------------------------------------------------------------------------------- */
     $('.faux-file-input input').change(function() {
     
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('span').text(fileName);
     });
     
    /* Focus parents when their child inputs recieve focus.
     * -------------------------------------------------------------------------------- */
     $('.faux-text-input').children('input[type="text"]').focus(function() {
     
        $(this).parent().addClass('focused');
        
     }).blur(function() {
     
        $(this).parent().removeClass('focused');
     
     });    
});