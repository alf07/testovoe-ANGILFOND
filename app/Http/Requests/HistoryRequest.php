<?php

namespace App\Http\Requests;

use App\Enum\NotificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
            ],

            'status' => [
                'nullable',
                'string',
                Rule::in(
                    array_column(NotificationStatus::cases(), 'value')
                ),
            ],

            'channel' => [
                'nullable',
                'string',
            ],

            'status_id' => [
                'nullable',
                'integer',
            ],
        ];
    }
}
