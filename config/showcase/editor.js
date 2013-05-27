/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages the showcase editor.
 *
 *	Zach Knickerbocker
 */
 
dese = (function() {
  
    function dese(slides) {
    
        var _this = this;
        this.$slides = $('#' + slides);
        this.num_slides = this.$slides.find('.dese-slide-editor').length;
        
        this.setupUIBindings();
    }
    
    dese.prototype.setupUIBindings = function() {
    
        var _this = this;
        this.$slides.find('.dese-slide-editor').each(function() {
            
            var _slide_no = $(this).data('slide-no')
            
            // Buff button
            $(this).find('.dese-controls-buff').click(function() {
                
                _this.buff(_slide_no, true);
            });
            
            // Nerf button
            $(this).find('.dese-controls-nerf').click(function() {
                
                _this.nerf(_slide_no, true);
            });
            
            // Remove button
            $(this).find('.dese-controls-remove').click(function() {
                
                _this.remove(_slide_no);
            });
            
            // Clear button
            $(this).find('.dese-controls-clear').click(function() {
                
                _this.clear(_slide_no);
            });
        });
    }
    
    /* Remove a slide from the ordering by buffing all of the other slides. */
    dese.prototype.remove = function(slide) {
    
        this.clear(slide);
        
        for(var i = slide + 1; i < this.num_slides; i++) {
        
            this.buff(i, false);
        }
    }
    
    /* Clear a slide by nullifying its form fields. */
    dese.prototype.clear = function(slide) {
    
        var $slide = this.$slides.find('.dese-slide-editor[data-slide-no="' + slide + '"]');
    
        $slide.find('.dese-controls-type').val(0);
        $slide.find('.dese-slide-fields').find('input').val('').removeAttr('value');
        this.playPingAnimation(slide);
    }
    
    /* Move a slide forward in the ordering. */
    dese.prototype.buff = function(slide, animate) {
    
        var target = (slide + this.num_slides - 1) % this.num_slides;
        this.swapSlides(slide, target);
        
        if(animate)
            this.scrollToSlide(target);
    }
    
    /* Moves a slide backward in the ordering. */
    dese.prototype.nerf = function(slide, animate) {
    
        var target = (slide + 1) % this.num_slides;
        this.swapSlides(slide, target);
        
        if(animate)
            this.scrollToSlide(target);
    }
    
    /* Swaps slides a and b in the ordering by swapping each slide editor's
     * contents and updating the form element's name attributes. 
     * 
     * Note that the values of the form inputs are not simply swapped because
     * which inputs are present is dependent on the type of slide being edited. */
    dese.prototype.swapSlides = function(a, b) {
    
        var $a_elems = $('input[name$=' + a + '], textarea[name$=' + a + ']');
        var $b_elems = $('input[name$=' + b + '], textarea[name$=' + b + ']');
        
        // Update names of form elements in a.
        $a_elems.each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.substring(0, name.length - a.toString().length) + b);
        });
        
        // Update names of form elements in b.
        $b_elems.each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.substring(0, name.length - b.toString().length) + a);
        });
        
        // Swap fields in a and b.
        swapHTML(this.$slides.find('.dese-slide-editor[data-slide-no="' + a + '"]').find('.dese-slide-fields'), 
                 this.$slides.find('.dese-slide-editor[data-slide-no="' + b + '"]').find('.dese-slide-fields'));
    
        // Swap the value of the type selectors.
        var $a_select = this.$slides.find('.dese-slide-editor[data-slide-no="' + a + '"]').find('.dese-controls-type');
        var $b_select = this.$slides.find('.dese-slide-editor[data-slide-no="' + b + '"]').find('.dese-controls-type');
        var a_select_val = $a_select.val();
        $a_select.val($b_select.val());
        $b_select.val(a_select_val);
        
        // Show the user that stuff changed.
        this.playPingAnimation(a);
        this.playPingAnimation(b);
    }
    
    /* Performs a subtle animation on the slide. This is useful to indicate
     * that some change has taken place in a slide. */
    dese.prototype.playPingAnimation = function(slide) {
    
        var $slide = this.$slides.find('.dese-slide-editor[data-slide-no="' + slide + '"]').find('.dese-slide-fields');
        var original_margin = $slide.css('marginLeft');
        console.log(original_margin);
        $slide.css({'opacity': 0, 'marginLeft': 50});
        $slide.animate({opacity: 1, marginLeft: 20}, 300);
    }
    
    /* Scrolls the window to the specified slide. */
    dese.prototype.scrollToSlide = function(slide) {
    
        var $slide = this.$slides.find('.dese-slide-editor[data-slide-no="' + slide + '"]');
        $('html, body').animate({
            scrollTop: $slide.offset().top
            }, 400);
        }
    
    return dese;
})();

/* Start the showcase editor by creating a new dese object. */
$(function() {

    new dese('dese');
});

/* Given two jQuery objects, their HTML is swapped. */
function swapHTML(a, b) {

    var temp = $(a).html();
    $(a).html($(b).html());
    $(b).html(temp);
}