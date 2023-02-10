<?php

namespace App\Http\Requests\ContactUs;

use App\Http\Requests\Request;

class ContactUsRequest extends Request
{
    public function rules(): array
    {
        return [
            'name' => 'string|required',
            'email' => 'string|email|required',
            'text' => 'string|required'
        ];
    }
}
