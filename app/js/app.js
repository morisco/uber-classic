(function() {

	"use strict";

	window.UberApp = window.UberApp || {
		Models 		: {},
		Collections : {},
		Views 		: {},
		Details 	: {}
	}

	UberApp.Models.Product = Backbone.Model.extend({

		idAttribute: 'product_id',

		defaults: {
			'surging' : '',
			'request_url' : false
		}

	});

	UberApp.Models.Venue = Backbone.Model.extend({

		idAttribute: 'venue_id'

	});

	UberApp.Collections.Products = Backbone.Collection.extend({

		model : UberApp.Models.Product,

		url : 'library/get_data.php',

		initialize : function(){
			// _.bindAll(this, 'loadProducts');
			// this.loadProducts();
		},

		parse : function(models){
			_.each(models,function(model,index){
				model['description'] = model['description'].replace('and Boro-Taxi ', '');
				if(model['display_name'] == 'uberT'){
					model['image'] = 'app/img/taxi.png';
				}
			});

		}
	});

	UberApp.Collections.FourSquareSearch = Backbone.Collection.extend({
		model : UberApp.Models.Venue,

		url : 'library/get_data.php',

		parse : function(models){
			var self = this;
			if(!models){

			} else if(models['id']){
				models.location_address = models['location']['address'];
				models.location_city = models['location']['city'];
				models.location_formatted = models['location']['address'] + ' ' + models['location']['city'] + ',' + models['location']['state'] + ' ' + models['location']['postalCode'];
			} else if(typeof(models[0]['venue']) == 'object'){
				_.each(models,function(model,index){
					for(var key in model.venue) {
						model[key] = model.venue[key];
					}
					model.location_address = model['location']['address'];
					model.location_city = model['location']['city'];
					model.location_formatted = model['location']['address'] + ', ' + model['location']['city'] + ', ' + model['location']['state'] + ' ' + model['location']['postalCode'];
					model.request_url = 'uber://?client_id=IEq8T9sAZpAafXEHJ58amGGOiKPcsmmMhOzg86ku'+
						'&action=setPickup&pickup=my_location'+
						'&dropoff[latitude]='+ model['location']['lat'] +
						'&dropoff[longitude]='+ model['location']['lng'] +
						'&dropoff[nickname]=' + model['name'] + 
						'&dropoff[formatted_address]=' +  encodeURIComponent(model.location_formatted) +
						'&product_id=a1111c8c-c720-46c3-8534-2fcdd730040d';
				});
			} else if(models.length > 1){
				_.each(models,function(model,index){
					model.location_address = model['location']['address'];
					model.location_city = model['location']['city'];
					model.location_formatted = model['location']['address'] + ' ' + model['location']['city'] + ', ' + model['location']['state'] + ' ' + model['location']['postalCode'];
					model.request_url = 'uber://?client_id=IEq8T9sAZpAafXEHJ58amGGOiKPcsmmMhOzg86ku'+
						'&action=setPickup&pickup=my_location'+
						'&dropoff[latitude]='+ model['location']['lat'] +
						'&dropoff[longitude]='+ model['location']['lng'];
				});
			}  else if(models.length == 1){
				self.updateModel = models[0];
				models[0].location_address = self.updateModel['location']['address'];
				models[0].location_city = self.updateModel['location']['city'];
				models[0].location_formatted = model['location']['address'] + ' ' + model['location']['city'] + ', ' + model['location']['state'] + ' ' + model['location']['postalCode'];
				models = models[0];
			}
			return models;
		}
	});


	UberApp.Views.Products = Backbone.View.extend({

		el : '#products',

		initialize: function(){
			_.bindAll(this,'getLocation', 'foundLocation', 'fetchProducts', 'checkSurge');
			this.getLocation();
		},

		getLocation : function(){
			if(navigator.geolocation){
				navigator.geolocation.getCurrentPosition(this.foundLocation, this.appFail);
			} else{
				this.appFail();
			}
		},

		foundLocation : function(location){
			UberApp.userLat = location.coords.latitude;
			UberApp.userLon = location.coords.longitude;
			UberApp.Collections.AvailableProducts = new UberApp.Collections.Products();
			this.fetchProducts();
			this.checkSurge();

		},

		fetchProducts : function(){
			var self = this;
			UberApp.Collections.AvailableProducts.fetch({
				parse: false,
				data: {
					action : 'get_products',
					user_longitude : UberApp.userLon,
					user_latitude  : UberApp.userLat
				},
				remove : false,
				success : function(response){
					_.each(response.models,function(product,index){
						var productView = new UberApp.Views.Product({model:product});
						product.set({'column': (10/response.models.length) })
						self.$('ul').append(productView.render());
						if((index+1) == response.models.length){
							self.$('ul').append('<div class="cb"></div>');
							$('.preloader').fadeOut(function(){
								$('.load_text').html('Just a second homie, I\'m looking.');
							});
						}
					});
				}
			});
		},

		checkSurge : function(){
			$.ajax({
				url : 'library/get_data.php',
				type : 'POST',
				data : {
					action : 'get_prices',
					user_longitude : UberApp.userLon,
					user_latitude  : UberApp.userLat,
					destination_longitude : UberApp.userLon,
					destination_latitude : UberApp.userLat
				},
				success : function(response){
					response = $.parseJSON(response);
					if(response['prices'].length){
						if(response['prices'][0]['surge_multiplier'] > 1){
							$('.surging .surge-multiplier').text(response['prices'][0]['surge_multiplier'] + 'X');
							$('.surging').animate({'height':'80'},300,function(){
								$(this).animate({'opacity':1});
							});
						}
					}
				}
			});
		},

		addProduct : function(product){
		},

		appFail : function(){
			alert('Looks like somethign went wrong. Sorry about that, Bro!');
		}
	});

	UberApp.Views.Product = Backbone.View.extend({

		template: _.template( $('#template-product').html() ),

		initialize: function(){
			this.render();
		},

		render : function(){
			return this.template( this.model.toJSON() );
		}
	});

	UberApp.Views.AddressSearch = Backbone.View.extend({
		el: '#address_lookup',

		events : {
			'click #submit_address' : 'getGeocode',
			'click #address_correct li' : 'correctAddress'
		},

		initialize : function(){
			_.bindAll(this,'getGeocode', 'getPrices', 'correctAddress');
		},

		getGeocode : function(event){
			event.preventDefault();
			var self = this;
			self.address = this.$('#destination_address').val();

			self.latLng = new google.maps.LatLng(UberApp.userLat, UberApp.userLon);
			self.circle = new google.maps.Circle({center: self.latLng, radius: 10000});
			self.bounds = self.circle.getBounds();

			this.geocoder = new google.maps.Geocoder();
			this.geocoder.geocode( { 'address': self.address, 'bounds': self.bounds}, function(results, status) {
				$('.products .price').remove();
				if(status == 'OK' && results.length == 1){
					$('#destination_address').val(results[0]['formatted_address']);
					$('.alert').fadeOut();
					var lat = results[0].geometry.location.lat();
					var lon = results[0].geometry.location.lng();
					self.getPrices(lat, lon);
					$('.thumbnail').animate({'opacity':1});
				} else if( status == 'OK' && results.length > 1){
					$('.alert').remove();
					$.each(results,function(index,address){
						$('#pick_your_poison').append('<li>'+address.formatted_address+'</li>');
						if(results.length == (index + 1)){
							$('#pick_your_poison').fadeIn();
							$('#pick_your_poison li').on('click',self.correctAddress);
						}
					});
				} else{
					$('.alert').remove();
					$('#search_options').append('<div class="alert alert cb" role="alert">Comeon man, be more specific!</div>');
					$('.thumbnail').animate({'opacity':0.5});
				}
			});
		},

		correctAddress : function(event){
			$('#pick_your_poison').animate({'opacity':0},300,function(){
				$(this).animate({'height':0},300,function(){
					$(this).hide().removeAttr('style');
				});
			});
			$('.alert').remove();
			this.newAddress = $(event.target).text();
			this.$('#destination_address').val(this.newAddress);
			this.$('#submit_address').click();

		},

		getPrices : function(lat, lon){
			$.ajax({
				url : 'library/get_data.php',
				type : 'POST',
				data : {
					action : 'get_prices',
					user_longitude : UberApp.userLon,
					user_latitude  : UberApp.userLat,
					destination_longitude : lon,
					destination_latitude : lat
				},
				success : function(response){
					response = $.parseJSON(response);
					$('body,html').animate({scrollTop : $('#products').offset().top});
					if(response.prices){
						$.each(response.prices,function(index,price){
							$('.products li[data-product-id="'+price.product_id+'"] .product-icon').after('<span class="price label label-success fr">' + price.estimate + '</span>');
						});
					} else{
						$('.alert').remove();
						$('#search_options').append('<div class="alert alert cb" role="alert">Bro, '+response.message+'</div>');
						$('.thumbnail').animate({'opacity':0.5});
					}

				}
			});
		},

	});

	UberApp.Views.BarSearch = Backbone.View.extend({
		el : '#bar_lookup',

		events : {
			'click #submit_bar' : 'searchBar'
		},

		template: _.template( $('#template-foursquareFail').html() ),

		initialize : function(){
			_.bindAll(this,'searchBar', 'updateBar', 'fetchVenues');

			UberApp.Collections.SearchBar = new UberApp.Collections.FourSquareSearch();
			this.collection = UberApp.Collections.SearchBar;
		},

		searchBar : function(event){
			event.preventDefault();
			var self = this;
			self.barName = $('#bar_address').val();
			var dataObject =  {
				action: 'search_foursquare',
				user_latitude : UberApp.userLat,
				user_longitude : UberApp.userLon,
				query : encodeURIComponent(self.barName)
			};
			this.fetchVenues(dataObject);
		},

		updateBar : function(event){
			event.preventDefault();
			$('#pick_your_poison').animate({'opacity':0},300,function(){
				$(this).animate({'height':0},300,function(){
					$(this).hide().removeAttr('style');
				});
			});
			var self = this;
			var dataObject =  {
				action: 'search_foursquare',
				user_latitude : UberApp.userLat,
				user_longitude : UberApp.userLon,
				venue_id : $(event.target).attr('data-venue-id')
			};
			this.fetchVenues(dataObject);
		},

		fetchVenues : function(venueData){
			var self = this;
			venueData = venueData;
			this.collection.fetch({
				parse: true,
				data: venueData,
				remove : true,
				success : function(response){
					$('.alert').remove();
					if(response.length == 1){
						$('#destination_address').val(self.collection.at(0).get('location_formatted'));
						$('#submit_address').click();
					} else if(response.length > 1){
						$('#pick_your_poison').empty();
						_.each(response.models,function(model,index){
							$('#pick_your_poison').append( self.template( model.toJSON() ));
							if(response.models.length == (index+1)){
								$('#pick_your_poison').fadeIn();
								$('#pick_your_poison li').on('click',self.updateBar);
							}
						});
					} else{
						$('.alert').remove();
						$('#search_options').append('<div class="alert alert cb" role="alert">Bro, is that place even real?</div>');
						$('.thumbnail').animate({'opacity':0.5});
					}
				}
			});
		}
	});

	UberApp.Views.PriceSearch = Backbone.View.extend({
		el : '#price_lookup',

		events : {
			'click #submit_price' : 'priceSearch',
			'click .minus, .plus' : 'adjustPrice'
		},

		template: _.template( $('#template-foursquareFail').html() ),

		initialize : function(){
			_.bindAll(this,'priceSearch', 'updateBar', 'fetchVenues', 'adjustPrice');
			var self = this;
			UberApp.Collections.SearchBar = new UberApp.Collections.FourSquareSearch();
			this.collection = UberApp.Collections.SearchBar;
		},

		adjustPrice : function(event){
			var self = this;
			if($(event.target).hasClass('minus')){
				self.currentVal = self.$('input').val();
				this.minusVal = parseInt(self.currentVal.substring(1,3)) - 1;
				this.$('input').val('$' + this.minusVal);
			} else{
				self.currentVal = self.$('input').val();
				this.plusVal = parseInt(self.currentVal.substring(1,3)) + 1;
				this.$('input').val('$'+ this.plusVal);
			}
		},

		priceSearch : function(event){
			event.preventDefault();
			var self = this;
			$(".preloader").fadeIn(250);
			self.price = $('#price_search').val().substring(1,3);
			self.maxMiles = Math.ceil(self.price/2);
			self.maxMeters = Math.ceil(self.maxMiles * 1609.34);
			var dataObject =  {
				action: 'search_foursquare',
				user_latitude : UberApp.userLat,
				user_longitude : UberApp.userLon,
				radius : self.maxMeters,
				max_price : self.price
			};
			this.fetchVenues(dataObject);
		},

		updateBar : function(event){
			if(!isMobile){
				event.preventDefault();
				var self = this;
				var dataObject =  {
					action: 'search_foursquare',
					user_latitude : UberApp.userLat,
					user_longitude : UberApp.userLon,
					venue_id : $(event.target).attr('data-venue-id')
				};
				this.fetchVenues(dataObject);
			}
			
		},

		fetchVenues : function(venueData){
			var self = this;
			venueData = venueData;
			this.collection.fetch({
				timeout: 50000,
				parse: true,
				data: venueData,
				remove : true,
				success : function(response){
					$('#pick_your_poison').empty();
					$('.alert').remove();
					$(".preloader").fadeOut(250);
					if(response.length == 1){
						$('#destination_address').val(self.collection.at(0).get('location_formatted'));
						$('#submit_address').click();
					} else if(response.length > 1){
						_.each(response.models,function(model,index){
							$('#pick_your_poison').append( self.template( model.toJSON() ));
							if(response.models.length == (index+1)){
								$('#pick_your_poison').fadeIn();
								$('#pick_your_poison li').on('click',self.updateBar);
							}
						});
					} else{
						$('.alert').remove();
						$('#search_options').append('<div class="alert cb" role="alert">Duuuuuude... we can\'t afford to go out tonight. Bummer, dude.</div>');
						$('.thumbnail').animate({'opacity':0.5});
					}
				},

				error : function(response){
					console.log(response);
				}
			});
		}
	});


	window.isMobile = /iphone|ipod|ipad|android|blackberry|opera mini|opera mobi|skyfire|maemo|windows phone|palm|iemobile|symbian|symbianos|fennec/i.test(navigator.userAgent.toLowerCase());

	UberApp.Views.ProductsView = new UberApp.Views.Products();
	UberApp.Views.AddressSearchView = new UberApp.Views.AddressSearch();
	UberApp.Views.BarSearchView = new UberApp.Views.BarSearch();
	UberApp.Views.PriceSearchView = new UberApp.Views.PriceSearch();


})();