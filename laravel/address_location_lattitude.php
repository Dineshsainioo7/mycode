<?php    

GOOGLE_MAP_KEY=AIzaSyDD2D0ZGcuTgYXr6Ui3iOPYLPZykgitG6s  // env file 

 public function getLocation($address)
    {
        $g_key = env('GOOGLE_MAP_KEY');
        $prepAddr = str_replace(' ', '+', $address);
        $url = 'https://maps.google.com/maps/api/geocode/json?address=' .urlencode($prepAddr) . '&sensor=false&key='.$g_key;
        $geocode = file_get_contents($url);
        return json_decode($geocode);
    }
    /* your controller code get lattitude and logitude */
     $location = $this->getLocation($request->address1.' '.$request->state.' '.$request->country);

            $hotelEntity->lattitude = !empty($location->results[0]->geometry->location->lat) ? $location->results[0]->geometry->location->lat : 0.00;
            
            $hotelEntity->longitude = !empty($location->results[0]->geometry->location->lng) ? $location->results[0]->geometry->location->lng : 0.00;





///////////////////////


            public function getlocation()
            {
                $address = "London united state";
                $array  = $this->get_longitude_latitude_from_adress($address);
                $latitude  = round($array['lat'], 6);
                $longitude = round($array['long'], 6);           
            }

            
            function get_longitude_latitude_from_adress($address){
  
                $lat =  0;
                $long = 0;
                 
                 $address = str_replace(',,', ',', $address);
                 $address = str_replace(', ,', ',', $address);
                 
                 $address = str_replace(" ", "+", $address);
                  try {

                     $json = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' .urlencode($address) . '&sensor=false&key='.$g_key);
                     $json1 = json_decode($json);
                     
                     if($json1->{'status'} == 'ZERO_RESULTS') {
                     return [
                         'lat' => 0,
                         'lng' => 0
                        ];
                     }
                     
                     if(isset($json1->results))
                     {
                        $lat = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lat'});
                        $long = ($json1->{'results'}[0]->{'geometry'}->{'location'}->{'lng'});
                      }
                } 

                  catch(exception $e) { }

                     return [
                     'lat' => $lat,
                     'lng' => $long
                     ];
            }



            /////////////////
            $cities = City::select(DB::raw('*, ( 6367 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latitude ) ) ) ) AS distance'))
                    ->having('distance', '<', 25)
                    ->orderBy('distance')
                    ->get();
?>







