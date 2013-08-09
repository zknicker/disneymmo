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

        this._this = this;
        this._msnry = null;
        this.$wall = $(profile_wall);
        
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
            operation: 'postMessage',
            message: message,
            recipient: recipient
        }

        $.ajax({
            url: '../php/eos_profiles.ajax.php',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data, status, jqXHR){
            
                _this.prependMessage(data.message, data.sender, data.sender_url, data.sender_avatar, data.date, data.date_rel);
                _this.enableMessageInput();
            },
            error:function(jqXHR, textStatus, errorThrown){
            
                dmmo.notifications.post("Sorry, your message could not be posted. Please retry.");
                _this.enableMessageInput();
            } 
        });
    }
    
    /*
     * Retrieves more messages and displays them.
     */
    profiles.prototype.getMoreMessages = function() {
        var _this = this;
    
        var data = {
            operation: 'getMoreMessages',
            begin: this.$wall.children().size(),
            qty: 10
        }

        $.ajax({
            url: '../php/eos_profiles.ajax.php',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data, status, jqXHR){
            
                jQuery.each(data, function() {
                    _this.appendMessage(this.message, this.sender, this.sender_url, this.sender_avatar, this.date, this.date_rel);
                });
                _this.enableMoreMessagesButton();
            },
            error:function(jqXHR, textStatus, errorThrown){
            
                _this.enableMoreMessagesButton();
            } 
        });
    }

    /* 
     * AJAX logic for posting a message.
     */
    profiles.prototype.appendMessage = function(message, sender, sender_url, sender_avatar, date, date_rel) {
    
        var $new_message = this.constructNewMessage(message, sender, sender_url, sender_avatar, date, date_rel);
        this.addMessageInDom($new_message, "append");
    }
    
    profiles.prototype.prependMessage = function(message, sender, sender_url, sender_avatar, date, date_rel) {
        var $new_message = this.constructNewMessage(message, sender, sender_url, sender_avatar, date, date_rel);
        this.addMessageInDom($new_message, "prepend");
    }
    
    profiles.prototype.constructNewMessage = function(message, sender, sender_url, sender_avatar, date, date_rel) {
    
        var $new_message = $(
            "<li class=\"wall-block wall-block-message\">" +
            "    <div class=\"message-header group\">" +
            "        <div class=\"avatar avatar-small\">" + sender_avatar + "</div>" +
            "        <a class=\"username\" href=\"" + sender_url + "\">" + sender + "</a>" +
            "        <span class=\"date\" data-tooltip=\"" + date + "\">" + date_rel + "</span>" +
            "    </div>" +
            "    <div class=\"message\">" + message + "</div>" +
            "</li>"
        );
        
        return $new_message;
    }
    
    profiles.prototype.addMessageInDom = function($new_message, type) {

        if(type == "prepend") {
            $('#profile-wall .wall').prepend($new_message);
            this._msnry.prepended($new_message);
            
        } else {
            $('#profile-wall .wall').append($new_message);
            this._msnry.appended($new_message);
        }
    }
    
    profiles.prototype.disableMessageInput = function() {
        
        $('.message-input').addClass('busy');
        $('#profile-message-input').prop('disabled', true);
    }
    
    profiles.prototype.enableMessageInput = function() {
    
        $('.message-input').removeClass('busy');
        $('#profile-message-input').prop('disabled', false);
    }

    profiles.prototype.disableMoreMessagesButton = function() {
    
        $('.more-wall-messages').addClass('busy');
    }

    profiles.prototype.enableMoreMessagesButton = function() {
    
        $('.more-wall-messages').removeClass('busy');
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
            var recipient = $('#profile-message-recipient').val();
            
            // Post specified message.
            _this.postMessage(message, recipient);
            
            // Disable input.
            _this.disableMessageInput();
            
            // Clear current message.
            $('#profile-message-input').val('');
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
        $('.more-wall-messages').click(function() {
        
            if(!$(this).hasClass('busy')) {
                _this.disableMoreMessagesButton();
                _this.getMoreMessages();
            }
        });
    }
    
    return profiles;
})();

/* Make the magic happen. */
$(function() {

    globprof = new dmmo.profiles('#profile-wall .wall');
});