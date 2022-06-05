<?php  
    public function getIpDetails()
    {
        $session = Session::get('ipDetail');
        $ipDetail = (Session::get('ipDetail'))?Session::get('ipDetail'):null;        
        if(empty($ipDetail))
        {
            if($_SERVER['SERVER_NAME'] == "localhost")
            {
               $url = 'https://ipinfo.io/json';
            }
            else
            {
               $ip = $_SERVER['REMOTE_ADDR'];
               $url  = "https://ipinfo.io/".$ip."/json";
            }
            
            $streamContext = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]);
            $data = @file_get_contents($url, false, $streamContext);
            $obj = json_decode($data);          
            if (!empty($obj->country)) 
            {
                Session::put('ipDetail',$obj);
                return $obj;          
            }
            else
            {
                return [];
            }
        }
        else
        {
            return $ipDetail;
        }   
    }

    /* other way */


To get server IP address by $_SERVER['SERVER_ADDR']

Local IP

To get client IP by $_SERVER['REMOTE_ADDR']

To find location simply do:

//ipinfo grabs the ip of the person requesting

 $getloc = json_decode(file_get_contents("http://ipinfo.io/"));

 echo $getloc->city; //to get city
If you are using coordinates it returns in single string like 32,-72 so, for that you can use:

$coordinates = explode(",", $getloc->loc); // -> '32,-72' becomes'32','-72'
echo $coordinates[0]; // latitude
echo $coordinates[1]; // longitude

/*/////////////////////////////////////////////////////////////////*/
        $location = $this->getIpDetails();
          $address = explode(',', $location->loc);
          $latt = $address[0];     // find ip deatils in contollere 
          $longt = $address[1];
          $distance = 25.00;


          // find near location
            $hotels_data['hotels'] = Hotels::whereRaw(DB::raw('( 6367 * acos( cos( radians('.$latt.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longt.') ) + sin( radians('.$latt.') ) * sin( radians( latitude ) ) ) ) < '.$distance))
          ->get();

          $hotels_data['total'] = Hotels::whereRaw(DB::raw('( 6367 * acos( cos( radians('.$latt.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longt.') ) + sin( radians('.$latt.') ) * sin( radians( latitude ) ) ) ) < '.$distance))
          ->count();



          // 100 % correct

          function findNearestRestaurants($latitude, $longitude, $radius = 400)
{
    /*
     * using eloquent approach, make sure to replace the "Restaurant" with your actual model name
     * replace 6371000 with 6371 for kilometer and 3956 for miles
     */
    $restaurants = Restaurant::selectRaw("id, name, address, latitude, longitude, rating, zone ,
                     ( 6371000 * acos( cos( radians(?) ) *
                       cos( radians( latitude ) )
                       * cos( radians( longitude ) - radians(?)
                       ) + sin( radians(?) ) *
                       sin( radians( latitude ) ) )
                     ) AS distance", [$latitude, $longitude, $latitude])
        ->where('active', '=', 1)
        ->having("distance", "<", $radius)
        ->orderBy("distance",'asc')
        ->offset(0)
        ->limit(20)
        ->get();

    return $restaurants;
}


?>          