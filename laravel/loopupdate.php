<?php  
     @foreach($socials as $link) 

                <div class="form-group">
                    {{ Form::label($link->var_key, trans($link->var_key), ['class' => 'col-lg-2 control-label required']) }}
                  
                    <div class="col-lg-10">
                        {{ Form::text($link->var_key, $link->var_value, ['class' => 'form-control box-size txturl','id'=>'txturl', 'placeholder' => trans('Enter '.$link->var_key.''), 'required' => 'required']) }}
                    </div>
                   
                </div><!--form control--> 
              
            @endforeach    
                <div class="edit-form-btn">
                    
                     {{ Form::submit(trans('Update'), ['class' => 'btn btn-primary btn-md','id' =>'btnValidate']) }}
                    <div class="clearfix"></div>
                </div>
            </div><!-- /.box-body -->



            <!-- ////////////// -->

               public function update(Request $request){
            $input = $request->all();        
            foreach ($input as $key=>$value) {
                DB::table('site_settings')->where('var_key', $key)->update(['var_value'=>$value]);
            }
            return redirect()->back()->withFlashSuccess('Social Link Update Successfully');
        }

        ///////
        foreach ($quantity as $key=> $value) {
                 $data = array('food_menu_id' => $foodmenuID,'quantity'=>$quantity[$key], 'price' => $price[$key]);
                       DB::table('food_menu_prices')->insert($data);

              }

        ?>