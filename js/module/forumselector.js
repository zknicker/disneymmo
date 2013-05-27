/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Sets up css and events for the forum selector on the portal
 *  page.
 *
 *  TODO:
 *    - General code cleanup.
 *    - Make this an object perhaps.
 *
 *	Zach Knickerbocker
 */

/* Forum Selector
 * --------------------------------------------------------------------------------- */
dmmo.forumselector = {

    root: null,
		
    computeGoldenRatio: function(width, factor) {
        
        var result = width;
        var goldenRatio = 1.618;
        while(factor >= 1) {
        
            result = result / goldenRatio;
            factor--;
        }
        return result;
    },
    
    configForumBlocks: function() {
    
        // Variables.
        var rightMargin = 5;
       
        var workingWidth = dmmo.forumselector.root.width();
        var effectiveWidth = workingWidth + rightMargin; // the last one has no margin
        
        // Logic.
        var blocksPerRow = 4;
        while(effectiveWidth / blocksPerRow > 290) {
        
            blocksPerRow++;
        }
        
        calculatedWidth = effectiveWidth / blocksPerRow - rightMargin;
        leftoverPx = Math.ceil((calculatedWidth - Math.floor(calculatedWidth)) * blocksPerRow);
        finalWidth = Math.floor(calculatedWidth);
        finalHeight = dmmo.forumselector.computeGoldenRatio(finalWidth, 3) + 10;
        
        dmmo.forumselector.root.find('li .forum-container').css({
        
            'width'     : finalWidth + 'px'
        });
        
        dmmo.forumselector.root.find('li .roller').css({
        
            'height'    : finalHeight + 'px'
        });
        
        dmmo.forumselector.root.find('li').removeClass('no-right-margin');
        dmmo.forumselector.root.find('li:nth-child(' + blocksPerRow + 'n)').addClass('no-right-margin');
        
        var fontSize;
        if (finalWidth >= 275) fontSize = 13;
        else if (finalWidth < 275 && finalWidth >= 255) fontSize = 12;
        else if (finalWidth < 255 && finalWidth >= 241) fontSize = 11;
        else if (finalWidth < 241) fontSize = 10;
        
        dmmo.forumselector.root.find('.description').css({
        
            'font-size' : fontSize + 'px'
        });
    },
        
    resizeEvents: function() {
        
        $(window).resize(function() {
    
            dmmo.forumselector.configForumBlocks();
        });
    },
    
    init: function() {
		
        $('.forum-blocks').css('width', '101%');
        
        dmmo.forumselector.configForumBlocks();
        dmmo.forumselector.resizeEvents();
        dmmo.forumselector.root.removeClass('loading');
    }
}

/* Make the magic happen baby. */
$('document').ready(function() {

    dmmo.forumselector.root = $('#forum-selector');
    dmmo.forumselector.init();
});