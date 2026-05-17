<?php

namespace Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Community\Services\TameemService;

class TameemController extends Controller
{
    protected $tameemService;

    public function __construct(TameemService $tameemService)
    {
        $this->tameemService = $tameemService;
    }

    public function index()
    {
        $tameems = $this->tameemService->getAllTameems();
        return response()->json(['message' => 'تم جلب التعاميم بنجاح', 'data' => $tameems]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_ids' => 'required|array',
            'recipient_ids.*' => 'exists:users,id'
        ]);

        $senderId = auth()->id();
        $recipientIds = $validatedData['recipient_ids'];

        $tameem = $this->tameemService->sendTameem($validatedData, $senderId, $recipientIds);

        return response()->json(['message' => 'تم إرسال التعميم بنجاح', 'data' => $tameem], 201);
    }

    public function myTameems()
    {
        $mosqueManagerId = auth()->id();
        $tameems = $this->tameemService->getMosqueManagerTameems($mosqueManagerId);

        return response()->json(['message' => 'تم جلب التعاميم الواردة بنجاح', 'data' => $tameems]);
    }

    public function markAsRead($id)
    {
        $mosqueManagerId = auth()->id();
        $this->tameemService->markTameemAsRead($id, $mosqueManagerId);

        return response()->json(['message' => 'تم تحديث حالة التعميم إلى مقروء']);
    }
}
