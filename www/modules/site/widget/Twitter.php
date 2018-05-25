<?php


namespace GO\Site\Widget;


class Twitter extends \GO\Site\Components\Widget {
	
	/**
	 * include retweets true, false
	 * 
	 * @var String 
	 */
	public $retweets="false";
	
	
	public $exclude_replies="true";
	
	/**
	 * Number of tweets
	 * @var int  
	 */
	public $limit=10;
	
	/**
	 * These are your keys/tokens/secrets provided by Twitter 
	 * dev.twitter.com -> create an app -> request oauth token. Then look at the 
	 * oAuth tab.
	 * 
	 * @var StringHelper 
	 */
	public $consumerKey='';
	
	/**
	 * These are your keys/tokens/secrets provided by Twitter 
	 * dev.twitter.com -> create an app -> request oauth token. Then look at the 
	 * oAuth tab.
	 * 
	 * @var StringHelper 
	 */
	public $consumerSecret='';
	
	/**
	 * These are your keys/tokens/secrets provided by Twitter 
	 * dev.twitter.com -> create an app -> request oauth token. Then look at the 
	 * oAuth tab.
	 * 
	 * @var StringHelper 
	 */
	public $accessToken='';
	
	/**
	 * These are your keys/tokens/secrets provided by Twitter 
	 * dev.twitter.com -> create an app -> request oauth token. Then look at the 
	 * oAuth tab.
	 * 
	 * @var StringHelper 
	 */
	public $accessTokenSecret='';
	
	
	/**
	 * Twitter username (ex. GroupOffice)
	 * 
	 * @var StringHelper 
	 */
	public $screenName="";
	
	/**
	 * Display user time line by default. Set to false for home timeline.
	 * @var boolean 
	 */
	public $userTimeLine=true;
	
	
	/**
	 * Tweets are cached to improve page load speed. 
	 * Cache lifetime in seconds. Set to 0 to disable.
	 * 
	 * @var int 
	 */
	public $cacheLifeTime=3600;
	
	
//	public $template = "<li><a id=\"twitter-account-name\" href=\"https://twitter.com/{user:screen_name}\">@{user:screen_name}</a>{text}</li>\n";
/**
 *["created_at"]=>
    string(30) "Wed Jul 24 15:57:45 +0000 2013"
    ["id"]=>
    int(360066280738398209)
    ["id_str"]=>
    string(18) "360066280738398209"
    ["text"]=>
    string(63) "Great German article about Group-Office:
http://t.co/zy3JDoVTEC"
    ["source"]=>
    string(62) "<a href="http://www.linkedin.com/" rel="nofollow">LinkedIn</a>"
    ["truncated"]=>
    bool(false)
    ["in_reply_to_status_id"]=>
    NULL
    ["in_reply_to_status_id_str"]=>
    NULL
    ["in_reply_to_user_id"]=>
    NULL
    ["in_reply_to_user_id_str"]=>
    NULL
    ["in_reply_to_screen_name"]=>
    NULL
    ["user"]=>
    object(stdClass)#38 (38) {
      ["id"]=>
      int(20156212)
      ["id_str"]=>
      string(8) "20156212"
      ["name"]=>
      string(12) "Group-Office"
      ["screen_name"]=>
      string(11) "GroupOffice"
      ["location"]=>
      string(15) "The Netherlands"
      ["description"]=>
      string(156) "Take your office online. Share projects, calendars, files and e-mail online with co-workers and clients. Group-Office takes collaboration to the next level!"
      ["url"]=>
      string(22) "http://t.co/rjhKN7Xav7"
      ["entities"]=>
      object(stdClass)#39 (2) {
        ["url"]=>
        object(stdClass)#40 (1) {
          ["urls"]=>
          array(1) {
            [0]=>
            object(stdClass)#41 (4) {
              ["url"]=>
              string(22) "http://t.co/rjhKN7Xav7"
              ["expanded_url"]=>
              string(27) "http://www.group-office.com"
              ["display_url"]=>
              string(16) "group-office.com"
              ["indices"]=>
              array(2) {
                [0]=>
                int(0)
                [1]=>
                int(22)
              }
            }
          }
        }
        ["description"]=>
        object(stdClass)#42 (1) {
          ["urls"]=>
          array(0) {
          }
        }
      }
      ["protected"]=>
      bool(false)
      ["followers_count"]=>
      int(114)
      ["friends_count"]=>
      int(43)
      ["listed_count"]=>
      int(5)
      ["created_at"]=>
      string(30) "Thu Feb 05 15:25:52 +0000 2009"
      ["favourites_count"]=>
      int(0)
      ["utc_offset"]=>
      int(7200)
      ["time_zone"]=>
      string(9) "Amsterdam"
      ["geo_enabled"]=>
      bool(true)
      ["verified"]=>
      bool(false)
      ["statuses_count"]=>
      int(83)
      ["lang"]=>
      string(2) "en"
      ["contributors_enabled"]=>
      bool(false)
      ["is_translator"]=>
      bool(false)
      ["profile_background_color"]=>
      string(6) "C0DEED"
      ["profile_background_image_url"]=>
      string(47) "http://a0.twimg.com/images/themes/theme1/bg.png"
      ["profile_background_image_url_https"]=>
      string(49) "https://si0.twimg.com/images/themes/theme1/bg.png"
      ["profile_background_tile"]=>
      bool(false)
      ["profile_image_url"]=>
      string(64) "http://a0.twimg.com/profile_images/1010593666/go-icon_normal.gif"
      ["profile_image_url_https"]=>
      string(66) "https://si0.twimg.com/profile_images/1010593666/go-icon_normal.gif"
      ["profile_link_color"]=>
      string(6) "0084B4"
      ["profile_sidebar_border_color"]=>
      string(6) "C0DEED"
      ["profile_sidebar_fill_color"]=>
      string(6) "DDEEF6"
      ["profile_text_color"]=>
      string(6) "333333"
      ["profile_use_background_image"]=>
      bool(true)
      ["default_profile"]=>
      bool(true)
      ["default_profile_image"]=>
      bool(false)
      ["following"]=>
      bool(false)
      ["follow_request_sent"]=>
      bool(false)
      ["notifications"]=>
      bool(false)
    }
    ["geo"]=>
    NULL
    ["coordinates"]=>
    NULL
    ["place"]=>
    NULL
    ["contributors"]=>
    NULL
    ["retweet_count"]=>
    int(0)
    ["favorite_count"]=>
    int(0)
    ["entities"]=>
    object(stdClass)#43 (4) {
      ["hashtags"]=>
      array(0) {
      }
      ["symbols"]=>
      array(0) {
      }
      ["urls"]=>
      array(1) {
        [0]=>
        object(stdClass)#44 (4) {
          ["url"]=>
          string(22) "http://t.co/zy3JDoVTEC"
          ["expanded_url"]=>
          string(21) "http://lnkd.in/9QJACa"
          ["display_url"]=>
          string(14) "lnkd.in/9QJACa"
          ["indices"]=>
          array(2) {
            [0]=>
            int(41)
            [1]=>
            int(63)
          }
        }
      }
      ["user_mentions"]=>
      array(0) {
      }
    }
    ["favorited"]=>
    bool(false)
    ["retweeted"]=>
    bool(false)
    ["possibly_sensitive"]=>
    bool(false)
    ["lang"]=>
    string(2) "en"
  }
 * @var type 
 */
	public $template = '<div>
	<a class="twitter-account-name" href="https://twitter.com/{user:screen_name}">@{user:screen_name}</a>
	<p class="date">{created_at}</p>
	<p class="tweet">{text}</p>
	<p class="interact">
	<a href="https://twitter.com/intent/tweet?in_reply_to={id}" class="twitter_reply_icon">Reply</a>
	<a href="https://twitter.com/intent/retweet?tweet_id={id}" class="twitter_retweet_icon">Retweet</a>
	<a href="https://twitter.com/intent/favorite?tweet_id={id}" class="twitter_fav_icon">Favorite</a>
	</p>
	</div>';
	
	public function render() {


		require_once(\GO::modules()->site->path.'widget/twitter/codebird.php');

		$cacheKey = $this->consumerKey.':'.$this->accessToken.':'.$this->userTimeLine.':'.$this->retweets;
		
		if(!$this->cacheLifeTime || !($tweets = \GO::cache()->get($cacheKey))){	
			//Get authenticated
			\Codebird\Codebird::setConsumerKey($this->consumerKey, $this->consumerSecret);

			$cb = \Codebird\Codebird::getInstance();
			$cb->setToken($this->accessToken, $this->accessTokenSecret);

			//These are our params passed in
			$params = array(
					'screen_name' => $this->screenName,
					'count' => $this->limit,
					'include_rts' => $this->retweets,
					'exclude_replies'=>$this->exclude_replies
			);

			//tweets returned by Twitter	
			$tweets = $this->userTimeLine ? (array) $cb->statuses_userTimeline($params) : (array) $cb->statuses_homeTimeline($params);
			
			\GO::cache()->set($cacheKey, $tweets,$this->cacheLifeTime);
		}

		$html = '';
		foreach($tweets as $tweet){
			if(is_object($tweet)){
				$str = $this->template;
				foreach($tweet as $key=>$value){
					if(!is_object($value)){
						
						if($key=='text')
							$value = \GO\Base\Util\StringHelper::text_to_html($value);
						
						if($key=='created_at'){
							$value = strtotime($value);
							
							if($value){
								$value = date('Y-m-d G:i', $value);
							}
						}
						
						$str = str_replace('{'.$key.'}', $value, $str); 
					}else
					{
						$value=(array) $value;
						foreach($value as $subKey=>$subValue){
							if(is_string($subValue))
								$str = str_replace('{'.$key.':'.$subKey.'}', $subValue, $str); 
						}
					}
				}
			
				$html .= $str;
			}
		}
		
		return $html;
	}

}
