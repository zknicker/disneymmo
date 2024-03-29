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

dmmo.profiles = (function() {
  
    function profiles(profile_wall) {

        this._this = this;
        this._msnry = null;
        this.$wall = $(profile_wall);
        
        this.setupUIBindings();
        this.initializeMasonry(profile_wall);
        
        this.setupProfileImageCustomizerDropzone();
    }
    
    /*
     * Initializes masonry to organize messages on the profile wall.
     */
    profiles.prototype.initializeMasonry = function(profile_wall) {

        this._msnry = new Masonry(document.querySelector(profile_wall));
    }
    
    /*
     * AJAX logic for posting a new message to your profile.
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
            
                // Good to go!
                if (!data.error) {
                    _this.prependMessage(data.data.message, data.data.sender, data.data.sender_url, data.data.sender_avatar, data.data.date, data.data.date_rel);
                // Error, handle it.
                } else {
                    dmmo.notifications.post(data.error_message);
                }
                _this.enableMessageInput();
            },
            error:function(jqXHR, textStatus, errorThrown){
            
                _this.enableMessageInput();
                dmmo.notifications.post('Sorry, we made a mistake (ERROR: AJAX request).');
            } 
        });
    }
    
    /*
     * AJAX logic for retrieving more messages and displays them.
     */
    profiles.prototype.getMoreMessages = function(recipient) {
        var _this = this;
    
        var data = {
            operation: 'getMoreMessages',
            recipient: recipient,
            begin: this.$wall.children().size(),
            qty: 20
        }

        $.ajax({
            url: '../php/eos_profiles.ajax.php',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data){
            
                // Good to go!
                if (!data.error) {
                    jQuery.each(data.data, function() {
                        _this.appendMessage(this.message, this.sender, this.sender_url, this.sender_avatar, this.date, this.date_rel);
                    });
                // Error, handle it.
                } else {
                    dmmo.notifications.post(data.error_message);
                }
                _this.enableMoreMessagesButton();
            },
            error:function(data){
            
                _this.enableMoreMessagesButton();
                dmmo.notifications.post("Sorry, we made a mistake (ERROR: AJAX request).");
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
     * Refreshes the profile banner.
     */
    profiles.prototype.refreshProfileBanner = function(recipient) {
        var $profile_header = $('.profile-header');
        var background = $profile_header.css('background-image');
        background = background.replace(/url\(\"?/, '').replace(/\"?\)/, '');
        var date = new Date();
        $profile_header.css('background-image', 'url(' + background + '?' + date.getTime() + ')');
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
            var recipient = $('#profile-recipient').val();
            
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
            extraSpace: -1
            
        // Prevent the textarea from scrolling and looking odd before the resize happens.
        }).on('scroll', function(e) {
            this.scrollTop = 0;
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
                var recipient = $('#profile-recipient').val();
                _this.disableMoreMessagesButton();
                
                _this.getMoreMessages(recipient);
            }
        });
    }
    
    /*
     * Setup Profile Image Dropzone
     */
    profiles.prototype.setupProfileImageCustomizerDropzone = function() {
        var _this = this;
    
        // Disable Dropzone's autodiscovery feature.
        Dropzone.autoDiscover = false;
    
        var dropzoneElem = '#profile-banner-customizer';
        var $dropzoneProgress = $(dropzoneElem).find('.dropzone-progress');
        
        // Enable Dropzone on the banner customizer form.
        var dropzone = new Dropzone(dropzoneElem, {
            url: $(dropzoneElem).attr('action'),
            maxFilesize: 1.5,
            maxFiles: 1,
            acceptedFiles: 'image/*',
            clickable: '.profile-banner-customizer, .profile-banner-customizer .upload-button',
            paramName: 'profile_header',
            fallback: function() { _this.setupProfileImageCustomizerDropzoneFallback($(dropzoneElem)) }
        });
        
        // Dropzone events and responses.
        dropzone.on('error', function(file, errorMessage) {
            dmmo.notifications.post('Error encountered uploading profile banner: ' + errorMessage);
        });
        
        dropzone.on('processing', function(file) {
            $(dropzoneElem).find('.upload-zone').addClass('uploading');
            $dropzoneProgress.removeClass('finished');
        });
        
        dropzone.on('uploadprogress', function(file, progress, bytesSent) {
            $dropzoneProgress.find('.progress span').text(progress);
        });
        
        dropzone.on('complete', function(file) {
            _this.refreshProfileBanner();
            
            // Aesthetic stuff.
            $(dropzoneElem).find('.upload-zone').removeClass('uploading');
            $dropzoneProgress.addClass('finished');
            
            // Remove uploaded/errored file.
            dropzone.removeFile(file);
        });
        
        dropzone.on('sending', function(file, xhr, formData) {
            formData.append('operation', 'uploadProfileBanner');
        });
    }
    
    /*
     * Setup Profile Banner Dropzone Fallback
     */
    profiles.prototype.setupProfileImageCustomizerDropzoneFallback = function($form) {
        var _this = this;
        
        // Fallback Setup
        $form.find('.profile-banner-customizer').addClass('customizer-fallback');
        
        // Jquery Form Plugin which makes the form send an AJAX request when submitted.
        $form.ajaxForm({ 
            cache: false,
            type: 'post',
            dataType: 'json',
            data: {
                operation: 'uploadProfileBanner'
            },
            success: function(data){
            
                // Good to go!
                if (!data.error) {
                    dmmo.notifications.post("Profile banner image was updated successfully.");
                    $form.find('.upload-zone').removeClass('uploading');
                    $form.find('.dropzone-progress').addClass('finished');
                    _this.refreshProfileBanner();
                    
                // Error, handle it.
                } else {
                    dmmo.notifications.post(data.error_message);
                }
            },
            error:function(data){
                dmmo.notifications.post("Sorry, we made a mistake (ERROR: AJAX request).");
            } 
        });
        
        // Form submit event.
        $form.on('submit', function() {
            $form.find('.dropzone-progress').removeClass('finished');
            $form.find('.upload-zone').addClass('uploading');
        });
        
        // Submit form when the file input is updated by the user.
        $form.find('.hidden-file-input').on('change', function() {
            $form.submit();
        });
    }
    
    return profiles;
})();

/* Make the magic happen. */
$(function() {

    new dmmo.profiles('#profile-wall .wall');
});