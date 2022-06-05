<?php 
if(!function_exists('displayDate'))
{
    function displayDate($date=null,$format='d, F Y')
    {
      $disp_date='';
      if(is_string($date)){
         $disp_date=date($format,strtotime($date));
      }
      else if(!empty($date) && $date!='0000-00-00' && $date!='0000-00-00 00:00:00') {
          $disp_date=$date->format($format);
      }
      return $disp_date;
    }
} 


displayDate($row->created_at)  // this display view 


 ?>