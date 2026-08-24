<?php

namespace Modules\Community\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Community\Models\Sermon;

class SearchSermonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'             => 'nullable|in:Pending,Archived,Rejected',
            'category'           => ['nullable', Rule::in(Sermon::CATEGORIES)],
            'mosque_manager_id'  => 'nullable|integer|exists:users,id',
            'region_manager_id'  => 'nullable|integer|exists:users,id',
            'speaker_name'       => 'nullable|string|max:255',
            'keyword'            => 'nullable|string|max:255',
            'sermon_date_from'   => 'nullable|date',
            'sermon_date_to'     => 'nullable|date|after_or_equal:sermon_date_from',
            'submitted_from'     => 'nullable|date',
            'submitted_to'       => 'nullable|date|after_or_equal:submitted_from',
            'sort'               => 'nullable|string',
            'per_page'           => 'nullable|integer|min:1|max:100',
        ];
    }
}
