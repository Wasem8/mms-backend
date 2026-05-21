<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\User\Models\User;

class UpdateTameemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title'           => 'sometimes|required|string|max:255',
            'content'         => 'sometimes|required|string',
            'recipient_ids'   => 'sometimes|required|array',
            'recipient_ids.*' => [
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = User::find($value);
                    if (!$user || !$user->hasRole('mosque_manager')) {
                        $fail('أحد المستلمين غير موجود أو ليس مدير مسجد.');
                    }
                },
            ],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
