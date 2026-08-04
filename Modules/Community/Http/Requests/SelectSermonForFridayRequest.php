<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Community\DTOs\SelectSermonForFridayDTO;
use Carbon\Carbon;
class SelectSermonForFridayRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'sermon_id'   => ['required', 'integer', 'exists:sermons,id'],
            'friday_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $date = $this->input('friday_date');
            if ($date && Carbon::parse($date)->dayOfWeekIso !== 5) {
                $validator->errors()->add('friday_date', __('messages.community.friday_date_required'));
            }
        });
    }

    public function toDTO(): SelectSermonForFridayDTO
    {
        return new SelectSermonForFridayDTO(
            sermonId: (int) $this->validated('sermon_id'),
            fridayDate: $this->validated('friday_date'),
        );
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
