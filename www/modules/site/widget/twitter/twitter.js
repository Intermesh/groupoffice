/**
 * get-tweets.js
 * http://github.com/kevindeleon/get-tweets
 *
 * Copyright 2013, Kevin deLeon
 * Licensed under the MIT license.
 * http://github.com/kevindeleon/get-tweets/blob/master/LICENSE
 *
 * Much of this logic was derrived from blogger.js from Twitter and converted to jQuery
 * The releative_time function was take directly from Twitter's blogger.js 
 *
 * Author: Kevin deLeon (http://github.com/kevindeleon)
 */

// Receives JSON object returned by get_most_recent from get-tweets.php
function display_tweets(tweets) {
    var statusHTML = "";
    jQuery.each(tweets, function(i, tweet) {
        //let's check to make sure we actually have a tweet
        if (tweet.text !== undefined) {
            var username = tweet.user.screen_name;
            //let's grab the tweet, and do some housekeeping to display it properly
            var status = tweet.text.replace(/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;'">\:\s\<\>\)\]\!])/g, function(url) {
                return '<a href="'+url+'">'+url+'</a>';
            }).replace(/\B@([_a-z0-9]+)/ig, function(reply) {
                return  reply.charAt(0)+'<a href="http://twitter.com/'+reply.substring(1)+'">'+reply.substring(1)+'</a>';
            });
            statusHTML = '<p><span>'+status+'</span> <a style="font-size:85%" href="http://twitter.com/'+username+'/statuses/'+tweet.id_str+'">'+relative_time(tweet.created_at)+'</a></p>';
            //remove the loader
            jQuery('.tweet-loader').remove();
            //display tweet(s)
            jQuery('#twitter_update_list').append(statusHTML);  
        }
    });
}

// taken from Twitter's blogger.js
// Makes our "x time ago" data prettier for display
function relative_time(time_value) {
  var values = time_value.split(" ");
  time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
  var parsed_date = Date.parse(time_value);
  var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
  var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
  delta = delta + (relative_to.getTimezoneOffset() * 60);

  if (delta < 60) {
    return 'less than a minute ago';
  } else if(delta < 120) {
    return 'about a minute ago';
  } else if(delta < (60*60)) {
    return (parseInt(delta / 60)).toString() + ' minutes ago';
  } else if(delta < (120*60)) {
    return 'about an hour ago';
  } else if(delta < (24*60*60)) {
    return 'about ' + (parseInt(delta / 3600)).toString() + ' hours ago';
  } else if(delta < (48*60*60)) {
    return '1 day ago';
  } else {
    return (parseInt(delta / 86400)).toString() + ' days ago';
  }
}
