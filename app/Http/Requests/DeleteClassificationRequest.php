<?php

namespace App\Http\Requests;

class DeleteClassificationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return ! empty(auth()->user()) && (auth()->user()->hasRole('administrator') || $this->userMadeClassification());
    }

    private function userMadeClassification()
    {
        $classificationId = $this->route('classifications');

        $classifications = app()->make('App\Repositories\ClassificationRepository');

        $user = $classifications->getUser($classificationId);

        return ! empty($user) && $user->getId() == auth()->user()->id;
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
