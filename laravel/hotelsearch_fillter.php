<script>   //  view jquery code 
$(document).ready(function(){
	$(".filter_data").change(function(){
		var datastring = $("#package_filter_form").serialize();
    $.ajax({
            url: '{!! URL::to("vacation/vacation_fillter") !!}',
            method:'get',
            data:datastring,
            beforeSend: function(){
            $('.ajax_loader_cls').css({'display':'block'});
	        },
            success: function(response){
                	$('.ajax_results_package').html(response);
                   	$('.ajax_loader_cls').css({'display':'none'});
                 }
            });
	});
});
</script>


<!--  php controller code -->
<?php  
public function vacationFillter(Request $request)
	{
		$query = Package::where('is_active', 'Y');
		if(isset($request->price))
		{
			$price = explode(';', $request->price);

			$min_price = $price[0];
			$max_Price = $price[1];
			$query = $query->whereBetween('basic_cost', [intval($min_price), intval($max_Price)]);
		}
		if(isset($request->vacation_type_array))
		{
			$query = $query->whereIn('vacation_type_id', $request->vacation_type_array);
		}
		if(isset($request->offer_types_array))
		{
			$query = $query->whereIn('offer_type_id', $request->offer_types_array);
		}

		if (isset($request->sort_by_price)) {
			$query = $query->orderBy('basic_cost',$request->sort_by_price);
		}
		// if (isset($request->sort_by_distance)) {
		// 		echo $this->fillter_location($request->sort_by_distance);
		// }
		$packages = $query->orderBy('basic_cost', 'desc')->paginate(10);

		if(!empty($packages))
		{
			foreach($packages as $package)
			{
				$package_id = $package->id;
				$images = PackageImages::where('package_id', $package_id)->orderBy('id', 'desc')->first();
				if(!empty($images))
				{
					$package['banner'] = $images->image;
				}
				


				$offer_types = OfferTypes::where('id', $package->offer_type_id)->first();
				if(!empty($offer_types))
				{
					$package['offer_type'] = $offer_types->title;
				}
				
			}
		}
		$packages_count = count($packages);
		return view('frontend.destinations.load_results',compact('packages'));

	}


	/* find location */

	// public function fillter_location($sort_by_distance)
	// {
	// 	if (!empty($sort_by_distance)) {
					
	// 	 $location = $this->getIpDetails();
 //          $address = explode(',', $location->loc);
 //          $latt = $address[0];    
 //          $longt = $address[1];
 //          $distance = 25.00;

 //          if (!empty($latt) && !empty($longt)) {
          		
 //          	$getlocationdata = DB::table('packages')->select('packages.destination_id','destinations.lattitude','destinations.longitude')
 //          										    ->join('destinations','destinations.id','=','packages.destination_id')
 //          										    ->WhereNull('packages.deleted_at') 
 //          	                                        ->get();
          	
 //          	foreach ($getlocationdata as $value) {
 //          			$lattitude = $value->lattitude;
 //          			$longitude = $value->longitude;


 //          	}
 //          }
          
 //            $hotels_data['hotels'] = Hotels::whereRaw(DB::raw('( 6367 * acos( cos( radians('.$latt.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longt.') ) + sin( radians('.$latt.') ) * sin( radians( latitude ) ) ) ) < '.$distance))
 //           ->get();

 //          $hotels_data['total'] = Hotels::whereRaw(DB::raw('( 6367 * acos( cos( radians('.$latt.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longt.') ) + sin( radians('.$latt.') ) * sin( radians( latitude ) ) ) ) < '.$distance))
 //          ->count();
	// 	}
	// }


?>