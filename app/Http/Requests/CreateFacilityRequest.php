<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateFacilityRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
            'category_id' => 'required', 
            'name' => 'required', 
            'latitude' => 'required', 
            'longitude' => 'required', 
            'contact'=>'digits_between:10,12|numeric',
            
		];
	}
}
