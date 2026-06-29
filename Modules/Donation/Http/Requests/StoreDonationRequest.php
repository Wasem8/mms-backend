<?php

namespace Modules\Donation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
    return [
            'donor_name'      => ['sometimes', 'string', 'max:255'],
            'donation_type'  => ['required', 'in:cash,in_kind'],
            'payment_method'  => ['sometimes', 'in:cash,stripe'],
            'amount'          => ['sometimes', 'numeric', 'min:0.01'],
            'item_description' => ['nullable', 'string', 'max:500'],
            'status'          => ['sometimes', 'in:pending,completed'],
            'campaign_id'     => ['nullable', 'integer', 'exists:campaigns,id'],
            'mosque_need_id'  => ['nullable', 'integer', 'exists:mosque_needs,id'],
        ];

    }


    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type   = $this->input('donation_type');
            $needId = $this->input('mosque_need_id');
            $campId = $this->input('campaign_id');

            if ($needId && $campId) {
                $v->errors()->add('mosque_need_id', 'لا يمكن تحديد حاجة وحملة في نفس التبرع');
                return;
            }

            if ($campId && $type !== 'cash') {
                $v->errors()->add('type', 'التبرعات للحملات يجب أن تكون نقدية فقط');
            }

            if ($needId && $type !== 'kind') {
                $v->errors()->add('type', 'التبرعات لحاجات المسجد يجب أن تكون عينية فقط');
            }

            if ($type === 'kind' && $this->filled('payment_method')) {
                $v->errors()->add('payment_method', 'طريقة الدفع غير مسموحة مع التبرع العيني.');
            }

            if ($type === 'cash' && $this->filled('item_description')) {
                $v->errors()->add('item_description', 'وصف البند غير مطلوب للتبرعات النقدية.');
            }
        });
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
