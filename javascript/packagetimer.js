<script>
jQuery(document).ready(function(){
	var i=1;

	var x = setInterval(function() {
		jQuery('.time_counter').each(function() {
		    var date = jQuery(this).data('id');   // get dyamic  date 
		    var countDownDate = new Date(date).getTime();
		    jQuery(this).attr("id","time_counter"+i);
		    var now = new Date().getTime();

		  	var distance = countDownDate - now;

		  	var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		  	var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		  	var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		  	var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		  	jQuery("#time_counter"+i).html(days + "Days " + hours + "Hours "
			  + minutes + "Minutes " + seconds + "Seconds ");
			  if (distance < 0) {
			    clearInterval(x);
			    jQuery(".time_counter").html('EXPIRED');
		  	}
		  	i++;
		});
		}, 1000);

	
});
	
</script>