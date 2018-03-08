<?php

/**
 * GetTweets - Used with Codebird-php to pull tweets from Twitter
 * https://github.com/kevindeleon/get-tweets
 *
 * @author Kevin deLeon <https://github.com/kevindeleon/>
 * @copyright 2013 Kevin deLeon <https://github.com/kevindeleon/>
 *
 * Licensed under the MIT license.
 * https://github.com/kevindeleon/get-tweets/blob/master/LICENSE
 */


/**
 * Class GetTweets
 */
class GetTweets {

	/**
	 * Gets most recent tweets
	 * @param String twitter username (ex. kevindeleon)
	 * @param String number of tweets
	 * @param String include retweets true, false
	 * @return JSON encoded tweets
	 */
	static public function get_most_recent($screen_name, $count, $retweets = NULL)
	{
		//let's include codebird, as it's going to be doing the oauth lifting for us
		require_once('codebird.php');

		//These are your keys/tokens/secrets provided by Twitter
		// dev.twitter.com -> create an app -> request oauth token. Then look at the oAuth tab.
		$CONSUMER_KEY = '3soJowYY3RvGLyhg4xpoJA';
		$CONSUMER_SECRET = 'ihH6rRYbNB3UszzhDdTKSVez3HCf67KDG0ufE69Oqw';
		$ACCESS_TOKEN = '337592867-PbwVrwFziRbjQvQEFtFpBDmmZs5xt1C1V2fghn8A';
		$ACCESS_TOKEN_SECRET = 'dNt44s1rmu8jh3bfmiyefsJkIIKHUKRYr33Jfx4YdM';

		//Get authenticated
		\Codebird\Codebird::setConsumerKey($CONSUMER_KEY, $CONSUMER_SECRET);
		$cb = \Codebird\Codebird::getInstance();
		$cb->setToken($ACCESS_TOKEN, $ACCESS_TOKEN_SECRET);
		//These are our params passed in
		$params = array(
			'screen_name' => $screen_name,
			'count' => $count,
			'rts' => $retweets,
		);

		//tweets returned by Twitter
		//$tweets = (array) $cb->statuses_userTimeline($params);
		$tweets = (array) $cb->statuses_homeTimeline($params);

		//Let's encode it for our JS/jQuery
		return json_encode($tweets);
	}

}
