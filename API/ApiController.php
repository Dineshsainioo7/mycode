<?php
namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use DB, Mail, Redirect, Response, Auth, Session;
use Illuminate\Sublogport\Facades\Input;
use Illuminate\Support\Facades\Request;
use Hash,Validator, App;
use URL;

use File;
use Image;
use Carbon\Carbon;

/**
 * Class FrontendController
 *@package App\Https\Controllers
 */
class ApiController extends Controller
{
	/**
	 * @return \Illuminate\View\View
	 */
	public function checkToken($access_token)
	{
		$token=env('ACCESS_TOKEN');
		if($access_token!=$token)
		{
			$resultArray['status']='0';
			$resultArray['message']=trans('invalid_token');
			return $resultArray;
			die;
		}
		else
		{
			$resultArray['status']='1';
			$resultArray['message']=trans('common.api.token');
			return $resultArray;
			die;	
		}
	}

	public function socialLogin()
	{	
		$access_token = Request::header('accesstoken');
		$email = isset($_REQUEST['email']) && !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '' ;
		$provider = isset($_REQUEST['provider']) && !empty($_REQUEST['provider']) ? trim($_REQUEST['provider']) : '' ;
		$provider_id = isset($_REQUEST['provider_id']) && !empty($_REQUEST['provider_id']) ? trim($_REQUEST['provider_id']) : '' ;
		$first_name = isset($_REQUEST['first_name']) && !empty($_REQUEST['first_name']) ? trim($_REQUEST['first_name']) : '' ;
		$gender = isset($_REQUEST['gender']) && !empty($_REQUEST['gender']) ? trim($_REQUEST['gender']) : '' ;
		$last_name = isset($_REQUEST['last_name']) && !empty($_REQUEST['last_name']) ? trim($_REQUEST['last_name']) : '' ;
		$age = isset($_REQUEST['age']) && !empty($_REQUEST['age']) ? trim($_REQUEST['age']):'';
		$image = isset($_REQUEST['image']) && !empty($_REQUEST['image']) ? trim($_REQUEST['image']) : '' ;
		$deviceID = isset($_REQUEST['deviceID']) && !empty($_REQUEST['deviceID']) ? trim($_REQUEST['deviceID']) : '' ;
		$deviceType = isset($_REQUEST['deviceType']) && !empty($_REQUEST['deviceType']) ? trim($_REQUEST['deviceType']) : '' ;
		if(isset($email) && !empty($email) && isset($provider) && !empty($provider) && isset($provider_id) && !empty($provider_id) && isset($first_name) && !empty($first_name) && isset($last_name) && !empty($last_name) && isset($age) && !empty($age))
		{
			if (filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$userArr = DB::table('users')->where('email', $email)->first();
				//$userArr = DB::table('users')->whereRaw("(email = '".$email."' AND deleted_at IS null )")->first();
				if(!empty($userArr))
				{
					$userID = $userArr->id;
					DB::table('social_logins')->where('user_id', $userID)->update(['user_id' => $userID, 'provider' => $provider, 'provider_id' => $provider_id, 'avatar' => $image]);
					$arr = array('first_name' => $first_name, 'last_name' => $last_name, 'age' => $age, 'gender' => $gender, 'deviceID' => $deviceID, 'deviceType' =>$deviceType, 'deleted_at' => 'NULL');
					DB::table('users')->where('id', $userID)->update($arr);
					$userArr = DB::table('users')->where('email', $email)->first();
				}
				else
				{
					$arr = array('email' => $email, 'first_name' => $first_name, 'last_name' => $last_name, 'age' => $age, 'gender' => $gender, 'confirmed' => 1, 'plan_status' => 'Deactive', 'deleted_at' => 'NULL', 'deviceID' => $deviceID, 'deviceType' =>$deviceType);
					$userID = DB::table('users')->insertGetId($arr);
					DB::table('role_user')->insert(['user_id' => $userID, 'role_id' => 3]);

					DB::table('user_search_settings')->insert(['user_id' => $userID]);
					DB::table('user_medias')->insert(['user_id' => $userID]);
					DB::table('user_details')->insert(['user_id' => $userID]);

					DB::table('social_logins')->insert(['user_id' => $userID, 'provider' => $provider, 'provider_id' => $provider_id, 'avatar' => $image]);
					$userArr = DB::table('users')->where('id', $userID)->first();
				}

				$checkPlanDate = DB::table('users')->select('plan_start_date', 'plan_end_date')->where('id', $userArr->id)->first();
				$today = date("Y-m-d");
				if($today >= $checkPlanDate->plan_end_date){
				 	$userDetail_arr['subscription'] = 'No';
				 }else{
				 	$userDetail_arr['subscription'] = 'Yes';
				 }
				$userDetail_arr['id'] = isset($userArr->id) && !empty($userArr->id) ? $userArr->id : '' ;
				$userDetail_arr['email'] = isset($userArr->email) && !empty($userArr->email) ? $userArr->email : '' ;
				$userDetail_arr['first_name'] = isset($userArr->first_name) && !empty($userArr->first_name) ? $userArr->first_name : '' ;
				$userDetail_arr['last_name'] = isset($userArr->last_name) && !empty($userArr->last_name) ? $userArr->last_name : '' ;
				$userDetail_arr['profile_pic'] = $image;
				$userDetail_arr['gender'] = isset($userArr->gender) && !empty($userArr->gender) ? $userArr->gender : '' ;
				$userDetail_arr['age'] = isset($userArr->age) && !empty($userArr->age) ? $userArr->age : '' ;
				# preference Start
				$userPre = DB::table('user_details')->where('user_id', $userArr->id)->first();
				$preference['min_age_group'] = isset($userPre->min_age_group) && !empty($userPre->min_age_group) ? $userPre->min_age_group : 0 ;
				$preference['max_age_group'] = isset($userPre->max_age_group) && !empty($userPre->max_age_group) ? $userPre->max_age_group : 0 ;
				$preference['receive_push'] = isset($userPre->receive_push) && !empty($userPre->receive_push) ? $userPre->receive_push : 'No' ;
				$preference['push_sound'] = isset($userPre->push_sound) && !empty($userPre->push_sound) ? $userPre->push_sound : 'No' ;
				$preference['hide_account'] = isset($userPre->hide_account) && !empty($userPre->hide_account) ? $userPre->hide_account : 'No' ;
				# preference End

				# Start User Search Setting
                $userSerch = DB::table('user_search_settings')->where('user_id', $userArr->id)->first();
                $searchSetting['region'] = isset($userSerch->region) && !empty($userSerch->region) ? $userSerch->region : '' ;
                $searchSetting['country'] = isset($userSerch->country) && !empty($userSerch->country) ? $userSerch->country : '' ;
                $searchSetting['country'] = isset($userSerch->country) && !empty($userSerch->country) ? $userSerch->country : '' ;
                $searchSetting['week_start'] = isset($userSerch->week_start) && !empty($userSerch->week_start) ? $userSerch->week_start : '' ;
                $searchSetting['week_start'] = isset($userSerch->week_start) && !empty($userSerch->week_start) ? $userSerch->week_start : '' ;
                $searchSetting['week_start'] = isset($userSerch->week_start) && !empty($userSerch->week_start) ? $userSerch->week_start : '' ;
                $searchSetting['enable_more_searching'] = isset($userSerch->enable_more_searching) && !empty($userSerch->enable_more_searching) ? $userSerch->enable_more_searching : 'No' ;
                $array1 = array(); 
                $str = isset($userSerch->language_native) && !empty($userSerch->language_native) ? $userSerch->language_native : '' ;
                $storagePath = URl::to('/storage/app/public/img/');
                $array1['language_native'] = array();
                if(!empty($str))
                {
                	$langID= explode(',', $str);
                	$array2 = DB::table('languages')->whereIn('id', $langID)->select('id', 'name', 'icon')->get()->toArray();
                	foreach ($array2 as $ln => $vl) {
						$array1['language_native'][$ln]['id'] = isset($vl->id) && !empty($vl->id) ? $vl->id : '';
						$array1['language_native'][$ln]['name'] = isset($vl->name) && !empty($vl->name) ? $vl->name : '';
						$array1['language_native'][$ln]['short_name'] = isset($vl->short_name) && !empty($vl->short_name) ? $vl->short_name : '';
						$array1['language_native'][$ln]['icon'] = isset($vl->icon) && !empty($vl->icon) ? $storagePath.'/language/'.$vl->icon : '';
					}

                }
                $searchSetting = $array1;
                $searchSetting['prefer_travel_gender'] = isset($userSerch->prefer_travel_gender) && !empty($userSerch->prefer_travel_gender) ? $userSerch->prefer_travel_gender : '' ;
                $searchSetting['location_range'] = isset($userSerch->location_range) && !empty($userSerch->location_range) ? $userSerch->location_range : '' ;
                $accommoD = DB::table('accommodations')->select('id', 'name', 'icon')->Where('status', 'Active')->get()->toArray();
                $accommoDations = array();
                foreach ($accommoD as $ac => $vc) {
                	$accommoDations[$ac]['id'] = isset($vc->id) && !empty($vc->id) ? $vc->id : '';
                	$accommoDations[$ac]['name'] = isset($vc->name) && !empty($vc->name) ? $vc->name : '';
                	$accommoDations[$ac]['icon'] = isset($vc->icon) && !empty($vc->icon) ? $storagePath.'/accomodation/'.$vc->icon : '';
                }
                $transp = DB::table('transports')->select('id', 'name', 'icon')->where('status', 'Active')->get()->toArray();
                $transports = array();
                foreach ($transp as $at => $vt) {
                	$transports[$at]['id'] = isset($vt->id) && !empty($vt->id) ? $vt->id : '';
                	$transports[$at]['name'] = isset($vt->name) && !empty($vt->name) ? $vt->name : '';
                	$transports[$at]['icon'] = isset($vt->icon) && !empty($vt->icon) ? $storagePath.'/transport/'.$vt->icon : '';
                }
                //$eduId  = $userArr->education_id;
                $education1 = DB::table('educations')->select('id', 'name', 'icon')->where('status', 'Active')
                	->get()->toArray();

                $education = array();
                foreach ($education1 as $aed => $ved) {
                	$education[$aed]['id'] = isset($ved->id) && !empty($ved->id) ? $ved->id : '';
                	$education[$aed]['name'] = isset($ved->name) && !empty($ved->name) ? $ved->name : '';
                	$education[$aed]['icon'] = isset($ved->icon) && !empty($ved->icon) ? $storagePath.'/education/'.$ved->icon : '';
                }	              	
				# End User Search Setting
				$resultArray['status']='1';
				$resultArray['message'] = "Successfully logged in".' '.$provider;
				$resultArray['user']['details']=$userDetail_arr;
				$resultArray['user']['preference']=$preference;
				$resultArray['user']['searchSetting']=$searchSetting;
				$resultArray['user']['accommodations']=$accommoDations;
				$resultArray['user']['transports']=$transports;
				$resultArray['user']['education']=$education;
				$resultArray['path']=url('/img/users').'/';
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);
				
			}
			else
			{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Email Format";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);
			}
		}
		else
		{
			$resultArray['status']='0';
			$resultArray['message'] = "Invalid Parameter";
			return response()->json($resultArray,JSON_UNESCAPED_UNICODE);
		}
	} 

	public function getProfileData($value = '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? trim($_REQUEST['user_id']) : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$chekUser = DB::table('users')->where('id', $user_id)->first();
			$language_code = $chekUser->language_code;
			if(!empty($chekUser) && isset($chekUser))
			{
				$lang_code = isset($chekUser->language_code) && !empty($chekUser->language_code) ? $chekUser->language_code : 'en';
				$accomo = DB::table('accommodations')
						  ->join('accommodation_langs','accommodation_langs.accommodation_id','=','accommodations.id')
						  ->join('system_languages','system_languages.id','=','accommodation_langs.lang_id')
						  ->select('accommodations.*','accommodation_langs.name')
						  ->where('accommodations.status','Active')
						  ->where('system_languages.language_code',$lang_code)
						  ->get();
				/*	$accomo = DB::table('accommodations')->get()->toArray(); */
				$list = array();
				foreach ($accomo as $acc => $vcc) {
					$list['accommodations'][$acc]['id'] = isset($vcc->id) && !empty($vcc->id) ? $vcc->id : '';
					$list['accommodations'][$acc]['name'] = isset($vcc->name) && !empty($vcc->name) ? $vcc->name : '';
					$list['accommodations'][$acc]['icon'] = isset($vcc->icon) && !empty($vcc->icon) ? $vcc->icon : '';
				}
				$transp = DB::table('transports')
						  ->join('transport_langs','transport_langs.transport_id','=','transports.id')
						  ->join('system_languages','system_languages.id','=','transport_langs.lang_id')
						  ->select('transports.*','transport_langs.name')
						  ->where('transports.status','Active')
						  ->where('system_languages.language_code',$lang_code)
						  ->get();
				//$transp = DB::table('transports')->get()->toArray(); 
				foreach ($transp as $att => $vtt) {
					$list['transport'][$att]['id'] = isset($vtt->id) && !empty($vtt->id) ? $vtt->id : '';
					$list['transport'][$att]['name'] = isset($vtt->name) && !empty($vtt->name) ? $vtt->name : '';
					$list['transport'][$att]['icon'] = isset($vtt->icon) && !empty($vtt->icon) ? $vtt->icon : '';
				}
				$lang = DB::table('languages')
						  ->join('language_langs','language_langs.language_id','=','languages.id')
						  ->join('system_languages','system_languages.id','=','language_langs.lang_id')
						  ->select('languages.*','language_langs.name')
						  ->where('languages.status','Active')
						  ->where('system_languages.language_code',$lang_code)
						  ->get();
				//$lang = DB::table('languages')->get()->toArray(); 
				foreach ($lang as $all => $vll) {
					$list['language'][$all]['id'] = isset($vll->id) && !empty($vll->id) ? $vll->id : '';
					$list['language'][$all]['name'] = isset($vll->name) && !empty($vll->name) ? $vll->name : '';
					$list['language'][$all]['short_name'] = isset($vll->short_name) && !empty($vll->short_name) ? $vll->short_name : '';
					$list['language'][$all]['icon'] = isset($vll->icon) && !empty($vll->icon) ? $vll->icon : '';
				}
				$educa = DB::table('educations')
						  ->join('education_langs','education_langs.education_id','=','educations.id')
						  ->join('system_languages','system_languages.id','=','education_langs.lang_id')
						  ->select('educations.*','education_langs.name')
						  ->where('educations.status','Active')
						  ->where('system_languages.language_code',$lang_code)
						  ->get();
				//$educa = DB::table('educations')->get()->toArray();
				foreach ($educa as $adu => $vdu) {
					$list['education'][$adu]['id'] = isset($vdu->id) && !empty($vdu->id) ? $vdu->id : '';
					$list['education'][$adu]['name'] = isset($vdu->name) && !empty($vdu->name) ? $vdu->name : '';
					$list['education'][$adu]['icon'] = isset($vdu->icon) && !empty($vdu->icon) ? $vdu->icon : '';
				}
			
				$userData = DB::table('users')->where('id', $user_id)->first();
				$userArr = array();
				$userArr['Detail']['id'] = isset($userData->id) && !empty($userData->id) ? $userData->id :'';
				$userArr['Detail']['first_name'] = isset($userData->first_name) && !empty($userData->first_name) ? $userData->first_name :'';
				$userArr['Detail']['last_name'] = isset($userData->last_name) && !empty($userData->last_name) ? $userData->last_name :'';
				$userArr['Detail']['email'] = isset($userData->email) && !empty($userData->email) ? $userData->email :'';
				$userArr['Detail']['age'] = isset($userData->age) && !empty($userData->age) ? $userData->age :'';
				$userimg = DB::table('social_logins')->where('user_id', $user_id)->first();
				$userArr['Detail']['profile_pic'] = isset($userimg->avatar) && !empty($userimg->avatar) ? $userimg->avatar :'';
				$userArr['Detail']['gender'] = isset($userData->gender) && !empty($userData->gender) ? $userData->gender :'';
				$userArr['Detail']['perfect_travel_friend'] = isset($userData->perfect_travel_friend) && !empty($userData->perfect_travel_friend) ? $userData->perfect_travel_friend :'';
				$userArr['Detail']['spend_free_time'] = isset($userData->spend_free_time) && !empty($userData->spend_free_time) ? $userData->spend_free_time :'';
				$userArr['Detail']['favorite_countries'] = isset($userData->favorite_countries) && !empty($userData->favorite_countries) ? $userData->favorite_countries :'';
				$education = DB::table('educations')
						  ->join('education_langs','education_langs.education_id','=','educations.id')
						  ->join('system_languages','system_languages.id','=','education_langs.lang_id')
						  ->select('educations.id','educations.icon','education_langs.name')
						  ->where('system_languages.language_code',$lang_code)
						  ->where('educations.id',$userData->education_id)
						  ->first();
				//$education = DB::table('educations')->select('id', 'name', 'icon')->where('id', $userData->education_id)->first();
				$educationPath = URl::to('/storage/app/public/img/education/');
				$userArr['educationPath'] = $educationPath;
				$userArr['Detail']['education_id'] = array();
				if(isset($education) && !empty($education)){
					$userArr['Detail']['education_id'][] = $education;

				}
				$path = URL::to('img/user/');
				$userArr['path'] = $path;
				$storagePath = URl::to('/storage/app/public/img/');
				/*$userArr['language_speak']= array();
				if(isset($userData->language_speak) && !empty($userData->language_speak))
				{
					$langID = explode(',', $userData->language_speak);
					$speak = DB::table('languages')->select('id','name', 'icon', 'status')->whereIn('id',$langID)->get()->toArray();
					foreach ($speak as $ks => $vs) {
						$userArr['language_speak'][$ks]['id'] = isset($vs->id) && !empty($vs->id) ? $vs->id : '';
						$userArr['language_speak'][$ks]['name'] = isset($vs->name) && !empty($vs->name) ? $vs->name : '';
						$userArr['language_speak'][$ks]['short_name'] = isset($vs->short_name) && !empty($vs->short_name) ? $vs->short_name : '';
						$userArr['language_speak'][$ks]['icon'] = isset($vs->icon) && !empty($vs->icon) ? $storagePath.'/language/'.$vs->icon : '';
					}
				}*/ 
				$userArr['accommodations_id']= array();
				if(isset($userData->accommodations_id) && !empty($userData->accommodations_id))
				{
					$langID = explode(',', $userData->accommodations_id);
					$accom = DB::table('accommodations')
							  ->join('accommodation_langs','accommodation_langs.accommodation_id','=','accommodations.id')
							  ->join('system_languages','system_languages.id','=','accommodation_langs.lang_id')
							  ->select('accommodations.*','accommodation_langs.name')
							  ->where('system_languages.language_code',$lang_code)
							  ->whereIn('accommodations.id',$langID)
							  ->get();
					//$accom = DB::table('accommodations')->select('id', 'name', 'icon')->whereIn('id',$langID)->get()->toArray();
					foreach ($accom as $ka => $va) {
						$userArr['accommodations_id'][$ka]['id'] = isset($va->id) && !empty($va->id) ? $va->id : '';
						$userArr['accommodations_id'][$ka]['name'] = isset($va->name) && !empty($va->name) ? $va->name : '';
						$userArr['accommodations_id'][$ka]['icon'] = isset($va->icon) && !empty($va->icon) ? $storagePath.'/accomodation/'.$va->icon : '';
					}
				}
				$userArr['transports_id']= array();
				if(isset($userData->transports_id) && !empty($userData->transports_id))
				{
					$langID = explode(',', $userData->transports_id);
					$trans = DB::table('transports')
							  ->join('transport_langs','transport_langs.transport_id','=','transports.id')
							  ->join('system_languages','system_languages.id','=','transport_langs.lang_id')
							  ->select('transports.*','transport_langs.name')
							  ->where('system_languages.language_code',$lang_code)
							  ->whereIn('transports.id',$langID)
							  ->get();
					//$trans = DB::table('transports')->select('id', 'name', 'icon')->whereIn('id',$langID)->get()->toArray();
					foreach ($trans as $kt => $vt) {
						$userArr['transports_id'][$kt]['id'] = isset($vt->id) && !empty($vt->id) ? $vt->id : '';
						$userArr['transports_id'][$kt]['name'] = isset($vt->name) && !empty($vt->name) ? $vt->name : '';
						$userArr['transports_id'][$kt]['icon'] = isset($vt->icon) && !empty($vt->icon) ? $storagePath.'/transport/'.$vt->icon : '';
					}
				}

				$userArr['language_native']= array();
				if(isset($userData->language_native) && !empty($userData->language_native))
				{
					$langID = explode(',', $userData->language_native);
					$trans = DB::table('languages')
							  ->join('language_langs','language_langs.language_id','=','languages.id')
							  ->join('system_languages','system_languages.id','=','language_langs.lang_id')
							  ->select('languages.*','language_langs.name')
							  ->where('system_languages.language_code',$lang_code)
							  ->whereIn('languages.id',$langID)
							  ->get();
					//$trans = DB::table('languages')->select('id', 'name', 'icon')->whereIn('id',$langID)->get()->toArray();
					foreach ($trans as $kn => $vn) {
						$userArr['language_native'][$kn]['id'] = isset($vn->id) && !empty($vn->id) ? $vn->id : '';
						$userArr['language_native'][$kn]['name'] = isset($vn->name) && !empty($vn->name) ? $vn->name : '';
						$userArr['language_native'][$kn]['icon'] = isset($vn->icon) && !empty($vn->icon) ? $storagePath.'/language/'.$vn->icon : '';
					}
				}
				$userArr['speak_language']= array();
				if(isset($userData->speak_language) && !empty($userData->speak_language))
				{
					$langID = explode(',', $userData->speak_language);
					$trans = DB::table('languages')
							  ->join('language_langs','language_langs.language_id','=','languages.id')
							  ->join('system_languages','system_languages.id','=','language_langs.lang_id')
							  ->select('languages.*','language_langs.name')
							  ->where('system_languages.language_code',$lang_code)
							  ->whereIn('languages.id',$langID)
							  ->get();
					//$trans = DB::table('languages')->select('id', 'name', 'icon')->whereIn('id',$langID)->get()->toArray();
					foreach ($trans as $ks => $vl) {
						$userArr['speak_language'][$ks]['id'] = isset($vl->id) && !empty($vl->id) ? $vl->id : '';
						$userArr['speak_language'][$ks]['name'] = isset($vl->name) && !empty($vl->name) ? $vl->name : '';
						$userArr['speak_language'][$ks]['icon'] = isset($vl->icon) && !empty($vl->icon) ? $storagePath.'/language/'.$vl->icon : '';
					}
				}
				$userArr['media'] = array();
				$getImage = DB::table('user_medias')->where('user_id', $user_id)->first();
				if(!empty($getImage)){
			 		$array = explode(',', $getImage->filename);
			 	 	array_pop($array);
			 	 	$userArr['media'] = $array;
				}
				$profileImage = DB::table('social_logins')->where('user_id', $user_id)->first();
				$profileImage1 = isset($profileImage->avatar) && !empty($profileImage->avatar) ? $profileImage->avatar : '';
				 $userArr['profile_image'] = $profileImage1;
			 	$resultArray['profile_data'] = $userArr;
			 	$resultArray['list'] = $list;		
				$resultArray['status']='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;				
			}else{
				$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}
		}
	}

	/* Update Profile */
	public function UpdateProfile()
	{
		$access_token = Request::header('accesstoken');		
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$perfect_travel_friend = isset($_REQUEST['perfect_travel_friend']) && !empty($_REQUEST['perfect_travel_friend']) ? $_REQUEST['perfect_travel_friend'] : '' ;
		$spend_free_time = isset($_REQUEST['spend_free_time']) && !empty($_REQUEST['spend_free_time']) ? $_REQUEST['spend_free_time'] : '' ;
		$favorite_countries = isset($_REQUEST['favorite_countries']) && !empty($_REQUEST['favorite_countries']) ? $_REQUEST['favorite_countries'] : '' ;
		$accmodation = isset($_REQUEST['accommodations_id']) && !empty($_REQUEST['accommodations_id']) ? $_REQUEST['accommodations_id'] : '' ;
		$transport = isset($_REQUEST['transports_id']) && !empty($_REQUEST['transports_id']) ? $_REQUEST['transports_id'] : '' ;
		$languageNative = isset($_REQUEST['language_native']) && !empty($_REQUEST['language_native']) ? $_REQUEST['language_native'] : '' ;
		$speak_language = isset($_REQUEST['speak_language']) && !empty($_REQUEST['speak_language']) ? $_REQUEST['speak_language'] : '' ;
		$education = isset($_REQUEST['education_id']) && !empty($_REQUEST['education_id']) ? $_REQUEST['education_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		//$this->setLanguage($user_id);
		/*if(isset($perfect_travel_friend) && !empty($perfect_travel_friend) && isset($spend_free_time) && !empty($spend_free_time) && isset($favorite_countries) && !empty($favorite_countries) && isset($accmodation) && !empty($accmodation) && isset($transport) && !empty($transport) && isset($languageNative) && !empty($languageNative) && isset($speak_language) && !empty($speak_language) && isset($education) && !empty($education) ){*/
			$user_arr = DB::table('users')->where('id',$user_id)->first();
			$language_code = $user_arr->language_code;
			if(!empty($user_arr)){
				$updateUser_arr['perfect_travel_friend'] = trim($perfect_travel_friend);
				$updateUser_arr['spend_free_time'] = trim($spend_free_time);
				$updateUser_arr['favorite_countries'] = trim($favorite_countries);
				$updateUser_arr['accommodations_id'] = trim($accmodation);
				$updateUser_arr['transports_id'] = trim($transport);
				$updateUser_arr['language_native'] = trim($languageNative);
				$updateUser_arr['speak_language'] = trim($speak_language);
				$updateUser_arr['education_id'] = trim($education);
				$skipUser = DB::table('user_blocks')->where('user_id', $user_id)->delete();
				$updateUser = DB::table('users')->where('id',$user_id)->update($updateUser_arr);
				$mess='Profile Succesfully Updated';
				if($language_code == 'de')
				{
					$mess='Profil Erfolgreich aktualisiert';
				}
				if($updateUser){
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					return json_encode($resultArray);exit;
				}else{
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					return json_encode($resultArray);exit;
				}
			}else{
				$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return json_encode($resultArray);exit;
			}					
		/*}else{
			$resultArray['status']='0';
			$resultArray['message']=trans('Invalid Parameter');
			return json_encode($resultArray);exit;
		}*/
	}

	public function logout()
	{
		$access_token = Request::header('accesstoken');
		$user_id=isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$language_code = ''; 
		if(isset($user_id) && !empty($user_id))
		{	
			//$user_arr = DB::table('users')->whereRaw("(id = '".$user_id."' AND deleted_at IS null )")->first();
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			if(!empty($user_arr))
			{					   			
				$check_auth = $this->checkToken($access_token);
				if($check_auth['status']!=1)
				{
					return json_encode($check_auth);
				}
				else{
					$arr = array('deviceID' => '', 'deviceType' => '', 'deleted_at' => 'NULL');
					DB::table('users')->where('id', $user_id)->update($arr);
					$mess='User Successfully logout';
					if($language_code == 'de')
					{
						$mess='Benutzer Erfolgreich abmelden';
					}
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					return json_encode($resultArray);	exit;			
				}	  
			}
			else{
				$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return json_encode($resultArray);exit;
			}
		}
		else{
			$resultArray['status']='0';
			$resultArray['message']=trans('Invalid parameter');
			return json_encode($resultArray);exit;
		}
	}

	public function blogList()
	{
		$access_token = Request::header('accesstoken');
		$check_auth=$this->checkToken($access_token);
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			$path = URl::to('/storage/app/public/img/blog/');
			$bloglist1 = DB::table('blogs')
						->join('users', 'blogs.created_by', '=', 'users.id')
						->select('blogs.id','blogs.name', 'blogs.publish_datetime', 'blogs.featured_image', 'blogs.content', 'users.first_name', 'users.last_name')
						->where('blogs.status', 'Published')->where('blogs.deleted_at', Null)->limit(10)->get();

			if($bloglist1){
				$bloglist=array();
				foreach ($bloglist1 as $key => $value) {
					$bloglist1[$key]->path = $path;
					$bloglist[$key] = $value;
					$bloglist[$key]->created_by = $value->first_name.' '.$value->last_name;
				}
				$resultArray["blogs"] 	= $bloglist;
				$resultArray['status']	='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']	=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;		
			}else{
				$mess='Blog not found';
				if($language_code == 'de')
				{
					$mess='Blog nicht gefunden';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}		
	}

	public function blogDetails()
	{  
		$access_token = Request::header('accesstoken');
		$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? $_REQUEST['id'] : '' ;
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$user_arr = DB::table('users')->where('id', $user_id)->first();
		$language_code = $user_arr->language_code;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$path = URl::to('/storage/app/public/img/blog/');
			$blogDetail = DB::table('blogs')
						->join('users', 'blogs.created_by', '=', 'users.id')
						->select('blogs.id','blogs.name', 'blogs.publish_datetime', 'blogs.featured_image', 'blogs.content', 'blogs.created_at', 'users.first_name', 'users.last_name')
						->where('blogs.id', $id)
						->where('blogs.status', 'Published')
						->first();  		
			if(!empty($blogDetail)){
				$blogDetail->path = $path;
				$resultArray["share_url"] = URL::to('/blogs').'/'.$id.'/details';
				$resultArray["blogs"] = $blogDetail;
				$resultArray['status']='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}else{
				$mess='No Blog Found';
				if($language_code == 'de')
				{
					$mess='Kein Blog gefunden';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	 
		}
	}

	public function uploadMedia()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$image = isset($_REQUEST['image']['name']) && !empty($_REQUEST['image']['name']) ? trim($_REQUEST['image']['name']) : '' ;
		/*$resultArray['status']='0';
		$resultArray['message']="Testing Message";
		$resultArray['user_id']=$user_id;
		$resultArray['img']=$_FILES;
		return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;*/ 
		$user_arr = DB::table('users')->where('id', $user_id)->first();
		$language_code = $user_arr->language_code;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
				$allowTypes = array('jpg','png','gif');
				$statusMsg = $errorMsg = $insertValuesSQL = $errorUpload = $errorUploadType = '';
				if(!empty($_FILES['image']['name']) && isset($_FILES['image']['name'])){
					foreach($_FILES['image']['name'] as $key=>$val){
						// File upload path
						$fileName = basename($_FILES['image']['name'][$key]);
						$targetFilePath = public_path()."/img/user/".$fileName;

						// Check whether file type is valid
						$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
						if(in_array($fileType, $allowTypes)){
							// Upload file to server
							$newfilename1 = rand(11111,99999).'_'.$fileName;
							if(move_uploaded_file($_FILES["image"]["tmp_name"][$key], public_path() . '/img/user/'.$newfilename1)){
							// Image db insert sql
								$insertValuesSQL .= $newfilename1.',';
							}else{
								$errorUpload .= $_FILES['image']['name'][$key].', ';
							}
						}else{
							$errorUploadType .= $_FILES['image']['name'][$key].', ';
						}
					}
				     $checkUser = DB::table('user_medias')->where('user_id', $user_id)->first();
				     if(!empty($checkUser)){
				     	$resultArray['user_id']    = trim($user_id);
						$imgs = $checkUser->filename;
						$imgs.=trim($insertValuesSQL);
				     	$imgArray['user_id']    = trim($user_id);
						$imgArray['filename']  = $imgs;
						$registerUserId = DB::table('user_medias')->where('user_id', $user_id)->update($imgArray);
						$str = explode(',', $imgs);
						$newval = array();
						$b= 0;
						foreach ($str as $key => $value) {
							if(!empty($value)){
								$newval[$b] = $value;
								$b=$b+1;
							}
						}
						$str1 = implode(',', $newval);
						$path = URL::to('img/user/');
						$resultArray['filename']   = $str1;
						$resultArray['path']= $path;
						$resultArray['status']='1';
						$mess='Successfully Updated';
						if($language_code == 'de')
						{
							$mess='Erfolgreich aktualisiert';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
				     }else{
						$resultArray['user_id']    = trim($user_id);
						$imgs = trim($insertValuesSQL);
						$imgArray['user_id']    = trim($user_id);
						$imgArray['filename']  = $imgs;
						$str = explode(',', $imgs);
						$newval = array();
						$b= 0;
						foreach ($str as $key => $value) {
							if(!empty($value)){
								$newval[$b] = $value;
								$b=$b+1;
							}
						}
						$str1 = implode(',', $newval);
						$resultArray['filename']   = $str1;
						$registerUserId = DB::table('user_medias')->insert($imgArray);
						$path = URL::to('img/user/');
						$resultArray['path']= $path;
						$resultArray['status']='1';
						$mess='Successfully Insert';
						if($language_code == 'de')
						{
							$mess='Erfolgreich einfügen';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				     }			 
				}else
				{
					$mess='Invalid Parameter';
					if($language_code == 'de')
					{
						$mess='Ungültiger Parameter';
					}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit; 
				}
		}
	}

	public function Faqs()
	{
		$access_token = Request::header('accesstoken');
		$check_auth=$this->checkToken($access_token);
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			$path = URl::to('/storage/app/public/img/faq/');
			$faqlist1 = DB::table('faqs')
						->join('users', 'faqs.created_by', '=', 'users.id')
						->select('faqs.id','faqs.question', 'faqs.answer', 'faqs.featured_image', 'faqs.created_at', 'users.first_name', 'users.last_name')
						->where('faqs.status', '1')->where('faqs.deleted_at', Null)->limit(10)->get();

			if($faqlist1){
				$faqlist=array();
				foreach ($faqlist1 as $key => $value) {
					$faqlist[$key] = $value;
					$faqlist[$key]->path = $path;
					$faqlist[$key]->featured_image = isset($value->featured_image) && !empty($value->featured_image) ? $value->featured_image : '';
					$faqlist[$key]->created_by = $value->first_name.' '.$value->last_name;
				}
				$resultArray["blogs"] 	= $faqlist;
				$resultArray['status']	='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']	=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;		
			}else{
				$mess='FAQ not found';
				if($language_code == 'de')
				{
					$mess='FAQ not found';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}		
	}

	public function faqsDetails()
	{  
		$access_token = Request::header('accesstoken');
		$id = isset($_REQUEST['id']) && !empty($_REQUEST['id']) ? $_REQUEST['id'] : '' ;
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			$path = URl::to('/storage/app/public/img/faq/');
			$faqsDetail = DB::table('faqs')
							->join('users', 'faqs.created_by', '=', 'users.id')
							->select('faqs.id','faqs.question', 'faqs.answer', 'faqs.featured_image', 'faqs.created_at', 'users.first_name', 'users.last_name')
							->where('faqs.id', $id)
							->where('faqs.status', 1)
							->first();
			if(!empty($faqsDetail)){
				$faqsDetail->featured_image = isset($faqsDetail->featured_image) && !empty($faqsDetail->featured_image)? $faqsDetail->featured_image:'';   
				$faqsDetail->path = $path;
				$resultArray["faqs"] = $faqsDetail;
				$resultArray['status']='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}else{
				$resultArray['status']='0';
				$mess='FAQ detail not found';
				if($language_code == 'de')
				{
					$mess='FAQ Detail nicht gefunden';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	 
		}
	}

	public function Language()
	{
		$access_token = Request::header('accesstoken');
		$check_auth=$this->checkToken($access_token);
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			$language = DB::table('languages')->where('status', 'Active')->get();
			if($language){
				$lang = array();
				foreach ($language as $key => $value) {
					$lang[$key]['id'] = isset($value->id) && !empty($value->id) ? $value->id : '';
					$lang[$key]['name'] = isset($value->name) && !empty($value->name) ? $value->name : '';
					$lang[$key]['short_name'] = isset($value->short_name) && !empty($value->short_name) ? $value->short_name : '';
					$lang[$key]['icon'] = isset($value->icon) && !empty($value->icon) ? $value->icon : '';
				}
				$resultArray["languages"] = $lang;
				$resultArray['status']='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}else{
				$resultArray['status']='0';
				$mess='Language not found';
				if($language_code == 'de')
				{
					$mess='Sprache nicht gefunden';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function PreFerences()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$language  = isset($_REQUEST['language']) && !empty($_REQUEST['language']) ? $_REQUEST['language'] : '' ;
		$minimumAgeGroup  = isset($_REQUEST['min_age_group']) && !empty($_REQUEST['min_age_group']) ? $_REQUEST['min_age_group'] : '' ;
		$maximumAgeGroup  = isset($_REQUEST['max_age_group']) && !empty($_REQUEST['max_age_group']) ? $_REQUEST['max_age_group'] : '' ;
		$recivePush  = isset($_REQUEST['receive_push']) && !empty($_REQUEST['receive_push']) ? $_REQUEST['receive_push'] : '' ;
		$puchSound   = isset($_REQUEST['push_sound']) && !empty($_REQUEST['push_sound']) ? $_REQUEST['push_sound'] : '' ;
		$accountHide  = isset($_REQUEST['hide_account']) && !empty($_REQUEST['hide_account']) ? $_REQUEST['hide_account'] : '' ;
		$switchAdd = isset($_REQUEST['switch_add']) && !empty($_REQUEST['switch_add']) ? $_REQUEST['switch_add'] : '' ;

		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			if(isset($language) && !empty($language) || isset($minimumAgeGroup) && !empty($minimumAgeGroup) || isset($maximumAgeGroup) && !empty($maximumAgeGroup) || isset($maximumAgeGroup) && !empty($maximumAgeGroup) || isset($recivePush) && !empty($recivePush) || isset($puchSound) && !empty($puchSound) || isset($accountHide) && !empty($accountHide))
			{
				$resultArray['user_id'] 		= trim($user_id);
				$resultArray['language'] 		= trim($language);
				$resultArray['min_age_group'] 	= trim($minimumAgeGroup);
				$resultArray['max_age_group'] 	= trim($maximumAgeGroup);
				$resultArray['receive_push']	= trim($recivePush);
				$resultArray['push_sound']		= trim($puchSound);
				$resultArray['hide_account'] 	= trim($accountHide);
				$resultArray['switch_add'] 		= trim($switchAdd);
				$checkUser = DB::table('users')->select('id')->where('id', $user_id)->first();
				$checkUser1 = DB::table('users')->where('id', $user_id)->first();
				//echo "<pre>"; print_r($checkUser1); exit;
				$language_code = $checkUser1->language_code;
				if(!empty($checkUser->id)){
					 $checkDetails = DB::table('user_details')->where('user_id', $checkUser->id)->first();
					 if(!empty($checkDetails)){
					 	 $updateUserDetails = DB::table('user_details')->where('user_id', $user_id)->update($resultArray);
						$resultArray['status']='1';
						$mess='Data Update Successfully';
						if($language_code == 'de')
						{
							$mess='Datenupdate erfolgreich';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					 }else{
					 	$insertUserDetails = DB::table('user_details')->insert($resultArray);
						$resultArray['status']='1';
						$mess='Data Update Successfully';
						if($language_code == 'de')
						{
							$mess='Datenupdate erfolgreich';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					 }				       	    
				}else{
					$resultArray['status']='0';
					$mess='User not found';
					if($language_code == 'de')
					{
						$mess='Benutzer wurde nicht gefunden';
					}
					$resultArray['message']="User not found";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function deleteAccount(){
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			if(isset($user_id) && !empty($user_id)){
				$user_arr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $user_arr->language_code;
				$resultArray['id'] = $user_id;
				$deleteUser = DB::table('users')->where('id', $user_id)->delete($resultArray);
				if($deleteUser){
					$resultArray['status']='1';
					$mess='Account Deleted Successfully';
					if($language_code == 'de')
					{
						$mess='Konto erfolgreich gelöscht';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$resultArray['status']='0';
					$mess='Account Not Deleted';
					if($language_code == 'de')
					{
						$mess='Konto nicht gelöscht';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
					$resultArray['status']='0';
					$resultArray['message']="Invalid Parameter";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}	
		}
	}

	public function reportProfile()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$seconduser_id = isset($_REQUEST['second_user_id']) && !empty($_REQUEST['second_user_id']) ? $_REQUEST['second_user_id'] : '' ;
		$reason  = isset($_REQUEST['reason']) && !empty($_REQUEST['reason']) ? $_REQUEST['reason'] : '' ;
		$message = isset($_REQUEST['message']) && !empty($_REQUEST['message']) ? $_REQUEST['message'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			if(isset($reason) && !empty($reason) && isset($message) && !empty($message)){
				$resultArray['user_id']	    	= trim($user_id);
				$resultArray['second_user_id']= trim($seconduser_id);				
				$resultArray['reason']  		= trim($reason);
				$resultArray['message'] 		= trim($message);				
				$reportProfile = DB::table('report_reason')->insert($resultArray);
				if($reportProfile){
					$resultArray['status']='1';
					$mess='Reason Insert Successfully';
					if($language_code == 'de')
					{
						$mess='Grund erfolgreich einfügen';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$resultArray['status']='0';
					$mess='Reason Not Insert';
					if($language_code == 'de')
					{
						$mess='Grund nicht einfügen';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$resultArray['status']='0';
				$mess='Invalid Parameter';
				if($language_code == 'de')
				{
					$mess='Ungültiger Parameter';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function chatList($value='')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($user_id) && !empty($user_id))
			{
				$userArr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $userArr->language_code;
				if(!empty($userArr))
				{
					$path = URL::to('img/user/');
					$chat = DB::table('messenger_participants')->where('user_id', $user_id)->pluck('thread_id')->toArray();
					//echo "<pre>"; print_r($chat);

					if(!empty($chat))
					{
						$asd = DB::table('messenger_participants')
							->join('users', 'messenger_participants.user_id', '=', 'users.id')
							->whereIn('messenger_participants.thread_id', $chat)
							->where('messenger_participants.user_id', '!=', $user_id)
							->select('messenger_participants.thread_id', 'messenger_participants.user_id as from_user_id', 'users.first_name', 'users.last_name', 'users.profile_pic', 'users.age', 'messenger_participants.created_at')
							->groupBy('messenger_participants.thread_id')
							->get()->toArray();
							//echo "<pre>"; print_r($chatList1); exit;
							if(!empty($asd)){
								$chatList = array();
								$mathches = array();
								$key =0;
								$key1 =0;
								foreach ($asd as $key2 => $value) {
									$img = DB::table('social_logins')->where('user_id', $value->from_user_id)->first();
									$mess = DB::table('messenger_messages')->where('thread_id', $value->thread_id)->orderBy('id', 'DESC')->first();
									//echo $value->thread_id; exit;
									if(!empty($mess)){
										$chatList[$key]['thread_id'] = $value->thread_id;
										$chatList[$key]['from_user_id'] = $value->from_user_id;
										$chatList[$key]['first_name'] = $value->first_name;
										$chatList[$key]['last_name'] = $value->last_name;
										$chatList[$key]['profile_pic'] = isset($img->avatar) && !empty($img->avatar) ? $img->avatar : '';
										$chatList[$key]['age'] = isset($value->age) && !empty($value->age) ? $value->age : '';
										$chatList[$key]['path'] = $path;
										$chatList[$key]['body1'] = $mess->body;
										$chatList[$key]['created_at'] = $mess->created_at;
										$key = $key+1;
										
										
									}
									else
									{
										$mathches[$key1] = $value;
										$mathches[$key1]->age = isset($value->age) && !empty($value->age) ? $value->age : '';
										$mathches[$key1]->path = $path;
										$mathches[$key1]->body1 = '';
										$mathches[$key1]->profile_pic =  isset($img->avatar) && !empty($img->avatar) ? $img->avatar : '';
										$mathches[$key1]->created_at = isset($value->created_at) && !empty($value->created_at) ? $value->created_at : '';
										$key1 = $key1+1;
									}
									//$mathches[$key]->age = isset($value->age) && !empty($value->age) ? $value->age : '';
								}
								$resultArray['status']='1';
								$mess='User chat list';
								if($language_code == 'de')
								{
									$mess='Benutzer-Chat-Liste';
								}
								$resultArray['message']=$mess;
								$resultArray['data']=$chatList;
								$resultArray['mathches']=$mathches;
								return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
							}else{
								$chatList1 = DB::table('messenger_participants')
									->join('users', 'messenger_participants.user_id', '=', 'users.id')
									->whereIn('messenger_participants.thread_id', $chat)
									->where('messenger_participants.user_id', '!=', $user_id)
									->select('messenger_participants.thread_id', 'messenger_participants.user_id as from_user_id', 'users.first_name', 'users.last_name', 'users.profile_pic')
									->groupBy('messenger_participants.thread_id')
									->get()->toArray();
								$chatList = array();
								$mathches = array();
								if(!empty($chatList1)){
									foreach ($chatList1 as $key1 => $value) {
										$img = DB::table('social_logins')->where('user_id', $value->from_user_id)->first();
										$mathches[$key1] = $value;
										$mathches[$key1]->path = $path;
										$mathches[$key1]->body1 = '';
										$mathches[$key1]->profile_pic = isset($img->avatar) && !empty($img->avatar) ? $img->avatar : '';
									}
								}
								$resultArray['status']='0';
								$mess='No Data Found';
								if($language_code == 'de')
								{
									$mess='Keine Daten gefunden';
								}
								$resultArray['message']=$mess;
								$resultArray['mathches']=$mathches;
								return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
						}						
					}
					else
					{
						
						$resultArray['status']='0';
						$mess='Chat list not available.';
						if($language_code == 'de')
						{
							$mess='Chatliste nicht verfügbar.';
						}
						$resultArray['message']=$mess;	
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
					}
				}
				else
				{
					$resultArray['status']='0';
					$mess='Invalid User';
					if($language_code == 'de')
					{
						$mess='Ungültiger Benutzer';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
				}
			}
			else
			{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	
		}
	}

	public function chatDetails($value='')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$thread_id = isset($_REQUEST['thread_id']) && !empty($_REQUEST['thread_id']) ? $_REQUEST['thread_id'] : '' ;
		$from_user_id = isset($_REQUEST['from_user_id']) && !empty($_REQUEST['from_user_id']) ? $_REQUEST['from_user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($user_id) && !empty($user_id) && isset($thread_id) && !empty($thread_id) && isset($from_user_id) && !empty($from_user_id))
			{
				$userArr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $userArr->language_code;
				if(!empty($userArr))
				{
					$chat = DB::table('messenger_messages')->where('thread_id', $thread_id)->select("id", "thread_id", "user_id", "body" , "created_at")->orderBy('id', 'ASC')->get()->toArray();
					/*if(!empty($chat))
					{*/
						$path = URL::to('img/user/');
						$fromUserDetails = DB::table('users')->where('id', $from_user_id)->select('id', 'first_name', 'last_name', 'profile_pic', 'age')->first();
						$profile = DB::table('social_logins')->where('user_id', $from_user_id)->first();
						//echo "<pre>"; print_r($fromUserDetails); exit;
						$fromUserDetails->age = isset($fromUserDetails->age) && !empty($fromUserDetails->age) ? $fromUserDetails->age:'';
						$fromUserDetails->profile_pic = isset($profile->avatar) && !empty($profile->avatar) ? $profile->avatar:'';
		//echo "ff"; exit;
						$fromUserDetails->path = $path;
						$resultArray['status']='1';
						$mess='User chat list';
						if($language_code == 'de')
						{
							$mess='Benutzer-Chat-Liste';
						}
						$resultArray['message']=$mess;
						$resultArray['fromUserDetails']=$fromUserDetails;
						$resultArray['data']=$chat;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;					
					/*}
					else
					{
						$resultArray['status']='0';
						$resultArray['message']="Chat message not available.";
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
					}*/
				}
				else
				{
					$resultArray['status']='0';
					$mess='Invalid User';
					if($language_code == 'de')
					{
						$mess='Ungültiger Benutzer';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
				}
			}
			else
			{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	
		}
	}

	public function sendMessage($value='')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$thread_id = isset($_REQUEST['thread_id']) && !empty($_REQUEST['thread_id']) ? $_REQUEST['thread_id'] : '' ;
		$from_user_id = isset($_REQUEST['from_user_id']) && !empty($_REQUEST['from_user_id']) ? $_REQUEST['from_user_id'] : '' ;
		$message = isset($_REQUEST['message']) && !empty($_REQUEST['message']) ? $_REQUEST['message'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($user_id) && !empty($user_id) && isset($from_user_id) && !empty($from_user_id))
			{
				$userArr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $userArr->language_code;
				if(!empty($userArr))
				{
					if($thread_id == -1)
					{
						$userData = DB::table('users')->where('id', $user_id)->first();
						$today = date('Y-m-d');
						if($userData->hey_date == $today){
							$count = $userData->hey_count + 1;
							$Updatehey = DB::table('users')->where('id', $user_id)->update(['hey_count' => $count]);
						}else{
							$count = 1;
							$Updatehey = DB::table('users')->where('id', $user_id)->update(['hey_count' => $count, 'hey_date' => $today]);
						}
						$arr = array('user_id' => $user_id, 'block_user_id' => $from_user_id);
		  				$insertUserBloacks  = DB::table('user_blocks')->insert($arr);
						$check1 = DB::table('messenger_participants')->where('user_id', $user_id)->pluck('thread_id')->toArray();
						$check2 = DB::table('messenger_participants')->where('user_id', $from_user_id)->pluck('thread_id')->toArray();
						$check3 = array_intersect($check1,$check2);
						
						if(!empty($check3))
						{
							if(isset($check3[0]) && !empty($check3[0])){
								$thread_id = $check3[0];	
							}else{
								$thread_id = $check3[1];	
							}
						}
						else
						{
							$threadArr = array('created_by' => $user_id, 'subject' => 'New Chat');
							$thread_id = DB::table('messenger_threads')->insertGetId($threadArr);
							$arr1 = array('thread_id' => $thread_id, 'user_id' => $user_id);
							$arr2 = array('thread_id' => $thread_id, 'user_id' => $from_user_id);
							DB::table('messenger_participants')->insert($arr1);
							DB::table('messenger_participants')->insert($arr2);
						}
					}
					else{
						$arr = array('thread_id' => $thread_id, 'user_id' => $user_id, 'body' => $message, 'message_type' => 'new');
						$insertData  = DB::table('messenger_messages')->insert($arr);
					}
					/*Push Notification Start*/
					$thread_id = $thread_id;
					$userArr1 = DB::table('users')->where('id', $from_user_id)->first();
					if(!empty($userArr1->deviceID) && !empty($userArr1->deviceType))
					{
						//echo "hh"; exit;
						$regid = $userArr1->deviceID;
						$title = isset($message) && !empty($message) ? 'New Message Received ' : 'New User Hey';
						$message = isset($message) && !empty($message) ? $message : 'Hey' ;
						$devicetype = $userArr1->deviceType;
						$device_id = $userArr1->deviceID;
						if($devicetype == 'Android')
						{
							$this->postpushnotification($regid,$title,$message,$devicetype,$thread_id,$from_user_id);
						}
						if($devicetype == 'iOS')
						{
							/*echo "ff"; exit;*/
							$this->iospush($device_id,$message,$thread_id,$from_user_id);
						}
					}
					/*Push Notification End*/
					$resultArray['thread_id'] 	=$thread_id;
					$resultArray['status'] 		='1';
					$mess='Message send successfully.';
					if($language_code == 'de')
					{
						$mess='Nachricht erfolgreich gesendet.';
					}
					$resultArray['message']		=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				} 
				else
				{
					$resultArray['status']='0';
					$mess='Invalid User';
					if($language_code == 'de')
					{
						$mess='Ungültiger Benutzer';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
				}
			}
			else
			{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	
		}
	}

	public function iospush($device_id,$message,$thread_id,$from_user_id)
	{
		//echo "aa"; exit;
		$lang = !empty($_REQUEST['lang']) ? $_REQUEST['lang'] : "en";
		App::setLocale($lang);
		$apnsHost = 'gateway.push.apple.com';//'gateway.sandbox.push.apple.com';
		//$apnsHost = 'gateway.sandbox.push.apple.com';
		$apnsCert = public_path().'/ck.pem';
		$apnsPort = 2195;
		$apnsPass = '';
		$token = $device_id;

		$payload['aps'] = array('alert' => $message, 'badge' => 1, 'sound' => 'default' , 'thread_id' => $thread_id, 'from_user_id' => $from_user_id);
		$output = json_encode($payload);
		$token = pack('H*', str_replace(' ', '', $token));
		$apnsMessage = chr(0).chr(0).chr(32).$token.chr(0).chr(strlen($output)).$output;

		$streamContext = stream_context_create();
		stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
		stream_context_set_option($streamContext, 'ssl', 'passphrase', $apnsPass);

		$apns = stream_socket_client('ssl://'.$apnsHost.':'.$apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		$result= fwrite($apns, $apnsMessage, strlen($apnsMessage));//fwrite($apns, $apnsMessage);
		fclose($apns);
		if (!$result)
			$a= 'Message not delivered' . PHP_EOL;
		else
			$a= 'Message successfully delivered' . PHP_EOL;

		$log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
	    "Attempt: ".($result[0]['success']=='1'?'Success':'Failed').PHP_EOL.
	    "Pass: ".$result.PHP_EOL.
	    "apns: ".$apns.PHP_EOL.
	    "apnsMessage: ".$apnsMessage.PHP_EOL.
	    "Pass: ".$a.PHP_EOL.
	    "-------------------------".PHP_EOL;
		//Save string to log, use FILE_APPEND to append.
		file_put_contents(public_path().'/log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
	}

	public function postpushnotification($regid=Null,$title=Null,$message=Null,$devicetype=Null,$thread_id=Null,$from_user_id=Null)
	{
		$msg = array('message' =>$message,'title' => $title, 'thread_id' =>$thread_id, 'from_user_id' => $from_user_id);
		$fields = array(
	        'to' => $regid,
	        'notification' => array('title' => $title, 'body' => $message, 'thread_id' => $thread_id, 'from_user_id' => $from_user_id),
	        'data' => $msg,
	    );
		$response = $this->sendPushNotification($fields,$devicetype);
		return true;
	}

	function sendPushNotification($fields = array(),$devicetype=Null)
	{
		$API_ACCESS_KEY = 'AAAAuVL5X3c:APA91bH_LT_m6AuoQn4BGxiBlGKABa0RKg4v0DczZdQtD6zhByXd6cQ0g47GQIkbD3sPGdzti4T6PEtQyM9XUVnM487yvdesLN-89E5DFTBo8vnuY2EcLvQlM2I2XRlxJLBfMYORGOnM';
		$headers = array
		(
			'Authorization: key=' . $API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		// Execute post
		$result = curl_exec($ch);
		//print_r($result);die;
		sleep(5);
		if ($result === FALSE) {
			die('Curl failed: ' . curl_error($ch));
		}
		// Close connection
		curl_close($ch);
		return $result;    
	}

	public function threadDelete($value = '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$thread_id = isset($_REQUEST['thread_id']) && !empty($_REQUEST['thread_id']) ? $_REQUEST['thread_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		$user_arr = DB::table('users')->where('id', $user_id)->first();
		$language_code = $user_arr->language_code;
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
		 	 $checkThread = DB::table('messenger_participants')->where('user_id', $user_id)->first();
		 	 if(!empty($checkThread)){
		 	     $deleteThread1 = DB::table('messenger_threads')
		 	     ->where('id', $thread_id)
		 	     ->delete();
		 	     $deletePartcipent = DB::table('messenger_participants')
		 	     ->where('thread_id', $thread_id)
		 	     ->delete();
		 	     $deleteMessage = DB::table('messenger_messages')
		 	     ->where('thread_id', $thread_id)
		 	      ->delete();
		 	     $resultArray['status']='1';
		 	     $mess='Delete Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich löschen';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
		 	 }else{
		 	 	$resultArray['status']='0';
		 	 	$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
		 	 }
		}
	}

	public function userPlans($value = ''){
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$userArr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $userArr->language_code;
			if(!empty($userArr)){
				$plan = DB::table('plans')->select('id', 'no_of_months', 'per_month_price', 'total_price')->get();
				$resultArray["plan"] = $plan;
				$resultArray['status']='1';
				$mess='Successfully';
				if($language_code == 'de')
				{
					$mess='Erfolgreich';
				}
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}			
		}
	}

	public function userTransaction($value = ''){
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$plan_id = isset($_REQUEST['plan_id']) && !empty($_REQUEST['plan_id']) ? $_REQUEST['plan_id'] : '' ;
		$number_of_month = isset($_REQUEST['number_of_month']) && !empty($_REQUEST['number_of_month']) ? $_REQUEST['number_of_month'] : '' ;
		$per_month_price = isset($_REQUEST['per_month_price']) && !empty($_REQUEST['per_month_price']) ? $_REQUEST['per_month_price'] : '' ;
		$total_price = isset($_REQUEST['total_price']) && !empty($_REQUEST['total_price']) ? $_REQUEST['total_price'] : '' ;
		$transaction_id = isset($_REQUEST['transaction_id']) && !empty($_REQUEST['transaction_id']) ? $_REQUEST['transaction_id'] : '' ;
		$transaction_date = isset($_REQUEST['transaction_date']) && !empty($_REQUEST['transaction_date']) ? $_REQUEST['transaction_date'] : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($user_id) && !empty($user_id) && isset($plan_id) && !empty($plan_id) && isset($number_of_month) && !empty($number_of_month) && isset($per_month_price) && !empty($per_month_price) && isset($total_price) && !empty($total_price) && isset($transaction_id) && !empty($transaction_id) && isset($transaction_date) && !empty($transaction_date)){
				$user_arr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $user_arr->language_code;
				/*Stripe gatway start*/
				require_once('stripe/stripe-php/init.php');
				\Stripe\Stripe::setApiKey('sk_test_sMmpotDSNdJstDiVRGPWSdBl00T7nFgPSb');
				$message='';
				$token = $transaction_id;
				/* $customer = \Stripe\Customer::create(array(
					"email" => "p@mailinator.com",
					"source" => $token,
				));*/
				try{
					$charge = \Stripe\Charge::create(array(
						"amount" => $total_price*100,
						"currency" => "usd",
						"description" => "Example charge",
						"source" => $token,
					));
				}
				catch(\Stripe\Error\Card $e){
					$messageTitle = 'Card Declined';
					$body = $e->getMessage();
					//$err  = $body['error'];
					$message = $body;
				}
				catch (\Stripe\Error\InvalidRequest $e){
					$body = $e->getMessage();
					$messageTitle = 'Oops...';
					$message = $body;
				}
				catch (\Stripe\Error\Authentication $e){
					$body = $e->getMessage();
					$messageTitle = 'Oops...';
					$message = $body;
				}
				catch (\Stripe\Error\ApiConnection $e) {
					$body = $e->getMessage();
					$messageTitle = 'Oops...';
					$message = $body;
				}
				catch (Stripe_Error $e){
					$messageTitle = 'Oops...';
					$message = 'It looks like my payment processor encountered an error. Please contact me before re-trying.';
				}
				catch (Exception $e){
					$messageTitle = 'Oops...';
					$message = 'It appears that something went wrong with your payment. Please contact me before re-trying.';
				}
				if(empty($message))
				{
					$cid = isset($charge->id) && !empty($charge->id) ? $charge->id :'';
					$arr = array('user_id' => $user_id, 'plan_id' => $plan_id, 'number_of_month' => $number_of_month, 'per_month_price' => $per_month_price, 'total_price' => $total_price, 'transaction_id' => $cid, 'transaction_date' => $transaction_date,);
					DB::table('transactions')->insert($arr);

					 $date = date('Y-m-d', strtotime('+'.$number_of_month.' month'));
					 $date1 = date('Y-m-d', strtotime('-1 day', strtotime($date)));
					 $array1 = array(
						'plan_id' 		  	=> $plan_id,
						'plan_start_date' 	=> $transaction_date,
						'plan_end_date'	  	=> $date1,
						'plan_status' 		=> 'Active',

					);
					$updatePlan = DB::table('users')->where('id', $user_id)->update($array1);
					
					$resultArray['transaction'] = $array1;
					$resultArray['status']='1';
					$mess='Payment Successfully.';
					if($language_code == 'de')
					{
						$mess='Zahlung erfolgreich.';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
				else{
					$resultArray['status']='0';
					$resultArray['message']=$message;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
				/*Stripe gatway End */
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function userCurrentLocation($value = '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$longlatitude = isset($_REQUEST['longlatitude']) && !empty($_REQUEST['longlatitude']) ? $_REQUEST['longlatitude'] : '' ;
		$latitude = isset($_REQUEST['latitude']) && !empty($_REQUEST['latitude']) ? $_REQUEST['latitude'] : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($user_id) && !empty($user_id) && isset($longlatitude) && !empty($longlatitude) && isset($latitude) && !empty($longlatitude)){
					$checkUSer = DB::table('users')->where('id', $user_id)->first();
					$language_code = $checkUSer->language_code;
					if(!empty($checkUSer)){
						$insertLocation = DB::table('users')->where('id',$user_id)->update(['longlatitude' => $longlatitude, 'latitude' => $latitude]);
						$resultArray['status']='1';
						$mess='Data Update Successfully';
						if($language_code == 'de')
						{
							$mess='Datenupdate erfolgreich';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}else{
						$resultArray['status']='0';
						$mess='Invalid User';
						if($language_code == 'de')
						{
							$mess='Ungültiger Benutzer';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}

			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function userSkip($value = '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$block_user_id = isset($_REQUEST['block_user_id']) && !empty($_REQUEST['block_user_id']) ? $_REQUEST['block_user_id'] : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			if(isset($user_id) && !empty($user_id) && isset($block_user_id) && !empty($block_user_id)){
				if(!empty($user_arr)){
					$arr = array('user_id' => $user_id, 'block_user_id' => $block_user_id);
		  			$insertUserBloacks  = DB::table('user_blocks')->insert($arr);
		  			$resultArray['status']='1';
		  			$mess='Data Insert Successfully';
					if($language_code == 'de')
					{
						$mess='Daten erfolgreich einfügen';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$resultArray['status']='0';
					$resultArray['message']="Invalid Parameter";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function deleteProfileImage($value = '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$image = isset($_REQUEST['filename']) && !empty($_REQUEST['filename']) ? $_REQUEST['filename'] : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			if(!empty($user_arr)){
				if(isset($image) && !empty($image)){
					$path = URL::to('img/user/');
					$getImage = DB::table('user_medias')->where('user_id', $user_id)->first();
					if(!empty($getImage)){
					$imgArr = $getImage->filename;
					$image = $image.',';
					$imgArr = str_replace($image, '', $imgArr);
					DB::table('user_medias')->where('id', $getImage->id)->update(['filename' => $imgArr]);
					$getImage = DB::table('user_medias')->where('user_id', $user_id)->first();
					if(!empty($getImage)){
				 		$array = explode(',', $getImage->filename);
				 	 	array_pop($array);
					}
					$resultArray['path']=$path;
					$resultArray['media']=$array;
					$resultArray['status']='1';
					$mess='Image Successfully Deleted';
					if($language_code == 'de')
					{
						$mess='Bild erfolgreich gelöscht';
					}
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}else{
						
						$resultArray['status']='0';
						$mess='No Image Found';
						if($language_code == 'de')
						{
							$mess='Kein Bild gefunden';
						}
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}	
				}else{
					$resultArray['status']='0';
					$resultArray['message']="Invalid Parameter";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}

		}
	}

	public function getUserData($value = '')
	{
		$access_token = Request::header('accesstoken');
		$email = isset($_REQUEST['email']) && !empty($_REQUEST['email']) ? trim($_REQUEST['email']) : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if(isset($email) && !empty($email)){
				 $chekEmail = DB::table('users')->where('email', $email)->first();
				 if(!empty($chekEmail)){
				 	$userData = DB::table('users')->select('*')->where('email', $email)->first();
				 	$userData->age = isset($userData->age) && !empty($userData->age) ? $userData->age :'';
					$userData->first_name = isset($userData->first_name) && !empty($userData->first_name) ? $userData->first_name :'';
					$userData->last_name = isset($userData->last_name) && !empty($userData->last_name) ? $userData->last_name :'';
					$userData->dob = isset($userData->dob) && !empty($userData->dob) ? $userData->dob :'';

					$userimg = DB::table('social_logins')->where('user_id', $userData->id)->first();
					$userData->profile_pic = isset($userimg->avatar) && !empty($userimg->avatar) ? $userimg->avatar :'';
					$userData->prefer_travel_gender = isset($userData->prefer_travel_gender) && !empty($userData->prefer_travel_gender) ? $userData->prefer_travel_gender :'';
					$userData->perfect_travel_friend = isset($userData->perfect_travel_friend) && !empty($userData->perfect_travel_friend) ? $userData->perfect_travel_friend :'';
					$userData->spend_free_time = isset($userData->spend_free_time) && !empty($userData->spend_free_time) ? $userData->spend_free_time :'';
					$userData->favorite_countries = isset($userData->favorite_countries) && !empty($userData->favorite_countries) ? $userData->favorite_countries :'';
					$userData->accommodations_id = isset($userData->accommodations_id) && !empty($userData->accommodations_id) ? $userData->accommodations_id :'';
					$userData->transports_id = isset($userData->transports_id) && !empty($userData->transports_id) ? $userData->transports_id :'';
					$userData->education_id = isset($userData->education_id) && !empty($userData->education_id) ? $userData->education_id :'';
					$userData->speak_language = isset($userData->speak_language) && !empty($userData->speak_language) ? $userData->speak_language :'';
					$userData->plan_id = isset($userData->plan_id) && !empty($userData->plan_id) ? $userData->plan_id :'';
					$userData->plan_start_date = isset($userData->plan_start_date) && !empty($userData->plan_start_date) ? $userData->plan_start_date :'';
					$userData->plan_end_date = isset($userData->plan_end_date) && !empty($userData->plan_end_date) ? $userData->plan_end_date :'';
					$userData->plan_status = isset($userData->plan_status) && !empty($userData->plan_status) ? $userData->plan_status :'';
					$userData->hey_date = isset($userData->hey_date) && !empty($userData->hey_date) ? $userData->hey_date :'';
					$userData->language_native = isset($userData->language_native) && !empty($userData->language_native) ? $userData->language_native :'';
					$userData->updated_by = isset($userData->updated_by) && !empty($userData->updated_by) ? $userData->updated_by :'';
					$userData->longlatitude = isset($userData->longlatitude) && !empty($userData->longlatitude) ? $userData->longlatitude :'';
					$userData->latitude = isset($userData->latitude) && !empty($userData->latitude) ? $userData->latitude :'';
					$userData->language_speak = isset($userData->language_speak) && !empty($userData->language_speak) ? $userData->language_speak :'';
					$userData->education = isset($userData->education) && !empty($userData->education) ? $userData->education :'';
					$userData->remember_token = isset($userData->remember_token) && !empty($userData->remember_token) ? $userData->remember_token :'';
					$userData->description = isset($userData->description) && !empty($userData->description) ? $userData->description :'';
					$userData->created_by = isset($userData->created_by) && !empty($userData->created_by) ? $userData->created_by :'';
					$userData->created_at = isset($userData->created_at) && !empty($userData->created_at) ? $userData->created_at :'';
					$userData->updated_at = isset($userData->updated_at) && !empty($userData->updated_at) ? $userData->updated_at :'';
					$userData->password = isset($userData->password) && !empty($userData->confirmation_code) ? $userData->confirmation_code :'';
					$userData->confirmation_code = isset($userData->confirmation_code) && !empty($userData->password) ? $userData->password :'';
					$userData->deleted_at = isset($userData->deleted_at) && !empty($userData->deleted_at) ? $userData->deleted_at :'';

					$resultArray['user'] = $userData;
					$resultArray['status'] = '1';
					$resultArray['message']="Successfully";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$resultArray['status']='0';
					$resultArray['message']="Email Not Found";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function Pages(){
		$access_token = Request::header('accesstoken');
		$type = isset($_REQUEST['type']) && !empty($_REQUEST['type']) ? trim($_REQUEST['type']) : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			if (isset($type) && !empty($type)) {
				$terms = DB::table('pages')->select('id', 'title', 'page_slug', 'description')->where('title' ,$type)->first();
				if(!empty($terms)){
					$resultArray['terms']= $terms;
					$resultArray['status']='1';
					$resultArray['message']="Successfully";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
				}else{
					$resultArray['status']='0';
					$resultArray['message']="Invalid Parameter";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}	
			}
		}
	}

	public function getPreFerences(){
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$language_code = '';
			if(isset($user_id) && !empty($user_id)){
				$userArr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $userArr->language_code;
				if($userArr){
					# preference Start
					$preference = array();
					$userPre = DB::table('user_details')->where('user_id', $userArr->id)->first();
					$preference['min_age_group'] = isset($userPre->min_age_group) && !empty($userPre->min_age_group) ? $userPre->min_age_group : 18 ;
					$preference['max_age_group'] = isset($userPre->max_age_group) && !empty($userPre->max_age_group) ? $userPre->max_age_group : 65 ;
					$preference['receive_push'] = isset($userPre->receive_push) && !empty($userPre->receive_push) ? $userPre->receive_push : 'Yes' ;
					$preference['push_sound'] = isset($userPre->push_sound) && !empty($userPre->push_sound) ? $userPre->push_sound : 'Yes' ;
					$preference['switch_add'] = isset($userPre->switch_add) && !empty($userPre->switch_add) ? $userPre->switch_add : 'off' ;
					$preference['hide_account'] = isset($userPre->hide_account) && !empty($userPre->hide_account) ? $userPre->hide_account : 'No' ;
					/*$langid = isset($userPre->language) && !empty($userPre->language) ? $userPre->language : 'No' ;
					$languages = '';
					if(!empty($langid)){
						$languages = DB::table('languages')->where('id', $langid)->select('id', 'name')->first();
					}*/
					 $st11 = isset($userPre->language) && !empty($userPre->language) ? $userPre->language : '' ;
					 $lang_code = isset($userArr->language_code) && !empty($userArr->language_code) ? $userArr->language_code : 'en';
					 
					 $value = DB::table('system_languages')->where('id', $st11)->first();
				  	$key = 0;
				  	$user_arr11[$key]['id'] = isset($value->id) && !empty($value->id) ? $value->id : '';
				  	$user_arr11[$key]['name'] = isset($value->language) && !empty($value->language) ? $value->language : '';
				  	$user_arr11[$key]['short_name'] = isset($value->language_code) && !empty($value->language_code) ? $value->language_code : '';
				  	$user_arr11[$key]['icon'] = isset($value->icon) && !empty($value->icon) ? $value->icon:'';
					  
					$preference['languages'] = $user_arr11;
					$resultArray['status']='1';
					$mess='user preferences data.';
					if($language_code == 'de')
					{
						$mess='Benutzereinstellungsdaten.';
					}
					$resultArray['message']=$mess;
					$resultArray['data']=$preference;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$mess='Invalid User';
					if($language_code == 'de')
					{
						$mess='Ungültiger Benutzer';
					}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
					$mess='Invalid Parameter';
					if($language_code == 'de')
					{
						$mess='Ungültiger Parameter';
					}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}	
		}
	}

	public function getSearchSetting(){
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$language_code='';
			if(isset($user_id) && !empty($user_id)){
				$userArr = DB::table('users')->where('id', $user_id)->first();
				$language_code = $userArr->language_code;
				if($userArr){
					# Start User Search Setting
					$searchSetting = array();
					$userSerch = DB::table('user_search_settings')->where('user_id', $userArr->id)->first();
					$searchSetting['region'] = isset($userSerch->region) && !empty($userSerch->region) ? $userSerch->region : '' ;
					$searchSetting['country'] = isset($userSerch->country) && !empty($userSerch->country) ? $userSerch->country : '' ;
					$str2 = isset($userSerch->country) && !empty($userSerch->country) ? $userSerch->country : '' ;
					if(!empty($str2)){
						$country1 = explode(',', $str2);
						$searchSetting['country'] = $country1;
					}
					else
					{
						$searchSetting['country'] = array();
					}
					$searchSetting['week_start'] = isset($userSerch->week_start) && !empty($userSerch->week_start) ? $userSerch->week_start : '' ;
					$searchSetting['week_end'] = isset($userSerch->week_end) && !empty($userSerch->week_end) ? $userSerch->week_end : '' ;
					$searchSetting['enable_more_searching'] = isset($userSerch->enable_more_searching) && !empty($userSerch->enable_more_searching) ? $userSerch->enable_more_searching : 'No' ;
					$array1 = array(); 
					$str = isset($userSerch->language_native) && !empty($userSerch->language_native) ? $userSerch->language_native : '' ;
					if(!empty($str))
					{
					$langID= explode(',', $str);
					$array11 = DB::table('languages')->whereIn('id', $langID)->select('id', 'name', 'short_name', 'icon')->get()->toArray();
					$array1 = array();
					 foreach ($array11 as $kl => $vl) {
					 	$array1[$kl]['id'] = isset($vl->id) && !empty($vl->id)?$vl->id:'';
					 	$array1[$kl]['name'] = isset($vl->name) && !empty($vl->name)?$vl->name:'';
					 	$array1[$kl]['short_name'] = isset($vl->short_name) && !empty($vl->short_name)?
					 	 $vl->short_name:'';
					 	$array1[$kl]['icon'] = isset($vl->icon) && !empty($vl->icon) ?$vl->icon:''; 
					 }
					}
					$searchSetting['language_native'] = $array1;
					$searchSetting['prefer_travel_gender'] = isset($userSerch->prefer_travel_gender) && !empty($userSerch->prefer_travel_gender) ? $userSerch->prefer_travel_gender : '' ;
					$searchSetting['location_range'] = isset($userSerch->location_range) && !empty($userSerch->location_range) ? $userSerch->location_range : '' ;
					$language = array();
					$lang = DB::table('languages')->get()->toArray(); 
					foreach ($lang as $all => $vll) {
						$language[$all]['id'] = isset($vll->id) && !empty($vll->id) ? $vll->id : '';
						$language[$all]['name'] = isset($vll->name) && !empty($vll->name) ? $vll->name : '';
						$language[$all]['short_name'] = isset($vll->short_name) && !empty($vll->short_name) ? $vll->short_name : '';
						$language[$all]['icon'] = isset($vll->icon) && !empty($vll->icon) ? $vll->icon : '';
					}

					# End User Search Setting
					$mess='User search data.';
					if($language_code == 'de')
					{
						$mess='Benutzersuchdaten.';
					}
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					$resultArray['data']=$searchSetting;
					$resultArray['language']=$language;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$mess='Invalid User';
					if($language_code == 'de')
					{
						$mess='Ungültiger Benutzer';
					}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$mess='Invalid Parameter';
				if($language_code == 'de')
				{
					$mess='Ungültiger Parameter';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}	
		}
	}

	######################################################

	public function generate_random_code($length = Null) 
	{
		$final_array = range('0','9');
		$random_code = '';
		while($length--) {
		  $key = array_rand($final_array);
		  $random_code .= $final_array[$key];
		}
		return $random_code;
	}

	public function userSearchSetting()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$searchRegion  = isset($_REQUEST['region']) && !empty($_REQUEST['region']) ? $_REQUEST['region'] : '' ;
		$country  = isset($_REQUEST['country']) && !empty($_REQUEST['country']) ? $_REQUEST['country'] : '' ;
		$weekStart = isset($_REQUEST['week_start']) && !empty($_REQUEST['week_start']) ? $_REQUEST['week_start'] : '' ;
		$weekEnd = isset($_REQUEST['week_end']) && !empty($_REQUEST['week_end']) ? $_REQUEST['week_end'] : '' ;
		$languageNative = isset($_REQUEST['language_native']) && !empty($_REQUEST['language_native']) ? $_REQUEST['language_native'] : '' ;

		$prefertravelGender = isset($_REQUEST['prefer_travel_gender']) && !empty($_REQUEST['prefer_travel_gender']) ? $_REQUEST['prefer_travel_gender'] : '' ;
		$locationRange = isset($_REQUEST['location_range']) && !empty($_REQUEST['location_range']) ? $_REQUEST['location_range'] : '' ;
		$enable_more_searching = isset($_REQUEST['enable_more_searching']) && !empty($_REQUEST['enable_more_searching']) ? $_REQUEST['enable_more_searching'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			/*if(isset($searchRegion) && !empty($searchRegion) && isset($weekStart) && !empty($weekStart) && isset($weekEnd) && !empty($weekEnd)){*/
				$resultArray = array();
				$resultArray['user_id'] 	= trim($user_id);
				$resultArray['region'] 		= trim($searchRegion);
				$resultArray['country'] 	= trim($country);
				$resultArray['week_start']  = trim($weekStart);
				$resultArray['week_end'] 	= trim($weekEnd);
				$resultArray['language_native'] 		= trim($languageNative);
				$resultArray['prefer_travel_gender']    = trim($prefertravelGender);
				$resultArray['location_range']    = trim($locationRange);
				$resultArray['enable_more_searching']    = trim($enable_more_searching);
				$user_arr1 = DB::table('users')->where('id', $user_id)->first();
				$language_code = $user_arr1->language_code;

				$user_arr = DB::table('user_search_settings')->where('user_id', $user_id)->first();
				
				if(!empty($user_arr)){
					$registerUserId = DB::table('user_search_settings')->
					where('user_id', $user_id)->update($resultArray);
					$mess='Data Update Successfully';
					if($language_code == 'de')
					{
						$mess='Datenupdate erfolgreich';
					}
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$registerUserId = DB::table('user_search_settings')->insert($resultArray);
					$mess='Data Insert Successfully';
					if($language_code == 'de')
					{
						$mess='Daten erfolgreich einfügen';
					}
					$resultArray['status']='1';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			/*}else{
				$resultArray['status']='0';
				$resultArray['message']="Invalid Parameter";
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}*/
		}
	}
	
	public function userCitySearch($value= '')
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$country = isset($_REQUEST['country']) && !empty($_REQUEST['country']) ? $_REQUEST['country'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$userArr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $userArr->language_code;
			if(!empty($userArr))
			{
				/*if(empty($userArr->perfect_travel_friend) && empty($userArr->spend_free_time) && empty($userArr->favorite_countries) &&empty($userArr->accommodations_id) && empty($userArr->transports_id) && empty($userArr->education_id) && empty($userArr->language_native) && empty($userArr->speak_language))
				{
					$resultArray['status']='0';
					$resultArray['message']="Please Fill Profile First";
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
				else
				{*/
					$checkSearch  = DB::table('user_search_settings')->where('user_id', $user_id)->first();
					if(empty($checkSearch))
					{
						$resultArray['status']='0';
						$resultArray['message']="Please Fill Search First";
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}
					else
					{
						/*New Searching Code Start*/
						$query = DB::table('users')
							->join('user_search_settings', 'users.id', '=', 'user_search_settings.user_id')
							->join('user_medias', 'users.id', '=', 'user_medias.user_id')
							->join('social_logins', 'users.id', '=', 'social_logins.user_id');
						$con = "`users`.`id` !=".$user_id;
						$checkPlanDate = DB::table('users')->select('plan_start_date', 'plan_end_date')->where('id', $user_id)->first();
						$today = date("Y-m-d");
						$subscription = 'No';
						if($today <= $checkPlanDate->plan_end_date)
						{
							$subscription = 'Yes';
						}
						if($subscription == 'Yes')
						{													
							if($checkSearch->enable_more_searching == 'yes')
							{
								$lon = $userArr->longlatitude;
								$lat = $userArr->latitude;
								$range= $checkSearch->location_range; 
								$query->select(DB::raw("6371 * acos(cos(radians(" . $lat . "))* cos(radians(users.latitude))* cos(radians(users.longlatitude) - radians(" . $lon . "))+ sin(radians(" .$lat. "))* sin(radians(users.latitude))) AS distance"),'users.id', 'social_logins.avatar', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'users.age', 'users.description','users.perfect_travel_friend', 'users.spend_free_time', 'users.favorite_countries', 'users.accommodations_id', 'users.transports_id', 'users.education_id', 'users.speak_language', 'users.longlatitude', 'users.latitude', 'users.hey_count', 'users.language_native', 'user_search_settings.region', 'user_search_settings.country', 'user_medias.filename')
								->having('distance', '<', $range);
							}
							else
							{
								$query->select('users.id', 'social_logins.avatar', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'users.age', 'users.description','users.perfect_travel_friend', 'users.spend_free_time', 'users.favorite_countries', 'users.accommodations_id', 'users.transports_id', 'users.education_id', 'users.speak_language', 'users.longlatitude', 'users.latitude', 'users.hey_count', 'users.language_native', 'user_search_settings.region', 'user_search_settings.country', 'user_medias.filename');
								if($checkSearch->region != 'All Regions')
								{
									$country = explode(',',$checkSearch->country);
									if(!empty($country))
									{
										$con.=" AND (";
										foreach ($country as $kc => $vc)
										{
											if($kc==0)
											{
												$con.= "find_in_set('".$vc."',user_search_settings.country)";
											}
											else
											{
												$con.= " OR find_in_set('".$vc."',user_search_settings.country)";
											}
										}
										$con.=" )";
									}
								}
							}
							#language_native condition start
							$language = explode(',',$checkSearch->language_native);
							if(!empty($language))
							{
								$con.=" AND (";
								foreach ($language as $kl => $vl)
								{
									if($kl==0)
									{
										$con.= "find_in_set('".$vl."',users.language_native)";
									}
									else
									{
										$con.= " OR find_in_set('".$vl."',users.language_native)";
									}
								}
								$con.=" )";
							}
							#language_native condition End
 							#Gender Condition Start
							if($checkSearch->prefer_travel_gender != 'everyone')
							{
								$query->where('users.gender', $checkSearch->prefer_travel_gender);
							}
							#Gender Condition End
						}
						else
						{
							$query->select('users.id', 'social_logins.avatar', 'users.first_name', 'users.last_name', 'users.email', 'users.gender', 'users.age', 'users.description','users.perfect_travel_friend', 'users.spend_free_time', 'users.favorite_countries', 'users.accommodations_id', 'users.transports_id', 'users.education_id', 'users.speak_language', 'users.longlatitude', 'users.latitude', 'users.hey_count', 'users.language_native', 'user_search_settings.region', 'user_search_settings.country', 'user_medias.filename');
							if($checkSearch->region != 'All Regions')
							{
								$country = explode(',',$checkSearch->country);
								if(!empty($country[0]))
								{
									$con.=" AND (";
									foreach ($country as $kc => $vc)
									{
										if($kc==0)
										{
											$con.= "find_in_set('".$vc."',user_search_settings.country)";
										}
										else
										{
											$con.= " OR find_in_set('".$vc."',user_search_settings.country)";
										}
									}
									$con.=" )";
								}
							}
						}
						#Age Condition Start
						$userDetail = DB::table('user_details')->where('user_id', $user_id)->first();
						if(!empty($userDetail))
						{
							if($userDetail->min_age_group !=0){
								$query->whereBetween('users.age', [$userDetail->min_age_group, $userDetail->max_age_group]);
							}
						}
						#Age Condition End
						#Hide Account Condition Start
						$userHide = DB::table('user_details')->where('hide_account', 'Yes')->pluck('user_id')->toArray();
						if(!empty($userDetail))
						{
							$query->whereNotIn('users.id', $userHide);
						}
						#Hide Account Condition End
						#Date Condition Start
						$todayDate = date('d/m/Y');
						if(!empty($checkSearch->week_start)){
							$query->where('user_search_settings.week_start', '<=', $todayDate);
						}
						if(!empty($checkSearch->week_end)){
							$query->where('user_search_settings.week_end', '>=', $todayDate);
						}
						#Date Condition End
						$checkUser1 =$query->whereRaw($con)
						->groupBy('users.id')
						->get()->toArray();
						/*New Searching Code End*/
						$checkUser = array();
						if($checkUser1)
						{
							foreach ($checkUser1 as $key => $value)
							{
								$checkUser[$key]['id'] = isset($value->id) && !empty($value->id)? $value->id : '';
								$checkUser[$key]['first_name'] 	= isset($value->first_name) && !empty($value->first_name) ? $value->first_name : '';
								$checkUser[$key]['last_name'] = isset($value->last_name) && empty($value->last_name) ? $value->last_name : '';
								$checkUser[$key]['email'] = isset($value->email) && !empty($value->email) ? $value->email : '';
								$checkUser[$key]['profile_pic'] = isset($value->avatar) && !empty($value->avatar) ? $value->avatar : '';
								$checkUser[$key]['gender'] = isset($value->gender) && !empty($value->gender) ? $value->gender : '';
								$checkUser[$key]['age'] = isset($value->age) && !empty($value->age) ? $value->age : '';
								$checkUser[$key]['prefer_travel_gender']  = isset($value->prefer_travel_gender) && !empty($value->prefer_travel_gender) ? $value->prefer_travel_gender : '';
								$checkUser[$key]['perfect_travel_friend'] = isset($value->perfect_travel_friend) && !empty($value->perfect_travel_friend) ? $value->perfect_travel_friend : '';
								$checkUser[$key]['spend_free_time'] = isset($value->spend_free_time) && !empty($value->spend_free_time) ? $value->spend_free_time : '';
								$checkUser[$key]['favorite_countries'] = isset($value->favorite_countries) && !empty($value->favorite_countries) ? $value->favorite_countries : '';
								$checkUser[$key]['hey_count'] = isset($value->hey_count) && !empty($value->hey_count) ? $value->hey_count : 0;
								$checkUser[$key]['description'] = isset($value->description) && !empty($value->description) ? $value->description : '';
								$checkUser[$key]['longlatitude'] = isset($value->longlatitude) && !empty($value->longlatitude) ? $value->longlatitude : '';
								$checkUser[$key]['latitude'] = isset($value->latitude) && !empty($value->latitude) ? $value->latitude : '';
								$checkUser[$key]['country'] = isset($value->country) && !empty($value->country) ? $value->country : '';
								$checkUser[$key]['week_start'] = isset($value->week_start) && !empty($value->week_start) ? $value->week_start : '';
								$checkUser[$key]['week_end'] = isset($value->week_end) && !empty($value->week_end) ? $value->week_end : '';
								$checkUser[$key]['week_end'] = isset($value->week_end) && !empty($value->week_end) ? $value->week_end : '';
								$checkUser[$key]['location_range'] = isset($value->location_range) && !empty($value->location_range) ? $value->location_range : '';
								$checkUser[$key]['travel_friend'] = isset($value->travel_friend) && !empty($value->travel_friend) ? $value->travel_friend : '';
								$storagePath = URl::to('/storage/app/public/img/');
								$str1 = isset($value->accommodations_id) && !empty($value->accommodations_id) ? $value->accommodations_id:'';
								$accId = explode(',', $str1);
								$accmodation = DB::table('accommodations')->whereIn('id', $accId)->get();
								$accomArray = array();
								foreach ($accmodation as $ac => $vc)
								{
									$accomArray[$ac]['id'] = isset($vc->id) && !empty($vc->id) ? $vc->id : '';
									$accomArray[$ac]['name'] = isset($vc->name) && !empty($vc->name) ? $vc->name : '';
									$accomArray[$ac]['icon'] = isset($vc->icon) && !empty($vc->icon) ? $storagePath.'/accomodation/'.$vc->icon : '';
								}
								$checkUser[$key]['accommodation'] = $accomArray;
								$str2 = isset($value->transports_id) && !empty($value->transports_id) ? $value->transports_id:'';
								$traId = explode(',', $str2);
								$transport = DB::table('transports')->whereIn('id', $traId)->get()->toArray();
								$transArray = array();
								foreach ($transport as $tc => $vt)
								{
									$transArray[$tc]['id'] = isset($vt->id) && !empty($vt->id) ? $vt->id : '';
									$transArray[$tc]['name'] = isset($vt->name) && !empty($vt->name) ? $vt->name : '';
									$transArray[$tc]['icon'] = isset($vt->icon) && !empty($vt->icon) ? $storagePath.'/transport/'.$vt->icon : '';
								}
								$checkUser[$key]['transport'] = $transArray;
								$str3 = isset($value->education_id) && !empty($value->education_id) ? $value->education_id:'';
								$eduId = explode(',', $str3);
								$education = DB::table('educations')->whereIn('id', $eduId)->get()->toArray();
								$eduArray = array();
								foreach ($education as $ed => $vd)
								{
									$eduArray[$ed]['id'] = isset($vd->id) && !empty($vd->id) ? $vd->id : '';
									$eduArray[$ed]['name'] = isset($vd->name) && !empty($vd->name) ? $vd->name : '';
									$eduArray[$ed]['icon'] = isset($vd->icon) && !empty($vd->icon) ? $storagePath.'/education/'.$vd->icon : '';
								}
								$checkUser[$key]['education'] = $eduArray;
								$str4 = isset($value->speak_language) && !empty($value->speak_language) ? $value->speak_language:'';
								$speakId = explode(',', $str4);
								$speaklang = DB::table('languages')->whereIn('id', $speakId)->get()->toArray();
								$speakArray = array();
								foreach ($speaklang as $sp => $vp)
								{
									$speakArray[$sp]['id'] = isset($vp->id) && !empty($vp->id) ? $vp->id : '';
									$speakArray[$sp]['name'] = isset($vp->name) && !empty($vp->name) ? $vp->name : '';
									$speakArray[$sp]['short_name'] = isset($vp->short_name) && !empty($vp->short_name) ? $vp->short_name : '';
									$speakArray[$sp]['icon'] = isset($vp->icon) && !empty($vp->icon) ? $storagePath.'/language/'.$vp->icon : '';
								}
								$checkUser[$key]['speak_language'] = 	$speakArray;
								$str5 = isset($value->language_native) && !empty($value->language_native) ? $value->language_native:'';
								$nativeId = explode(',', $str5);
								$nativelang = DB::table('languages')->whereIn('id', $nativeId)->get()->toArray();
								$nativeArray = array();
								foreach ($nativelang as $nt => $vl)
								{
									$nativeArray[$nt]['id'] = isset($vl->id) && !empty($vl->id) ? $vl->id : '';
									$nativeArray[$nt]['name'] = isset($vl->name) && !empty($vl->name) ? $vl->name : '';
									$nativeArray[$nt]['short_name'] = isset($vl->short_name) && !empty($vl->short_name) ? $vl->short_name : '';
									$nativeArray[$nt]['icon'] = isset($vl->icon) && !empty($vl->icon) ? $storagePath.'/language/'.$vl->icon : '';
								}
								$checkUser[$key]['language_native'] = 	$nativeArray;
								$str6 = isset($value->language) && !empty($value->language) ? $value->language:'';
								$languageId = explode(',', $str6);
								$searchlang = DB::table('languages')->whereIn('id', $languageId)->get()->toArray();
								$languageArray = array();
								foreach ($searchlang as $st => $vs)
								{
									$languageArray[$st]['id'] = isset($vs->id) && !empty($vs->id) ? $vs->id : '';
									$languageArray[$st]['name'] = isset($vs->name) && !empty($vs->name) ? $vs->name : '';
									$languageArray[$st]['short_name'] = isset($vs->short_name) && !empty($vs->short_name) ? $vs->short_name : '';
									$languageArray[$st]['icon'] = isset($vs->icon) && !empty($vs->icon) ? $storagePath.'/language/'.$vs->icon : '';
								}
								$checkUser[$key]['user_search_language'] = 	$languageArray;
								$str7 = isset($value->filename) && !empty($value->filename) ? $value->filename:'';
								$media = explode(',', $str7);
								array_pop($media);
								$path = URL::to('img/user/');
								$checkUser[$key]['mediapath'] = $path;
								$checkUser[$key]['media'] = $media;
							}
							$resultArray['user'] = $checkUser;
							$resultArray['status']='1';
							$resultArray['hey_count'] = isset($userArr->hey_count) && !empty($userArr->hey_count) ? $userArr->hey_count : 0;
							$mess='Successfully';
							if($language_code == 'de')
							{
								$mess='Erfolgreich';
							}
							$resultArray['message']=$mess;
							return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
						}
						$mess='Data not available.';
						if($language_code == 'de')
						{
							$mess='Keine Daten verfügbar.';
						}
						$resultArray['status']='0';
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}
				/*}*/
			}
			else
			{
				$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function updateProfileImage()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$image = isset($_REQUEST['image']['name']) && !empty($_REQUEST['image']['name']) ? trim($_REQUEST['image']['name']) : '' ;
		/*$resultArray['status']='0';
		$resultArray['message']="Testing Message";
		$resultArray['img']=$_FILES;
		return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit; */
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$user = DB::table('social_logins')->where('user_id', $user_id)->first();
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			if(!empty($user)){
				 if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0){
					$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/*", "png" => "image/png");
			        $filename = $_FILES["image"]["name"];
			        $filetype = $_FILES["image"]["type"];
			        $filesize = $_FILES["image"]["size"];
			        $ext = pathinfo($filename, PATHINFO_EXTENSION);
			        if(!array_key_exists($ext, $allowed)){
			        	$mess='Please select a valid file format jpg, png, jpeg, gif';
						if($language_code == 'de')
						{
							$mess='Bitte wählen Sie ein gültiges Dateiformat jpg, png, jpeg, gif';
						}
			        	$resultArray['status']='0';
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit; 
			        }
			        $maxsize = 5 * 1024 * 1024;
        			if($filesize > $maxsize){
     					$mess='File size is larger than the allowed limit 2mb.';
						if($language_code == 'de')
						{
							$mess='Die Dateigröße ist größer als die zulässige Grenze von 2 MB.';
						} 
        				$resultArray['status']='0';
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
					}
					if(in_array($filetype, $allowed)){
            		// Check whether file exists before uploading it
						if(file_exists("upload/" . $filename)){
							echo $filename . " is already exists.";
						} else{
							$targetFilePath = public_path()."/img/user/";
							$imagePath = URL::to('img/user');
							$newfilename1 = rand(11111,99999).'_'.$filename;
							move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath . $newfilename1);
							$userImage = DB::table('social_logins')->where('user_id', $user_id)->update(['avatar' => $imagePath.'/'.$newfilename1]);
							$resultArray['filename'] = $newfilename1;
							$resultArray['path'] = $imagePath;
							$resultArray['status']='1';
							$mess='Image Successfully Updated';
							if($language_code == 'de')
							{
								$mess='Bild erfolgreich aktualisiert';
							}
							$resultArray['message']=$mess;
							return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit; 
						}
					} else{
						$mess='Error: There was a problem uploading your file. Please try again.';
						if($language_code == 'de')
						{
							$mess='Fehler: Beim Hochladen Ihrer Datei ist ein Problem aufgetreten. Bitte versuche es erneut.';
						}
						$resultArray['status']='0';
						$resultArray['message']=$mess;
						return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit; 
					}
				}else{
					$mess='Something Went towrong';
					if($language_code == 'de')
					{
						$mess='Etwas ging schief';
					}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$mess='Invalid User';
				if($language_code == 'de')
				{
					$mess='Ungültiger Benutzer';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function checknotification()
	{
		$access_token = Request::header('accesstoken');
		$devicetype = isset($_REQUEST['devicetype']) && !empty($_REQUEST['devicetype']) ? $_REQUEST['devicetype'] : '' ;
		$device_id = isset($_REQUEST['device_id']) && !empty($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '' ;
		if(!empty($device_id)){
		$title = isset($message) && !empty($message) ? 'Message Received ' : 'New User Hey';
		$message = isset($message) && !empty($message) ? $message : 'Hey' ;
		$thread_id = 25;
		$from_user_id=30;
		$regid = $device_id;
		if($devicetype == 'Android')
		{
			$check = $this->postpushnotification($regid,$title,$message,$devicetype,$thread_id,$from_user_id);
			echo "<pre>"; print_r($check);
		}
		if($devicetype == 'iOS')
		{
			$this->iospush($device_id,$message,$thread_id,$from_user_id);
		}
		echo "if";exit; 
		}
		else{
			echo "else"; exit;
		}
	}

	public function systemLanguage()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$check_auth=$this->checkToken($access_token);
		if($check_auth['status']!=1){
			return json_encode($check_auth);
		}else{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			$language_code = $user_arr->language_code;
			$language = DB::table('system_languages')->where('is_active', 'Y')->where('deleted_at', NULL)->get()->toArray();
			if($language){
				$lang = array();
				foreach ($language as $key => $value) {
					$lang[$key]['id'] = isset($value->id) && !empty($value->id) ? $value->id : '';
					$lang[$key]['language'] = isset($value->language) && !empty($value->language) ? $value->language : '';
					$lang[$key]['language_code'] = isset($value->language_code) && !empty($value->language_code) ? $value->language_code : '';
				}
				$mess='System Language List';
				if($language_code == 'de')
				{
					$mess='Liste der Systemsprachen';
				}
				$resultArray["system_languages"] = $lang;
				$resultArray['status']='1';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;	
			}else{
				$mess='System Language not found';
				if($language_code == 'de')
				{
					$mess='Systemsprache nicht gefunden';
				}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}

	public function userLanguage()
	{
		$access_token = Request::header('accesstoken');
		$user_id = isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '' ;
		$language_code = isset($_REQUEST['language_code']) && !empty($_REQUEST['language_code']) ? $_REQUEST['language_code'] : '' ;
		$check_auth = $this->checkToken($access_token);
		if($check_auth['status']!=1)
		{
			return json_encode($check_auth);
		}
		else
		{
			$user_arr = DB::table('users')->where('id', $user_id)->first();
			if(isset($user_id) && !empty($user_id) && isset($language_code) && !empty($language_code)){
				if(!empty($user_arr)){
					$arr = array('language_code' => $language_code);
		  			DB::table('users')->where('id', $user_id)->update($arr);
					$sa = DB::table('system_languages')->where('language_code', $language_code)->first();
					$arr1 = array('language' => $sa->id);
		  			DB::table('user_details')->where('user_id', $user_id)->update($arr1);
		  			$mess='Change user language Successfully';
		  			if($language_code == 'de')
		  			{
		  				$mess='Benutzersprache erfolgreich ändern';
		  			}
		  			$resultArray['status']='1';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}else{
					$mess='Invalid User';
		  			if($language_code == 'de')
		  			{
		  				$mess='Ungültiger Benutzer';
		  			}
					$resultArray['status']='0';
					$resultArray['message']=$mess;
					return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
				}
			}else{
				$mess='Invalid Parameter';
	  			if($language_code == 'de')
	  			{
	  				$mess='Ungültiger Parameter';
	  			}
				$resultArray['status']='0';
				$resultArray['message']=$mess;
				return response()->json($resultArray,JSON_UNESCAPED_UNICODE);exit;
			}
		}
	}
} 
?>
