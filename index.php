<!DOCTYPE html>
<html>
<head>
<title>Uber Me Bro.</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="app/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="app/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
<link href="app/css/jquery.ui.min.css" rel="stylesheet" media="screen">
<link href="app/css/app.css" rel="stylesheet" media="screen">
<link href='http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700|Open+Sans:600italic,400,600' rel='stylesheet' type='text/css'>
<meta property="og:title" content="Uber Me, Bro!"/>
<meta property="og:description" content="Find out how much it costs to get to the party. Search by address, bar name or tell us how much you want to spend we'll give you a few options."/>
<meta property="og:url" content="http://ubermebro.com"/>
<meta property="og:site_name" content="Uber Me Bro"/>
<meta property="og:type" content="website"/>
<meta property="og:image" content="http://ubermebro.com/app/img/facebook-image.png"/>
</head>
<body>
	<div class="surging col-lg-12 col-sm-12 col-xs-12">
			<div class="surger">
				<img src="app/img/surging.png" alt="surging icon" class="surge-icon" />
				<div class="surge-message">Dude, looks like we're paying <span class="surge-multiplier"></span> tonight. Total ummer.</div>
			</div>
		</div>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="page-header">
				<h1>UBER ME, BRO</h1>
				<h2>An Uber Pricing App</h2>
			</div>
		</div>
		<div class="row-fluid">
			<div id="search_options">
				<div class="search col-lg-4 col-md-4 col-sm-4">
					<h3>Enter An Address</h3>
					<p>Tell us the address of your destination and we'll look it up. If we find a few places we think match, you can choose the right one.</p>
					<form id="address_lookup" class="col-lg-12">
						<div class="input_wrap">
							<input type="text" class="form-control col-lg-12 cb" name="destination_address" id="destination_address" placeholder="Wheres the sweet house party at?" id="inputSuccess2">
						</div>
						<button id="submit_address" type="submit" class='btn btn-primary btn-block doit'>Grab the 6-pack, Brah. Let's kick this pig.</button>
						<img src="app/img/google.png" alt="powered by google" />
					</form>

				</div>

				<div class="search col-lg-4 col-md-4 col-sm-4">
					<h3>Enter The Name of A Bar</h3>
					<p>We'll look up the bar and if we find a few results that are close, you can choose the right one. Click the right one and see the cost.</p>
					<form id="bar_lookup" class="col-lg-12">
						<div class="input_wrap">
							<input type="text" class="form-control col-lg-12" name="bar_address" id="bar_address" placeholder="You thinkin a bar tonight, bro?!" id="inputSuccess2">
						</div>
						<button id="submit_bar" type="submit" class='btn btn-primary btn-block doit'>Bro, this bar is dope. Let's go.</button>
						<img src="app/img/foursquare.png" alt="powered by foursquare" />
					</form>
				</div>

				<div class="search price-search col-lg-4 col-md-4 col-sm-4">
					<? // <div class="disable">Coming Soon!</div> ?>
					<h3>Set your Maxiumum Price</h3>
					<p>Set how much you'd like to spend and we'll tell you the places you can go for that price. Click on a place and see the cost.</p>
					<form id="price_lookup" class="col-lg-12">
						<div class="minus col-lg-2, col-xs-2">-</div>
						<input type="text" class="form-control col-lg-8 col-xs-8 offset-3" name="price_search" id="price_search" value="$20" id="inputSuccess2" />
						<div class="plus col-lg-2 col-xs-2">+</div>
						<button id="submit_price" type="submit" class='btn btn-primary btn-block doit'>Bro, I only got money for beers.</button>
						<img src="app/img/foursquare.png" alt="powered by foursquare" />
					</form>
				</div>
				<div class="cb"></div>
				<ul id="pick_your_poison" class="col-lg-12">
					<h3>Choose an option</h3>
				</ul>
			</div>
			

			<div id="products" class="products col-lg-12">
				<h3>Available Options</h3>
				<ul class="p0"></ul>
			</div>
		</div>
		<div class="row-fluid cb">
			<div class="col-lg-12 built-by"><a target="_blank" href="http://mikemorisco.com">Built By: MikeMo</a></div>
		</div>
	</div>

	<div class="preloader" style="display: block;">
      <div class="ballHolder">
        <div class="ball"></div>
        <div class="ball2"></div>
      </div>
      <div class="load_text">Sup, Bro? I'm finding you.</div>

    </div>



<? include('templates.php'); ?>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone-min.js"></script>
<script src="app/js/app.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-54099046-1', 'auto');
  ga('send', 'pageview');

</script>
<!-- <script src="app/lib/bootstrap.min.js"></script> -->

</body>
</html>