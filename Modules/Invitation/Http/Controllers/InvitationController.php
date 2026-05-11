<?php

namespace Modules\Invitation\Http\Controllers;

use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Invitation\Actions\SendInvitationAction;
use Modules\Invitation\Actions\AcceptInvitationAction;
use Modules\Invitation\Http\Requests\SendInvitationRequest;
use Modules\Invitation\Http\Requests\AcceptInvitationRequest;

class InvitationController
{
    public function send(SendInvitationRequest $request, SendInvitationAction $action)
    {
        $invitation = $action->execute(
            $request->user(),
            $request->email,
            $request->role
        );

        return ApiResponse::success($invitation, 'Invitation sent successfully.');
    }

    public function accept(AcceptInvitationRequest $request, AcceptInvitationAction $action)
    {
        $result = $action->execute($request->validated());

        return ApiResponse::success([
            'user' => $result['user'],
            'user_status' => $result['is_new_user'] ? 'new' : 'existing',
            'roles_added' => [$result['role']],
        ], 'Invitation accepted successfully.');
    }
}
