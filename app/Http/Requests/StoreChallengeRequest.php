<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallengeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'bet_amount' => 'required|numeric|min:0',
            'participants' => 'required|array|min:1',
            'participants.*.id' => 'required',
            'participants.*.type' => 'required|in:user,guest',
            'failed_by' => 'required|array',
            'failed_by.id' => 'required',
            'failed_by.type' => 'required|in:user,guest'
        ];
    }
}
