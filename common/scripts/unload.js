/*!
 * unload script
 */
 
jQuery(window).bind('beforeunload', function(e) {
    var message = "Why are you leaving?";
    e.returnValue = message;
    return message;
});
 