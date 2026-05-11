<?php

namespace Modules\Mosque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdateMosqueRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'working_hours' => ['nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'required', Rule::in(['active', 'maintenance', 'closed'])],
            'is_featured' => ['sometimes', 'nullable|in:true,false,1,0,true,false'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'district' => ['sometimes', 'required', 'string', 'max:100'],
            'latitude' => ['sometimes', 'required', 'numeric', 'decimal:0,8', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'decimal:0,8', 'between:-180,180'],
            'imam_id' => ['nullable', 'integer', 'exists:users,id'],
            'khatib_id' => ['nullable', 'integer', 'exists:users,id'],
            'manager_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereExists(function ($subquery) {
                        $subquery->select(DB::raw('1'))
                            ->from('role_user')
                            ->join('roles', 'roles.id', '=', 'role_user.role_id')
                            ->whereColumn('role_user.user_id', 'users.id')
                            ->where('roles.name', 'mosque_manager');
                    });
                }),
            ],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['required', 'integer', 'exists:facilities,id'],
            'spaces' => ['nullable', 'array'],
            'spaces.*.name' => ['required', 'string', 'max:255'],
            'spaces.*.capacity' => ['required', 'integer', 'min:1'],
            'spaces.*.type' => ['nullable', 'string'],
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
