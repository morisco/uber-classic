<?
if(isset($_REQUEST['action'])){
	$user_longitude = $_REQUEST['user_longitude'];
	$user_latitude  = $_REQUEST['user_latitude'];
	$destination_longitude = $_REQUEST['destination_longitude'];
	$destination_latitude  = $_REQUEST['destination_latitude'];
	$product_id = $_REQUEST['product_id'];
	$search_term = $_REQUEST['query'];
	$venue_id = $_REQUEST['venue_id'];
	$radius = $_REQUEST['radius'];
	$max_price = $_REQUEST['max_price'];

	switch($_REQUEST['action']){
		case 'get_products':
			if($user_longitude && $user_latitude){
				getProducts($user_longitude, $user_latitude);			
			}
		break;
		case 'get_prices':
			// echo 'start_long' . $user_longitude .'<br/>';
			// echo 'start_lat' . $user_latitude .'<br/>';
			// echo 'dest_long' . $destination_longitude . '<br/>';
			// echo 'dest_lat' . $destination_latitude . '<br/>';
			$price_to_venue = getPrices($user_longitude, $user_latitude, $destination_longitude, $destination_latitude);
			print_r(json_encode($price_to_venue));
		break;
		case 'get_times':
			getTimes($user_longitude, $user_latitude, $product_id);
		break; 
		case 'search_foursquare':
			if($venue_id){
				$foursquare_url = 'https://api.foursquare.com/v2/venues/'.$venue_id;
				$foursquare_data = '?client_id=WTOCBAXWSLCPNIR4RIEPMGSKAGOF2YFWPE5W2GBLWKO5NRNJ&client_secret=K3S4LHTYSIR1YWWSGRFZ0A5DIST5H1C3XM55IVXBLEVTDR0O';
				$foursquare_data .= '&v=20140806&m=foursquare';	
				$foursquare_results = fourSquareAPI($foursquare_url, $foursquare_data);
				$return_data = $foursquare_results->response->venue;
				$encoded_data =json_encode($return_data);
				print_r($encoded_data);
			} else if($radius){
				$foursquare_url = 'https://api.foursquare.com/v2/venues/explore';
				$foursquare_data = '?ll=' . $user_latitude . ','. $user_longitude;
				$foursquare_data .= '&radius='.$radius;
				$foursquare_data .= '&amp;section=drinks';
				$foursquare_data .= '&sortByDistance=1';
				$foursquare_data .= '&openNow=1';
				$foursquare_data .= '&client_id=WTOCBAXWSLCPNIR4RIEPMGSKAGOF2YFWPE5W2GBLWKO5NRNJ&client_secret=K3S4LHTYSIR1YWWSGRFZ0A5DIST5H1C3XM55IVXBLEVTDR0O';
				$foursquare_data .= '&v=20140806&m=foursquare';	
				$foursquare_results = fourSquareAPI($foursquare_url, $foursquare_data);
				$return_data = $foursquare_results->response->groups[0];
				$check_items = array_reverse($return_data->items);
				$encoded_data = json_encode($return_data);
				$keep_checking = true;
				for($i=0; $i < count($check_items); $i++){
					$venue = $check_items[$i]->venue;
					$venueLat = $venue->location->lat;
					$venueLon = $venue->location->lng;
					if($keep_checking){
						$price_to_venue = getPrices($user_longitude, $user_latitude, $venueLon, $venueLat);
						if(intval($price_to_venue->prices[0]->high_estimate) > intval($max_price)){
						} else{
							$keep_checking = false;
						}	
					} else{
						$final_items[] = $check_items[$i];
					}
				}
				$final_items = array_reverse($final_items);
				print_r(json_encode($final_items));
				return;
			} else{
				$foursquare_url = 'https://api.foursquare.com/v2/venues/search';
				$foursquare_data = '?ll=' . $user_latitude . ','. $user_longitude;
				$foursquare_data .= '&radius=10000';
				$foursquare_data .= '&categoryId=4d4b7105d754a06376d81259';
				$foursquare_data .= '&query='.$search_term;
				$foursquare_data .= '&client_id=WTOCBAXWSLCPNIR4RIEPMGSKAGOF2YFWPE5W2GBLWKO5NRNJ&client_secret=K3S4LHTYSIR1YWWSGRFZ0A5DIST5H1C3XM55IVXBLEVTDR0O';
				$foursquare_data .= '&v=20140806&m=foursquare';	
				$foursquare_results = fourSquareAPI($foursquare_url, $foursquare_data);
				$return_data = $foursquare_results->response->venues;
				$encoded_data =json_encode($return_data);
				print_r($encoded_data);
			}
			
		break;
	}
} else{
	return 'nope!';
}

function getProducts($user_longitude, $user_latitude){
	$ch = curl_init();
	$headr = array();
	$headr[] = 'Content-length: 0';
	$headr[] = 'Content-type: application/json';
	$headr[] = 'Authorization: Token IEq8T9sAZpAafXEHJ58amGGOiKPcsmmMhOzg86ku';
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
	curl_setopt($ch, CURLOPT_URL, 'https://api.uber.com/v1/products?latitude='.$user_latitude.'&longitude='.$user_longitude);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	curl_close($ch);
	$output_data = json_decode($data);
	$final_products = array();

	$ch_time = curl_init();
	$headr_time = array();
	$headr_time[] = 'Content-length: 0';
	$headr_time[] = 'Content-type: application/json';
	$headr_time[] = 'Authorization: Token IEq8T9sAZpAafXEHJ58amGGOiKPcsmmMhOzg86ku';
	curl_setopt($ch_time, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch_time, CURLOPT_HTTPHEADER,$headr_time);
	curl_setopt($ch_time, CURLOPT_URL, 'https://api.uber.com/v1/estimates/time?start_latitude='.$user_latitude.'&start_longitude='.$user_longitude);
	curl_setopt($ch_time, CURLOPT_SSL_VERIFYPEER, false);
	$times = curl_exec($ch_time);
	curl_close($ch_time);
	$decoded_times = json_decode($times);

	foreach($output_data->products as $product){
		$product->description = str_replace('and Boro-Taxi ','',$product->description);
		switch($product->display_name){
			case 'uberT':
			$product->image = 'app/img/uber-taxi.png';
			break;
			case 'uberX':
			$product->image = 'app/img/uber-x.png';
			break;
			case 'uberXL':
			$product->image = 'app/img/uber-xl.png';
			break;
			case 'UberSUV':
			$product->image = 'app/img/uber-suv.png';
			break;
			case 'UberBLACK':
			$product->image = 'app/img/uber-black.png';
			break;
		}
		foreach($decoded_times->times as $time){
			if($time->product_id == $product->product_id){
				$minutes = ceil($time->estimate / 60);
				$product->estimatedTime = $time->estimate;
				$product->minutes = $minutes;
				$final_products[] = $product;
			}
		}
	}
	print_r( json_encode($final_products) );
}

function getTimes($user_longitude, $user_latitude, $product_id){
	$ch = curl_init();
	$headr = array();
	$headr[] = 'Content-length: 0';
	$headr[] = 'Content-type: application/json';
	$headr[] = 'Authorization: Token IEq8T9sAZpAafXEHJ58amGGOiKPcsmmMhOzg86ku';
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
	curl_setopt($ch, CURLOPT_URL, 'https://api.uber.com/v1/estimates/time?start_latitude='.$user_latitude.'&start_longitude='.$user_longitude.'&product_id='.$product_id);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$data = curl_exec($ch);

	curl_close($ch);
	return json_decode($data);
}

function getPrices($start_longitude, $start_latitude, $end_longitude, $end_latitude){
	// echo 'start_long' . $start_longitude .'<br/>';
	// echo 'start_lat' . $start_latitude .'<br/>';
	// echo 'dest_long' . $end_longitude . '<br/>';
	// echo 'dest_lat' . $end_latitude . '<br/>';
	// echo 'https://api.uber.com/v1/estimates/price?start_latitude='.$user_latitude.'&start_longitude='.$user_longitude.'&end_latitude='.$destination_latitude.'&end_longitude='.$destination_longitude;
	$ch = curl_init();
	$headr = array();
	$headr[] = 'Content-length: 0';
	$headr[] = 'Content-type: application/json';
	$headr[] = 'Authorization: Token 5jKc-uxfLRGpa27CWxucHfZTxG4Cue6cjZNShhVh';
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
	curl_setopt($ch, CURLOPT_URL, 'https://api.uber.com/v1/estimates/price?start_latitude='.$start_latitude.'&start_longitude='.$start_longitude.'&end_latitude='.$end_latitude.'&end_longitude='.$end_longitude);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);
	curl_close($ch);
	return json_decode($data);
}

function fourSquareAPI($url, $data){
	$ch = curl_init();
	$headr = array();
	$headr[] = 'Content-length: 0';
	$headr[] = 'Content-type: application/json';
	$headr[] = 'Authorization: Token 5jKc-uxfLRGpa27CWxucHfZTxG4Cue6cjZNShhVh';
	$search_url = $url;
	$search_url .= $data;
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
	curl_setopt($ch, CURLOPT_URL, $search_url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$data = curl_exec($ch);

	curl_close($ch);
	$decoded_data = json_decode($data);
	return $decoded_data;
}
?>