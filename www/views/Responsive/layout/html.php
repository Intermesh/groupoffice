<!doctype html>
<html lang="en">
	<head>
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!--    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>-->
		
		<!-- LOAD ANGULAR -->
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.0-beta.5/angular.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.0-beta.5/angular-route.js"></script>
		 <!--<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.2.8/angular.min.js"></script>-->
	
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!-- Latest compiled and minified JavaScript -->
<!--		<script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>-->
		
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

		<!-- Optional theme -->
<!--		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">-->

		

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
		
		<script src="/trunk/www/views/Responsive/resources/ui-bootstrap-tpls-0.10.0.js"></script>
		
		<script src="/trunk/www/views/Responsive/resources/GO.js"></script>
		<script src="/trunk/www/views/Responsive/resources/service/Alert.js"></script>
		
		<style>
			/* for bootstrap-ui */
			.nav, .pagination, .carousel, .panel-title a { cursor: pointer; }
		</style>
		
		<link rel="stylesheet" href="/trunk/www/views/Responsive/resources/css/main.css">
	</head>
	<body ng-app="GO">
	
		

		
		<div class="container">
			
			<div class="go-alerts">
				<alert ng-repeat="alert in alerts" type="alert.type" close="closeAlert($index)">{{ alert.msg }}</alert>
			</div>
			
			<?php if(false && GO::user()){ ?>

	<!-- Static navbar -->
	<div class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div class="navbar-collapse collapse">
				<ul id="go-module-menu" class="nav navbar-nav">
					
					<li><a href="<?php echo GO::url("email/responsive/load"); ?>">E-mail</a></li>
					<li><a href="<?php echo GO::url("email/responsive/load"); ?> ">Address book</a></li>
					
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="<?php echo GO::url('setttings/edit'); ?>"><span class="glyphicon glyphicon-user"></span> <?php echo GO::user()->name; ?></a></li>
					<li><a href="<?php echo GO::url('auth/logout'); ?>"><?php echo GO::t('logout'); ?></a></li>
				</ul>
			</div><!--/.nav-collapse -->
		</div><!--/.container-fluid -->
	</div>
			<?php } ?>
	
	<div id="goMessageContainer"></div>
	<div id="go-module-container">
		<?php echo $content; ?>
	</div>


</div> <!-- /container -->

    
		
		
<!--		<script src="/trunk/www/views/Responsive/resources/template.js"></script>
		<script src="/trunk/www/views/Responsive/resources/message.js"></script>-->
	</body>
</html>
