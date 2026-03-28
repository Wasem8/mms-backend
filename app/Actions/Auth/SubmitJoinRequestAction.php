<?php

// app/Actions/Auth/SubmitJoinRequestAction.php

namespace App\Actions\Auth;

use App\Models\RegistrationRequest;
use Illuminate\Support\Facades\Hash;

class SubmitJoinRequestAction
{
    public function execute(array $data): RegistrationRequest
    {
        return RegistrationRequest::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']), // تشفير استباقي
            'age'          => $data['age'],
            'grade'        => $data['grade'],
            'parent_phone' => $data['parent_phone'],
            'address'      => $data['address'] ?? null,
            'current_hifz' => $data['current_hifz'] ?? null,
            'status'       => 'pending',
        ]);
    }
}
