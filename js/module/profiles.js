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

/* Profile Events
 * --------------------------------------------------------------------------------- */
dmmo.profiles = {

    showCustomizer: function()
    {
        $('#customize-profile-menu')
            .css('visibility', 'visible')
            .animate({opacity: 1}, 700);
            
        $('#menu-active-area')
            .animate({top: '45%'}, 700);
    },
    
    hideCustomizer: function() {
    
        $('#customize-profile-menu')
            .animate({opacity: 0}, 700, function() {
                $(this).css('visibility', 'hidden');
            });
            
        $('#menu-active-area')
            .animate({top: '-45%'}, 700);
    },
    
    commitChanges: function(form) {
    
        $('form[name=' + form + ']').submit();
    },
    
    /* Close menu when user clicks in non-menu space.
     * ------------------------------------------------------------ */
    indirectMenuCloseEvent: function() {
    
        var that = this;
        var isHovered = false;
        
        $('#menu-active-area').hover(function() {
        
            isHovered = true;
        }, function() {
        
            isHovered = false;
        });
        
        $('#customize-profile-menu').click(function() {
        
            if(!isHovered) that.hideCustomizer();
        });
    },
    
    init: function()
    {
        this.indirectMenuCloseEvent();
    }
}

/* Make the magic happen baby. */
(function() {

  dmmo.profiles.init();
})();

/* Profile Uploader [OBJECT]
 * --------------------------------------------------------------------------------- */
ProfileUploader = function(parent, name, defaultImgSrc, buttonText) {

    var _parent = '#' + parent;
    createProfileUploader(_parent);
    
    /* creates the profile uploader HTML */
    function createProfileUploader()
    {
        var _preview = document.createElement('div');
        $(_preview)
            .addClass("customize-image")
            .css('background-image', 'url(' + defaultImgSrc + ')');
        
        var _uploaderButton = document.createElement('div');
        $(_uploaderButton)
            .addClass('button-file-selector')
            .addClass('clickable')
            .html(buttonText);
            
        var _uploader = document.createElement('div');
        $(_uploader)
            .addClass('faux-file-viewer')
            .append(_uploaderButton);
        
        var _progress = document.createElement('div');
        $(_progress)
            .addClass('upload-progress');
        
        var _hiddenInput = document.createElement('input');
        $(_hiddenInput)
            .attr('type', 'hidden')
            .attr('name', name);
        
        $(_parent)
            .append(_progress)
            .append(_preview)
            .append(_uploader)
            .append(_hiddenInput);
        
        /* create instance of the file uploader */
        var _qqu = new qq.FileUploaderBasic({
            
            button: $(_uploaderButton)[0],
            multiple: false,
            action: 'http://disneymmo.com/lib/fileuploader/uploader.php',
            debug: true,
            sizeLimit: 2097152,
            maxConnections: 1,
            allowedExtensions: ['jpg', 'jpeg', 'bmp', 'png', 'gif'],
            onUpload: uploadResponse,
            onProgress: progressResponse,
            onComplete: completeResponse,
            onError: errorResponse
        });
        
        /* response to detection of uploading image */
        function errorResponse(id, fileName, errorReason)
        {
            alert("Error: " + errorReason);
        }
        
        /* response to detection of uploading image */
        function uploadResponse(id, fileName)
        {
            $(_parent).addClass('uploading');
        }
        
        /* response to progress in uploading image */
        function progressResponse(id, fileName, uploadedBytes, totalBytes)
        {
            var height = $(_preview).outerHeight();
            $(_progress)
                .css('height', uploadedBytes / totalBytes *  height)
                .html(Math.ceil(uploadedBytes / totalBytes * 100) + '%');
        }
        
        /* response to completion in uploading of an image */
        function completeResponse(id, fileName, responseJSON)
        {
            var url = 'http://disneymmo.com/images/previews/' + fileName;
        
            // update image preview
            $(_preview).css('background-image', 'url(' + url + ')');
        
            // update hidden input
            $(_hiddenInput).val(fileName);
        
            // after so many seconds, remove progress bar
            setTimeout(function() {
                
                $(_progress).animate({
                    opacity: 0,
                    height: 0
                    
                }, 500, function() {
                
                    $(this).fadeTo(0, 1);
                    $(_parent).removeClass('uploading');
                });
            }, 500);
        }
    }
}