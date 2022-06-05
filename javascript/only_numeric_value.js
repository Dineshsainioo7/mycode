<div class="container">
  <h1>JQuery - Allow only numeric values (numbers) in Textbox - ItSolutionStuff.com</h1>
         
      <label>Enter Value:</label>
      <input type="text" name="myValue" class="only-numeric" >
      <span class="error" style="color: red; display: none">* Input digits (0 - 9)</span>
    
</div>
    
<script type="text/javascript">
    
    $(document).ready(function() {
      $(".only-numeric").bind("keypress", function (e) {
          var keyCode = e.which ? e.which : e.keyCode
               
          if (!(keyCode >= 48 && keyCode <= 57)) {
            $(".error").css("display", "inline");
            return false;
          }else{
            $(".error").css("display", "none");
          }
      });
    });
     
</script>