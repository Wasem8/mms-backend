<?php

namespace Modules\Donation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'donor_name'      => ['sometimes', 'string', 'max:255'],

            'amount'          => ['sometimes', 'numeric', 'min:0.01', 'max:9999999.99'],
            'item_description' => ['sometimes', 'nullable', 'string', 'max:500'],

        

            'status'          => ['sometimes', 'in:pending,completed'],

            'campaign_id'     => ['sometimes', 'nullable', 'integer', 'exists:campaigns,id'],
            'mosque_need_id'  => ['sometimes', 'nullable', 'integer', 'exists:mosque_needs,id'],

        ];
    }

    public function messages(): array
    {
        return [
            'amount.numeric'     => 'المبلغ يجب أن يكون رقماً.',
            'amount.min'         => 'الحد الأدنى للتبرع هو 0.01.',
            'amount.max'         => 'المبلغ تجاوز الحد المسموح به.',
            'status.in'          => 'الحالة يجب أن تكون معلقة أو مكتملة.',
            'campaign_id.exists' => 'الحملة المحددة غير موجودة.',
        ];
    }
}
