<?php

namespace App\Http\Requests;

use App\Repositories\Eloquent\ApiKeyRepository;
use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $apiKey = request()->input('api_key');

        if (empty($apiKey)) {
            return false;
        }

        return app(ApiKeyRepository::class)->doesApiKeyExist($apiKey);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
