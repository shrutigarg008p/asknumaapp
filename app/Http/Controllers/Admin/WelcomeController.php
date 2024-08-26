<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Redirect;
use Schema;
use DB;
use App\Test;
use App\Http\Requests\CreateTestRequest;
use App\Http\Requests\UpdateTestRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Traits\FileUploadTrait;
use Maatwebsite\Excel\Facades\Excel;
use Auth;
USE Session;
use App\SubCatetory;

class WelcomeController extends Controller {

	 public function postSearch(Request $request)
		{
			$request->session()->put('key', $request->all()['dieases']);
			return redirect('/search_result')->with('id_search', $request->all()['dieases']);
		}
	 public function unread_update(Request $request)
		{
		      
			$user_id=Auth::user()->id; 
			
		 	$unread_message= DB::table('numa_message')->select('id')->where('user_to', '=',$user_id)->where('read','=',0)->get();
		 	DB::table('numa_message')
				->where('user_to', '=',$user_id)->where('read','=',0)
				->update(['read'=>1]);
		}
	 public function unread(Request $request)
		{
			$user_id=Auth::user()->id;
		 	$unread_message= DB::table('numa_message')->select('id')->where('user_to', '=',$user_id)->where('read','=',0)->get();
		 	echo count($unread_message);
		}
	public function postSub_cat(Request $request)
		{
			$subcatetory = SubCatetory::where('category_id','=',$_POST['id'])->where('status','=','Active')->lists("sub_category_name", "id");
			$option='';
			foreach($subcatetory as $key=>$value)
			{
			$option .='<option value="'.$key.'" selected="selected">'.$value.'</option>';
			 } 
			echo ($option);
			
		}
	 public function postQuestion(Request $request)
	 {
		 $user_id=Auth::user()->id;
		 $exist_user= DB::table('numa_message_user')
			->select('id')
			->where('user', '=',$user_id)
            ->get();
			if(empty($exist_user))
			{
				DB::table('numa_message_user')->insert(
				['user'=>$user_id,'admin'=>1,'status'=>'Active']
				);
			}
			DB::table('numa_message')->insert(
				['diseases_article_id'=>$_POST['article_id'],'user_id'=>$user_id,'user_to'=>1,'age'=>$_POST['age'],'message' => $_POST['comment'],'gender' => $_POST['gender'],'created_at'=>date('Y-m-d H:i:s')]
				);
			$ch = curl_init("https://slack.com/api/chat.postMessage");
			$data = http_build_query([
			"token" => "xoxp-2274855213-71654124519-71891706131-548eb7ffb8",
			"channel" => "#mvpmessages", //"#mychannel",
			"text" => $_POST['comment'], //"Hello, Foo-Bar channel message.",
			"username"=>Auth::user()->name
			]);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($ch);
			$result=(json_decode($result));
			curl_close($ch);
			echo "done";
		 
	 }
	 public function postBookmark(Request $request)
	 {
		 $user_id=Auth::user()->id;
		 $exist_user= DB::table('numa_bookmark')
			->select('id')
			->where('user_id', '=',$user_id)
			->where('diseases_article_id', '=',$_POST['article_id'])
			->where('type', '=',$_POST['type'])
            ->get();
			if(empty($exist_user))
			{
				DB::table('numa_bookmark')->insert(
				['type'=>$_POST['type'],'user_id'=>$user_id,'diseases_article_id'=>$_POST['article_id'],'status'=>$_POST['status'],'created_date'=>date('Y-m-d H:i:s'),'updated_date'=>date('Y-m-d H:i:s')]
				);
			}else{
				DB::table('numa_bookmark')
				->where('user_id', '=',$user_id)
				->where('diseases_article_id', '=',$_POST['article_id'])
				->update(['status'=>$_POST['status'],'updated_date'=>date('Y-m-d H:i:s')]);
			}
	}
	public function postNewsletter(Request $request)
	 {
		 $exist_user= DB::table('newsletter')
			->select('id')
			->where('email', '=',$_POST['email'])
			->get();
			$apikey = '8954af9a4315019f1d0f8082f8925744-us9';
            		$auth = base64_encode( 'user:'.$apikey );

            $datas = array(
                'apikey'        => $apikey,
                'email_address' => $_POST['email'],
                'status'        => 'subscribed',
            );
            $json_data = json_encode($datas);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://us9.api.mailchimp.com/3.0/lists/d29163d261/members/');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
                                                        'Authorization: Basic '.$auth));
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);                                                                                                                  

            $result = curl_exec($ch);
			if(empty($exist_user))
			{
				DB::table('newsletter')->insert(
				['email'=>$_POST['email'],'created_date'=>date('Y-m-d H:i:s')]
				);
				echo "1";
			}else{
				echo "3";
			}
	}
	public function postMessage_deny(Request $request)
	 {
	 			DB::table('users')
				->where('id', Auth::user()->id)
				->update(['flag'=>$_POST['value']]);
		 
	}
	 public function postYes_no(Request $request)
		{
			if (Auth::check())
			{
				$user_id=Auth::user()->id; 
			}
			else
			{
				$user_id =Session::getId(); 
			}
			$feedback= DB::table('feedback')
			->select('id')
			->where('diseasesarticle_id', '=',$_POST['id'])
			->where('type', '=',$_POST['type'])
			->where('user_id', '=',$user_id)
            		->get();
			if(!empty($feedback))
			{
				if($_POST['message']=='Yes')
				{
					$reason_id=$_POST['reason_id'];
					$reasons=$_POST['reasons'];
				}else{
						$reason_id=$_POST['reason_id'];
					$reasons=$_POST['reasons'];
				}
				DB::table('feedback')
				->where('diseasesarticle_id', $_POST['id'])
				->where('user_id', $user_id)
				->update(['user_id'=>$user_id,'feedback' => $_POST['message'],'reason_id' => $reason_id,'reasons' => $reasons,'updated_at'=>date('Y-m-d H:i:s')]);
			}
			else
			{
				if($_POST['message']=='Yes')
				{
					$reason_id=$_POST['reason_id'];
					$reasons=$_POST['reasons'];
				}else{
					$reason_id=$_POST['reason_id'];
					$reasons=$_POST['reasons'];
				}
				DB::table('feedback')->insert(
				['type'=>$_POST['type'],'status'=>'Active','user_id'=>$user_id,'diseasesarticle_id'=>$_POST['id'],'feedback' => $_POST['message'],'reason_id' => $reason_id,'reasons' => $reasons,'created_at'=>date('Y-m-d H:i:s')]
				);
			}

		}
}
