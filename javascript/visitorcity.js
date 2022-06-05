// get visitor city name using coordinates

const getVisitorCity = (latitude,longitude) =>{

    var geocoder;
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(latitude, longitude);

    geocoder.geocode(
        {'latLng': latlng}, 
        function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    var add= results[0].formatted_address ;
                    var  value=add.split(",");

                    count=value.length;
                    country=value[count-1];
                    state=value[count-2];
                    city=value[count-3];
                    console.log("city name is: " + city);
                }
                else  {
                    console.log("address not found");
                }
            }
            else {
                console.log("Geocoder failed due to: " + status);
            }
        }
    );
}



/// get geoloaction
const successCallback = (position) => {
    
    var latitude = position.coords.latitude;
    var longitude = position.coords.longitude;

    getVisitorCity(latitude,longitude);
};

const errorCallback = (error) => {

    switch(error.code)
    {
        case error.PERMISSION_DENIED:
            console.log("User denied the request for Geolocation.");
        break;
        case error.POSITION_UNAVAILABLE:
            console.log("Location information is unavailable.");
        break;
        case error.TIMEOUT:
            console.log("The request to get user location timed out.");
        break;
        case error.UNKNOWN_ERROR:
            console.log("An unknown error occurred.");
        break;
    }
    console.log(error);
    getTopKgVerfiredLawyer();
}

if (navigator.geolocation) 
{
    navigator.geolocation.getCurrentPosition(successCallback, errorCallback);
}else
{
    console.log("Geolocation is not supported by this browser.");
}