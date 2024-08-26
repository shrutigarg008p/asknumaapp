<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Redirect;
use Schema;
use App\UserMessage;
use App\Http\Requests\CreateUserMessageRequest;
use App\Http\Requests\UpdateUserMessageRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use DB;
use Auth;


class UserMessageController extends Controller {

	/**
	 * Display a listing of usermessage
	 *
     * @param Request $request
     *
     * @return \Illuminate\View\View
	 */
	public function index(Request $request)
    {
        @$main_message=Auth::user();
		$message= DB::table('numa_message')
			->select('numa_message.*')
			->where('status', '=','Active')
			->where('user_id', '=',$main_message->id)
			 ->orWhere('user_to', '=',$main_message->id)
			->orderBy('numa_message.created_at', 'asc')
            ->get();
			//print_r($message);die;
		 return view('admin.usermessage.view',compact('message','main_message'));
	}

	/**
	 * Show the form for creating a new usermessage
	 *
     * @return \Illuminate\View\View
	 */
	public function create()
	{
	    
	    
	    return view('admin.usermessage.create');
	}

	/**
	 * Store a newly created usermessage in storage.
	 *
     * @param CreateUserMessageRequest|Request $request
	 */
	public function store(CreateUserMessageRequest $request)
	{
	        $request = $this->saveFiles($request);
			$input=$request->all();
			$user_id=Auth::user()->id;
		//print_r($input);die;
			$exist_user= DB::table('numa_message_user')
			->select('id')
			->where('user', '=',$user_id)
            ->get();
			
			if(empty($exist_user))
			{
				DB::table('numa_message_user')->insert(
				['created_date'=>date('Y-m-d H:i:s'),'user'=>$user_id,'admin'=>1,'status'=>'Active']
				);
			}
			else{
			DB::table('numa_message_user')->where('user', $user_id)->update(
				['created_date'=>date('Y-m-d H:i:s')]
				);
			}
		$ch = curl_init("https://slack.com/api/chat.postMessage");
		$data = http_build_query([
			"token" => "xoxp-2274855213-71654124519-71891706131-548eb7ffb8",
			"channel" => "#mvpmessages", //"#mychannel",
			"text" => $input['message'], //"Hello, Foo-Bar channel message.",
			"username"=>Auth::user()->name
		]);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		$result=(json_decode($result));
		curl_close($ch);
		/*if($result->ok)
		{
			return 1;
		}else{
			return 0;
		}*/
		DB::table('numa_message')->insert(
			['user_to' => $input['user_to'],'message' => $input['message'],'user_id' => $user_id,'created_at'=>date('Y-m-d H:i:s'),'status'=>'Active',
			'parent_id'=>$input['parent_id'],'profile_pic'=>@$input['profile_pic'],'embedded'=>@$input['embedded']]
		);
	  // print_r();
		return redirect()->back();
	}
	public function postSlack($msg)
	{
		
	}
	/**
	 * Show the form for editing the specified usermessage.
	 *
	 * @param  int  $id
     * @return \Illuminate\View\View
	 */
	public function edit($id)
	{
		$usermessage = UserMessage::find($id);
	    
	    
		return view('admin.usermessage.edit', compact('usermessage'));
	}

	/**
	 * Update the specified usermessage in storage.
     * @param UpdateUserMessageRequest|Request $request
     *
	 * @param  int  $id
	 */
	public function update($id, UpdateUserMessageRequest $request)
	{
		$usermessage = UserMessage::findOrFail($id);

        

		$usermessage->update($request->all());

		return redirect()->route('admin.usermessage.index');
	}

	/**
	 * Remove the specified usermessage from storage.
	 *
	 * @param  int  $id
	 */
	public function destroy($id)
	{
		UserMessage::destroy($id);

		return redirect()->route('admin.usermessage.index');
	}

    /**
     * Mass delete function from index page
     * @param Request $request
     *
     * @return mixed
     */
    public function massDelete(Request $request)
    {
        if ($request->get('toDelete') != 'mass') {
            $toDelete = json_decode($request->get('toDelete'));
            UserMessage::destroy($toDelete);
        } else {
            UserMessage::whereNotNull('id')->delete();
        }

        return redirect()->route('admin.usermessage.index');
    }

}
