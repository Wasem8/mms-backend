<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\User\DTOs\UpdateProfileDTO;

class UpdateProfileRequest extends FormRequest
{
public function authorize(): bool
{
return true;
}

public function rules(): array
{
return [
'full_name' => ['required', 'string', 'max:150'],
'phone' => [
'required', 'string', 'max:20',
Rule::unique('users', 'phone')->ignore(Auth::id()),
],
'email' => [
'required', 'email', 'max:150',
Rule::unique('users', 'email')->ignore(Auth::id()),
],
];
}

public function toDTO(): UpdateProfileDTO
{
return new UpdateProfileDTO(
fullName: trim($this->validated('full_name')),
phone: $this->validated('phone'),
email: $this->validated('email'),
);
}
}
