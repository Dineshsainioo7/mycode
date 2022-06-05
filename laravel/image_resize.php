<?php   
                        use Image;                    


                        $files = $request->file("image");


                        $name = $val->getClientOriginalName();
                        $image_resize = Image::make($val->getRealPath());
                        $image_resize->resize(370, 260);
                        $image_resize->save('img/hotel/' . $name, 95);

                        $name1 = time() . $val->getClientOriginalName();
                        $image_resize = Image::make($val->getRealPath());
                        $image_resize->resize(1366, 611);
                        $image_resize->save('img/hotel/' . $name1, 95);

?>                        