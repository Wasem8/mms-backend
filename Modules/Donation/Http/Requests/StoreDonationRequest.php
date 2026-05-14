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
            'mosque_id'        => ['required', 'integer', 'exists:mosques,id'],
            'type'             => ['required', 'string', 'in:cash,kind'],

            'campaign_id'      => ['nullable', 'integer', 'exists:campaigns,id'],
            'mosque_need_id'   => ['nullable', 'integer', 'exists:mosque_needs,id'],

            'payment_method'   => ['required_if:type,cash', 'nullable', 'string', 'in:stripe,cash'],
            'amount'           => ['required_if:type,cash', 'nullable', 'numeric', 'min:5', 'max:999999'],
            'success_url'      => ['required_if:payment_method,stripe', 'nullable', 'url'],
            'cancel_url'       => ['required_if:payment_method,stripe', 'nullable', 'url'],
            'customer_email'   => ['nullable', 'email'],

            'item_description' => ['required_if:type,kind', 'nullable', 'string', 'max:500'],

            'donor_name'       => ['nullable', 'string', 'max:100'],
        ];
    }


    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $type   = $this->input('type');
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
