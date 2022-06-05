  var dateToday = new Date();
        var dates = $("#from, #to").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 3,
            minDate: dateToday,
            onSelect: function(selectedDate) {
                var option = this.id == "from" ? "minDate" : "maxDate",
                    instance = $(this).data("datepicker"),
                    date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
                dates.not(this).datepicker("option", option, date);
            }
        });



        
  $('#start-at').daterangepicker({
      "singleDatePicker": true,
      "showDropdowns": true,
      "timePicker": true,
      "autoApply": true,
      "autoUpdateInput": false,
      "locale": { "firstDay": 1 },
      "startDate":  moment(),
      "minDate":  moment(),
    }, function(start, end, label) {
    var startAt = start.format('DD-MMM-YYYY h:mm a');

    $('#start-at').val(startAt);
    //console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
  }); 

  $('#start-at').on('hide.daterangepicker', function(ev, picker) {
    var startAt = $('#start-at').val();
    var endAt   = $('#end-at').val();
    var start   = moment(startAt,'YYYY-MM-DD h:mm a').format('YYYY-MM-DDTHH:mm:ssZ'); 
    var end     = moment(endAt,'YYYY-MM-DD h:mm a').format('YYYY-MM-DDTHH:mm:ssZ');

    if(start <= end){
      $('#end-at').val(endAt);
    }else{
      $('#end-at').val('');
    }

    $('#end-at').daterangepicker({
      "singleDatePicker": true,
      "showDropdowns": true,
      "timePicker": true,
      "autoApply": true,
      "autoUpdateInput": false,
      "locale": { "firstDay": 1 },
      "startDate":  moment(),
      "minDate":  moment(picker.startDate),
    }, function(start, end, label) {
      var endAt   = start.format('DD-MMM-YYYY h:mm a');
      $('#end-at').val(endAt);
    });

  });
