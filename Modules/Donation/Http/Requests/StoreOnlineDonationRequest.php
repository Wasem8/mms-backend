<?php

namespace Modules\Donation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOnlineDonationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id'        => ['required', 'integer'],
            'user_id'          => ['nullable', 'integer', 'exists:users,id'], // مسموح هنا للمدير
            'donor_name'       => ['nullable', 'string', 'max:255'],
            'donation_type'    => ['required', 'in:cash,in_kind'], // نقدي للصندوق، أو عيني للمسجد

            // المبلغ مطلوب فقط إذا كان التبرع نقدياً
            'amount'           => ['required_if:donation_type,cash', 'numeric', 'min:0.01'],

            // وصف البند مطلوب فقط إذا كان التبرع عينياً
            'item_description' => ['nullable', 'required_if:donation_type,in_kind', 'string', 'max:500'],
            'campaign_id'      => ['nullable', 'integer', 'exists:campaigns,id'],
            'mosque_need_id'   => ['nullable', 'integer', 'exists:mosque_needs,id'],

            // يمكنك إضافة حقل لتاريخ التبرع إذا كان المدير يدخل تبرعات سابقة
            'donation_date'    => ['nullable', 'date'],
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
                $v->errors()->add('donation_type', 'التبرعات للحملات يجب أن تكون نقدية فقط');
            }

            if ($needId && $type !== 'in_kind') {
                $v->errors()->add('donation_type', 'التبرعات لحاجات المسجد يجب أن تكون عينية فقط');
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
