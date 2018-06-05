<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

		<title>Group Office - <?php echo Site::controller()->getPageTitle(); ?></title>
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo Site::template()->getUrl(); ?>favicon.ico">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

		<!-- Optional theme -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

		<link rel="stylesheet" href="<?php echo Site::template()->getUrl(); ?>css/site.css">

		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

		<script type="text/javascript">
			$(function() {				
				$('a[href^="#"]').bind('click.smoothscroll',function (e) {
						e.preventDefault();
						var target = this.hash;
								$target = $(target);
						$('html, body').stop().animate({
								'scrollTop': $target.offset().top
						}, 1000, 'swing', function () {
								window.location.hash = target;
						});
				});
			});
		</script>

		<script src="<?php echo Site::file('lightbox/js/lightbox-2.6.min.js'); ?>"></script>
		<link href="<?php echo Site::file('lightbox/css/lightbox.css'); ?>" rel="stylesheet" />
	</head>

	<body>
		
		<?php 
		if(Site::model()->{"Google tracking code"}){
			
			preg_match("/[^\.\/]+\.[^\.\/]+$/", $_SERVER['SERVER_NAME'], $matches);
			$domain = $matches[0];
			
		?>
		
		<script>
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', '<?php echo Site::model()->{"Google tracking code"}; ?>', '<?php echo $domain; ?>');
			ga('send', 'pageview');

		</script>
		<?php
		}
		?>
		
		<div class="container" id="header">

			<div class="page-header">
				<h1><img src="<?php echo Site::file('images/groupoffice.gif'); ?>" alt="Group-Office" /> <small>manual  for administrators</small></h1>
			</div>

			<?php echo $content; ?>
			
			
			<div id="footer">
				Copyright Intermesh BV.<br /><a href="https://www.group-office.com">https://www.group-office.com</a>
			</div>

		</div>

	</body> 
</html>
