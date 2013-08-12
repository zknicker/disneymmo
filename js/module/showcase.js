/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages the beautiful monster that is the homepage's showcase.
 *
 *	Zach Knickerbocker
 */
 
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
        this._timePerSlide = 3000;    
        
        this.$showcase = $('#' + showcase);
        
        this.assignPositions();
        this.setupUIBindings();
        window.setInterval(function() { _this.update(); }, 40);
    }
    
    showcaseAnimator.prototype.assignPositions = function() {
    
        this._slides[3] = CLASS_LEFT_2;
        this._slides[4] = CLASS_LEFT_1;
        this._slides[0] = CLASS_CENTER;
        this._slides[1] = CLASS_RIGHT_1;
        this._slides[2] = CLASS_RIGHT_2;
        this.applySlideStyles();
    }
    
    showcaseAnimator.prototype.setupUIBindings = function() {
    
        var _this = this;
        
        // Hovering over the center slide triggers isHovered.
        this.$showcase.find('.slide').hover(function() {
            if ($(this).hasClass('.anim-center')) {
                _this._isHovered = true;
            } else {
                _this._isHovered = false;
            }
        });

        // Caption hovering changes slide.
        this.$showcase.find('.caption').hover(function() {
            _this.jumpTo($(this).parent('.slide').index());
        });
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
    
        if(!this._isJumping && (slideNum !=  this._curSlideIndex)) {
            
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
            
            this.resetSlideProgress();
            this.applySlideStyles();
            setTimeout(function() { _this._isJumping = false; }, 50);
        }    
    }
    
    showcaseAnimator.prototype.applySlideStyles = function() {
    
        var _this = this;
        this.$showcase.find('.slide').each(function(i) {
        
            var $panel = $(this).children('.panel');
            var classes = $panel[0].className;
            $panel[0].className = classes.replace(/\s*\anim-.*\b\s*/g, '');
            $panel.addClass(_this._slides[i]);
        }); 
    }
    
    showcaseAnimator.prototype.updateCaptionProgress = function() {
    
        var $caption = $('.slide:eq(' + this._curSlideIndex + ')').children('.caption');
        var $progress = $caption.find('.progress-bar');
        var percent = this._timeOnSlide / this._timePerSlide * 100;
        $progress.width(percent + "%");
    }
    
    showcaseAnimator.prototype.resetSlideProgress = function() {
    
        this._timeOnSlide = 0;
        this.$showcase.find('.progress-bar').width(0);
    }
    
    showcaseAnimator.prototype.update = function() {
    
        // Adjust time on slide counter.
        if(this._isHovered) {
            this._timeOnSlide = Math.max(0, this._timeOnSlide - 60); 
        } else {
            this._timeOnSlide += 10; 
        }
        
        // Check if it's time for the next slide.
        if(this._timeOnSlide >= this._timePerSlide) {
            
            this.resetSlideProgress();
            this.nextSlide();
            this.applySlideStyles();
        }
        
        this.updateCaptionProgress();
    }
    
    return showcaseAnimator;
})();

/* Make the magic happen. */
$(function() {

    $('.showcase').each(function() {
    
        console.log("Creating showcase with id " + $(this).attr('id'));
        new dmmo.showcaseAnimator($(this).attr('id'));
    });
});