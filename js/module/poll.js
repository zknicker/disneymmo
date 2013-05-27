/*
 *	___  _ ____ _  _ ____ _   _ _  _ _  _ ____    ____ ____ ____ 
 *	|  \ | [__  |\ | |___  \_/  |\/| |\/| |  |    |___ |  | [__  
 *	|__/ | ___] | \| |___   |   |  | |  | |__|    |___ |__| ___] 
 *    
 *	Interacts with an existing poll to facilitate AJAX voting 
 *  in coordination with PHPBB 3.0.X.
 *
 *  External equirements: jQuery
 *
 *	Zach Knickerbocker
 */

dmmo.ajaxPoll = (function() {
  
    function ajaxPoll(poll) {

        var _this = this; 
        this.$poll = $('#' + poll);
        this.$poll_questions = this.$poll.find('.latest-poll-options');
        this._voteable = this.$poll.hasClass('voteable');
        
        console.log(this.$poll.find('form input[name="cur_voted_id"]').val());
        
        this.setupUIBindings();
    }
    
    ajaxPoll.prototype.vote = function(elem) {
    
        var _this = this;
    
        // Prevent more clicks.
        this.$poll_questions.children('.latest-poll-option').unbind('click').css({ 'cursor' : 'auto' });

        var update = this.$poll.find('form input[name="update"]').val();
        var topic_id = this.$poll.find('form input[name="topic_id"]').val();
        var forum_id = this.$poll.find('form input[name="forum_id"]').val();
        var creation_time = this.$poll.find('form input[name="creation_time"]').val();
        var form_token = this.$poll.find('form input[name="form_token"]').val();
        var cur_voted_id = this.$poll.find('form input[name="cur_voted_id"]').val();
        var vote_id = $(elem).data('poid');

        console.log(update + " : " + topic_id + " : " + forum_id + " : " + creation_time + " : " + form_token + " : " + cur_voted_id + " : " + vote_id);
        
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
                
                    dmmo.notifications.post("Your vote has been submitted.<br>Refresh to see the updated poll statistics.");
                    
                    // Maintenance.
                    _this.setVote(vote_id);
                    //_this.refreshPage();
                    
                } else {
                
                    dmmo.notifications.post("ERROR: " + response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
            
                dmmo.notifications.post("Error submitting vote. Please try again later.");
            }
        });
    }
    
    /* Set the user's vote with proper class mangement and anim.
	 * ------------------------------------------------------------ */
    ajaxPoll.prototype.setVote = function(option_id) {

        this.$poll.find('.voted').removeClass('voted');
        this.$poll.find('#' + option_id).addClass('voted');
    }

    /* Remove voting ability from user.
	 * ------------------------------------------------------------ */
    ajaxPoll.prototype.refreshPage = function() {

        // other poll maintenance post-user-vote
        this.$poll_questions.fadeOut(200, function() {
        
            location.reload();
        });
    }
    
    ajaxPoll.prototype.setupUIBindings = function() {
    
        // Clicking a poll option registers a poll vote.
        if(this._voteable) {
        
            var _this = this;
            this.$poll_questions.children('.latest-poll-option').click(function() {

                _this.vote(this);
            });
        }
    }
    
    return ajaxPoll;
})();

/* Make the magic happen. */
$(function() {

    $('.ajax-poll').each(function() {
    
        console.log("Creating ajax poll with id " + $(this).attr('id'));
        new dmmo.ajaxPoll($(this).attr('id'));
    });
});