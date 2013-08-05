/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	A collection of javascript functions and objects required
 *  by the profile pages.
 *
 *	Zach Knickerbocker
 */

/*
 * jQuery autoResize (textarea auto-resizer)
 * @copyright James Padolsey http://james.padolsey.com
 * @version 1.04
 */

var globprof;

dmmo.profiles = (function() {
  
    function profiles(profile_wall) {

        var _this = this; 
        var _msnry;

        this.setupUIBindings();
        this.initializeMasonry(profile_wall);
    }
    
    /*
     * Initializes masonry to organize messages on the profile wall.
     */
    profiles.prototype.initializeMasonry = function(profile_wall) {
        this._msnry = new Masonry(document.querySelector(profile_wall));
    }
    
    /*
     * Posts a new message to your profile.
     */
    profiles.prototype.postMessage = function(message, recipient) {
        var _this = this;

        var data = {
            operation: 'addMessage',
            message: message,
            recipient: recipient
        }

        $.ajax({
            url: '../php/eos_profiles.ajax.php',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data, status, jqXHR){
                console.log(data);
                console.log(status);
                _this.addMessageInDom(data.message, data.sender, data.sender_url, data.sender_avatar, data.date);
            },
            error:function(jqXHR, textStatus, errorThrown){
                console.log("jqHXR: " + jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            } 
        });
    }

    /* 
     * AJAX logic for posting a message.
     */
    profiles.prototype.addMessageInDom = function(message, sender, sender_url, sender_avatar, date) {
        
        var $new_message = $(
            "<li class=\"wall-block wall-block-message\">" +
            "    <div class=\"message-header group\">" +
            "        <div class=\"avatar avatar-small\"><img src=\"" + sender_avatar + "\" /></div>" +
            "        <a class=\"username\" href=\"#\">" + sender + "</a>" +
            "        <span class=\"date\">" + date + "</span>" +
            "    </div>" +
            "    <div class=\"message\">" + message + "</div>" +
            "</li>"
        );

        $('#profile-wall .wall').prepend($new_message);
        this._msnry.prepended($new_message);
    }

    /*
     * Setup UI bindings for profile elements.
     */
    profiles.prototype.setupUIBindings = function() {
        var _this = this;

        // Disable form submit. Everything is done with AJAX.
        // --------------------------------------------------------------------------------
        $('#profile-wall-form').submit(function(e) {
            e.preventDefault();
        });

        // New message submit button.
        // --------------------------------------------------------------------------------
        $('#profile-message-submitter').click(function() {
            var message = $('#profile-message-input').val();
            $('#profile-message-input').val('');
            var recipient = $('#profile-message-recipient').val();
            _this.postMessage(message, recipient);
        })

        // Textbox growing and special functionality with the enter button.
        // --------------------------------------------------------------------------------
        $('#profile-message-input').keydown(function(e){

            // Enter was pressed without shift key, trigger message submit.
            if (e.keyCode == 13 && !e.shiftKey)
            {
                $('#profile-message-submitter').click();
                e.preventDefault();

                return false;
            }
        }).autoResize({
            animate: false,
            extraSpace: -4
        });

        // Submit button animation over the text box.
        // --------------------------------------------------------------------------------
        var isHovered = false;

        $('#profile-message-input').focus(function() {
            isHovered = true;

            $('#profile-message-submitter').addClass('active');

        }).blur(function() {
            isHovered = false;

            setTimeout(function() {
                if(!isHovered) {
                    $('#profile-message-submitter').removeClass('active');
                }
            }, 700);
        });

        // More messages button.
        // --------------------------------------------------------------------------------
        $('#profile-more-messages').click(function() {

            // more to come
        });
    }
    
    return profiles;
})();

/* Make the magic happen. */
$(function() {

    globprof = new dmmo.profiles('#profile-wall .wall');
});