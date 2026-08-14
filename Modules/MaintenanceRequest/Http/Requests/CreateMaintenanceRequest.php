<?php

namespace Modules\MaintenanceRequest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Modules\Mosque\Models\Mosque;

class CreateMaintenanceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('mosque_manager')) {
            throw ValidationException::withMessages([
                'mosque_id' => __('messages.maintenance.manager_only'),
            ]);
        }

        $mosque = Mosque::where('manager_id', $user->id)->first();

        if (! $mosque) {
            abort(403, __('messages.maintenance.no_mosque_assigned'));
        }

        // تجاهل أي mosque_id مُدخل يدوياً، واشتقاقه من التوكن دائماً
        $this->merge([
            'mosque_id' => $mosque->id,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'mosque_id'    => ['required', 'integer', 'exists:mosques,id'],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'max:5000'],
            'category'     => ['required', 'string', 'in:electrical,plumbing,carpentry,cleaning, other'],
            'priority'     => ['sometimes', 'string', 'in:low,medium,high,urgent'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes'        => ['nullable', 'string'],
            'files'        => ['nullable', 'array', 'max:10'],
            'files.*'      => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'mosque_id.required' => __('messages.maintenance.mosque_id_required'),
            'mosque_id.exists'   => __('messages.maintenance.mosque_id_exists'),
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
