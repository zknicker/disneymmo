/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Manages the category selector displayed when creating a new discussion.
 *
 *	Zach Knickerbocker
 */

 dmmo.discussionCategorySelector = (function() {
  
    function discussionCategorySelector(category_selector) {
    
        var _this = this;
        
        // The category selector parent element.
        this.$category_selector = $('#' + category_selector);
        
        // Filters within the category selector.
        this.$filters = this.$category_selector.children('li');
        
        // Article edit blocks made visible when specific filters are selected.
        this.$articles_edit_blocks = $('.edit-articles-only');
        
        // Class on filter which results in showing the article edit blocks.
        this.article_class = "posts-articles";
        
        // Hidden input for PHPBB to keep track of selected category.
        this.$hidden_input = $('input[name="f"]');
        
        this.setupUIBindings();
    }
    
    discussionCategorySelector.prototype.setupUIBindings = function() {
    
        var _this = this;
        
        // Discussion filter clicked; switch highlighted filter.
        this.$filters.click(function() {
        
            _this.handleFilterSelection(this);
        });        
    }
    
    discussionCategorySelector.prototype.handleFilterSelection = function(filter) {
    
        this.$filters.removeClass('selected');
        $(filter).addClass('selected');
        this.$hidden_input.val($(filter).data('filter-id'));

        // Enabled/disable article edit blocks.
        if ($(filter).hasClass(this.article_class)) {
        
            this.$articles_edit_blocks.addClass('visible');
        
        } else {
        
            this.$articles_edit_blocks.removeClass('visible');
        }
    }
    
    return discussionCategorySelector;
})();

/* Make the magic happen. */
$(function() {

    $('.discussion-category-selector').each(function() {
    
        console.log("Creating discussion category selector with id " + $(this).attr('id'));
        new dmmo.discussionCategorySelector($(this).attr('id'));
    });
});