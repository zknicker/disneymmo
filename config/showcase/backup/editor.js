dese = {

	initialize: function() {
	
        dese.showcase.initialize();
        dese.editor.initialize();
        dese.events.initialize();
	
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
    }

}

dese.showcase = {

	FEATURED_HEIGHT: 250,		// Featured article height.
	FEATURED_WIDTH: 427,		// Featured article width.
	NORMAL_HEIGHT: 250,			// Normal article height.
	NORMAL_WIDTH: 219,			// Normal article width.
	
	FEATURED_CLASS: 'feature',	// Featured article class.
	NORMAL_CLASS: 'normal',		// Normal article class.
	
	featuredElem: 0,			// Current featured article cache.

	/*
	 * Offset and resize background for aesthetic purposes.
	 */
	setBackgroundPosition: function(elem) {
	
		var img = $(elem).find('img');
		
		// Check if image has been loaded.  If not, load it.
		if (!img[0].complete) {
		
			// Make a request to load the image, including a callback to this function
			// in order to run it again and accomplish the original goal.
			dese.loadImage(img, function() { dese.showcase.setBackgroundPosition(elem) });
		
		}
		
		img.css({ "visibility": "hidden" });
		
		// Reposition image for featured block.
		if ($(elem).hasClass('feature')) {
		
			var xOffset = (dese.showcase.FEATURED_WIDTH - img[0].width) / 4;
			var yOffset = (dese.showcase.FEATURED_HEIGHT - img[0].height) / 4;
			img.css({ "top": yOffset, "left": xOffset });
		
		// Reposition image for normal block.
		} else {
			
			var xOffset = (dese.showcase.NORMAL_WIDTH - img[0].width) / 4;
			var yOffset = (dese.showcase.NORMAL_HEIGHT - img[0].height) / 4;
			img.css({ "top": yOffset, "left": xOffset });
		
		}
		
		img.css({ "visibility": "visible" });		
	},
	
	/*
	 * Set a dese.showcase article to featured.
	 */
	setFeatured: function(panel) {
		
        var elem = $('#panel-' + panel);
        
		// Make current featured article normal.
		$(dese.showcase.featured).removeClass(dese.showcase.FEATURED_CLASS).addClass(dese.showcase.NORMAL_CLASS);
		dese.showcase.setBackgroundPosition(dese.showcase.featured);
		
		// Make requested article featured.
		$(elem).removeClass(dese.showcase.NORMAL_CLASS).addClass(dese.showcase.FEATURED_CLASS);
		dese.showcase.setBackgroundPosition(elem);
        
		// Maintenance tasks.
		dese.showcase.featured = $(elem);
	
	},
    
    refresh: function() {
    
        for(var i = 1; i <= 4; i++) {
        
            var panel = $('#panel-' + i)
            dese.showcase.setBackgroundPosition(panel);
            
            panel.removeClass('dmmo cp ph po tt').addClass(dese.editor.getCurrentCategory(i));
            panel.find('.news-title').text(dese.editor.getCurrentTitle(i));
            panel.find('.news-preview').text(dese.editor.getCurrentPreview(i));
            panel.find('.game-title').text(dese.editor.getCurrentCategoryText(i));
            panel.find('.background img').attr('src', dese.editor.getCurrentCover(i));
            
        }
    
    },
	
	initialize: function() {
	
		$('#showcase').find('article').each(function() {
		
			dese.showcase.featured = $('#showcase').find('.feature');
        
			dese.showcase.setBackgroundPosition(this);
			
		});
	
    }
	
};

dese.editor = {

	changeFocus: function(slot) {
    
        dese.editor.showEditorForpanel(slot);
    
    },
    
    showEditorForpanel: function(slot) {
    
        $('.editor').removeClass('active');
        $('#editor-' + slot).addClass('active');
    },
    
    populate: function(slot, id, title, preview, category, cover) {
    
        var editor = $('#editor-' + slot);
        
        editor.find('input[name=panel-' + slot + '-topic-id]').val(id);
        editor.find('input[name=panel-' + slot + '-title]').val(title);
        editor.find('input[name=panel-' + slot + '-cover]').val(cover);
        editor.find('textarea[name=panel-' + slot + '-preview]').val(preview);
        editor.find('select[name=panel-' + slot + '-category]').val(category);
    
    },
    
    getCurrentTopicId: function(slot) {
        
        return $('#editor-' + slot).find('input[name=panel-' + slot + '-topic-id]').val();
        
    },
    
    getCurrentTitle: function(slot) {
    
        return $('#editor-' + slot).find('input[name=panel-' + slot + '-title]').val();
        
    },
    
    getCurrentCategory: function(slot) {
    
        return $('#editor-' + slot).find('select[name=panel-' + slot + '-category]').val();
        
    },
    
    getCurrentCategoryText: function(slot) {
    
        var select = $('#editor-' + slot).find('select[name=panel-' + slot + '-category]')
        return select.find('option[value=' + select.val() + ']').text();
        
    },
    
    getCurrentPreview: function(slot) {
        
        return $('#editor-' + slot).find('textarea[name=panel-' + slot + '-preview]').val();
    
    },
    
    getCurrentCover: function(slot) {
    
        return $('#editor-' + slot).find('input[name=panel-' + slot + '-cover]').val();
    
    },
    
    initialize: function() {
        
    
    }
}

dese.events = { 

    /*
     * Submit form button located above showcase preview.
     */
    submitForm: function() {
    
        $('#save-changes').click(function() {
        
            $('#showcase-data').submit();
        
        });
    
    },
    
    /*
     * Generate information from topic id.
     */
    generateFromTopicID: function() {
    
        $('.generate-button').click(function() {
        
            $(this).addClass('requesting');
            
            var spot = ($(this).attr('id')).substring(6,7);
            var topic_id = $('input[name=panel-' + spot + '-topic-id]').val();
        
            $.ajax({
                type: 'POST',
                url: 'editor.scraper.php',
                data: {topic_id : topic_id},
                timeout: 5000,
                success: function(data_json) {
                    
                    // Get JSON data and populate editor.
                    data = jQuery.parseJSON(data_json);
                    dese.editor.populate(spot, data['topic_id'], data['title'], data['preview'], data['category'], data['cover']);
                    
                    // Refresh showcase preview.
                    dese.showcase.refresh();
                    
                    $('.generate-button').removeClass('requesting');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                
                    alert('Fatal error. Request returned:\n\n' + textStatus);
                    $('.generate-button').removeClass('requesting');

                }
            });            
        
        });
    
    },
    
    /*
     * Resize showcase panel, move victim highlight, and change editor.
     */
    choosePanel: function() {
    
        $('article').click(function() {
        
            var panel = ($(this).attr('id')).substring(6,7);
            
            // Set showcase item to featured (also sets victim via css).
            dese.showcase.setFeatured(panel);
            
            // Change editor location/contents.
            dese.editor.changeFocus(panel);
        
        });
    
    },
    
    /*
     * Refresh the showcase preview when a form field is defocused.
     */
    automaticPreviewRefresh: function() {
    
        $('input, select, textarea').blur(function() {
        
            dese.showcase.refresh();
        
        });
    
    },
    
    /*
     * Spawn notification for successful form submission.
     */
    changesWritten: function() {
    
        $('#notification').stop().hide();
        $('#notification > span').text('Changes written to the database.');
        $('#notification').fadeIn(800).delay(3000).fadeOut(800);
    
    },
    
    /*
     * Spawn notification for error in form validation.
     */
    formError: function(message) {
    
        $('#notification').stop().hide();
        $('#notification > span').text('ERROR: ' + message);
        $('#notification').fadeIn(800).delay(3000).fadeOut(800);
    
    },
    
    /*
     * Shift panels to the right one space.
     */
    shiftRight: function() {
    
        dese.events.swapPanels(1, 2);
        dese.events.swapPanels(1, 3);
        dese.events.swapPanels(1, 4);
        
        setTimeout(function() { dese.showcase.refresh(); }, 200);
        
    },
    
    /*
     * Shift panels to the left one space.
     */
    shiftLeft: function() {
    
        dese.events.swapPanels(4, 3);
        dese.events.swapPanels(4, 2);
        dese.events.swapPanels(4, 1);
        
        setTimeout(function() { dese.showcase.refresh(); }, 200);
        
    },
    
    /*
     * Spawn notification for error in form validation.
     */
    swapPanels: function(slot_a, slot_b) {
    
        // Swap topic_id values.
        var title_a = $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-topic-id]').val();
        $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-topic-id]').val($('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-topic-id]').val());
        $('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-topic-id]').val(title_a);
        
        // Swap title values.
        var title_a = $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-title]').val();
        $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-title]').val($('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-title]').val());
        $('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-title]').val(title_a);
        
        // Swap showcase preview values.
        var title_a = $('#editor-' + slot_a + ' textarea[name=panel-' + slot_a + '-preview]').val();
        $('#editor-' + slot_a + ' textarea[name=panel-' + slot_a + '-preview]').val($('#editor-' + slot_b + ' textarea[name=panel-' + slot_b + '-preview]').val());
        $('#editor-' + slot_b + ' textarea[name=panel-' + slot_b + '-preview]').val(title_a);
        
        // Swap category.
        var title_a = $('#editor-' + slot_a + ' select[name=panel-' + slot_a + '-category]').val();
        $('#editor-' + slot_a + ' select[name=panel-' + slot_a + '-category]').val($('#editor-' + slot_b + ' select[name=panel-' + slot_b + '-category]').val());
        $('#editor-' + slot_b + ' select[name=panel-' + slot_b + '-category]').val(title_a);
        
        // Swap cover image.
        var title_a = $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-cover]').val();
        $('#editor-' + slot_a + ' input[name=panel-' + slot_a + '-cover]').val($('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-cover]').val());
        $('#editor-' + slot_b + ' input[name=panel-' + slot_b + '-cover]').val(title_a);
        
        // Handle refresh of showcase on your own. This only changes the editor!
        
    },
    
    /*
     * Enable the panels on the showcase to be dragged and reordered.
     */
    showcaseReordering: function() {
    
        $('article').draggable({ 
        
            containment: "#showcase", 
            axis: 'x',
            scroll: false,
            revert: true,
            revertDuration: 0,
            snap: 'article',
            snapMode: 'both',
            distance: 20,
            
            start: function(event, ui) {
            
                $('.showcase-view').addClass('dragging');
            
            },
            
            stop: function(event, ui) {
            
                $('.showcase-view').removeClass('dragging');
            
            }
        
        });
	
        $('article').droppable({
        
            drop: function(event, ui) {
				
                // Swap dragged and dropped panels.
                var slot_a = ($(this).attr('id')).substring(6,7);
                var slot_b = (ui.draggable.attr('id')).substring(6,7);
                dese.events.swapPanels(slot_a, slot_b);                
                
                // Override the click event triggered by dragging.
                setTimeout(function() { 
                    
                    // Change showcase victim highlight, editor position, and refresh showcase.
                    dese.showcase.setFeatured(slot_a);
                    dese.editor.changeFocus(slot_a);
                    dese.showcase.refresh();
                    
                }, 0);
			}
        
        });
    
    },
    
    initialize: function() {
    
        dese.events.submitForm();
        dese.events.generateFromTopicID();
        dese.events.choosePanel();
        dese.events.automaticPreviewRefresh();
        dese.events.showcaseReordering();
    
    }

}

$('document').ready(function() {

	dese.initialize();

});