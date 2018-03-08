<?php include("twitter.class.php"); ?>
<html>
<head>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	<script src="twitter.js"></script>
</head>
<body>

<!-- Our div that will contain our tweets -->
<div id="twitter_update_list"><span class="tweet-loader">Loading tweets...</span></div>
 
<script type="text/javascript">
    //get JSON object from twitter
    var tweets = <?php echo GetTweets::get_most_recent('wesley_smits','120','true') ?>;
     
    //pass returned JSON object into display_tweets()
    display_tweets(tweets);
</script>

</body>
</html>
