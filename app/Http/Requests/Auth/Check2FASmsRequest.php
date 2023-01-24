<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class Check2FASmsRequest extends FormRequest
{
    public function authorize()
    {
        return !empty($this->user()->phone);
    }

    public function rules()
    {
        return [
            'code' => 'required'
        ];
    }
}
