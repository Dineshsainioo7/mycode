<!DOCTYPE html>
<html>
<head>
    <title>Jquery disable right click, cut, copy and paste example - ItSolutionstuff.com</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body>
    
<h1>Jquery disable right click, cut, copy and paste example - ItSolutionstuff.com</h1>
   
<script type="text/javascript">
$(document).ready(function () {
    //Disable cut copy paste
    $(document).bind('cut copy paste', function (e) {
        e.preventDefault();
    });
     
    //Disable mouse right click
    $(document).on("contextmenu",function(e){
        return false;
    });
});
</script>
   
</body>
</html>