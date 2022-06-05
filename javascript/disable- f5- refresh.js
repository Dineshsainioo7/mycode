<!DOCTYPE html>
<html>
<head>
  <title>How to disable f5 refresh button using jquery? - ItSolutionStuff.com</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
</head>
<body>
   
<div class="container">
  <h1>How to disable f5 refresh button using jquery? - ItSolutionStuff.com</h1>
   
</div>
   
<script type="text/javascript">
    
    $(document).ready(function() {
      $(window).keydown(function(event){
        if(event.keyCode == 116) {
          event.preventDefault();
          return false;
        }
      });
    });
    
</script>
     
</body>