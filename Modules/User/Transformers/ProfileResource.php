<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // تحديد المسمى الوظيفي والدور باللغة العربية
        $primaryRole = $this->roles->first()?->name;
        $roleTitle = match ($primaryRole) {
            'super_admin'       => 'مدير المنطقة',
            'mosque_manager'    => 'مدير مسجد',
            'halaqa_supervisor' => 'مشرف حلقات',
            'teacher'           => 'معلم',
            'parent'            => 'ولي أمر',
            'volunteer'         => 'متطوع',
            default             => 'مستخدم',
        };

        return [
            // 1️⃣ الهوية والمعلومات الشخصية
            'personal_info' => [
                'id'                 => $this->id,
                'first_name'         => $this->first_name,
                'last_name'          => $this->last_name,
                'full_name'          => $this->name ?? trim("{$this->first_name} {$this->last_name}"),
                'email'              => $this->email,
                'phone'              => $this->phone ?? 'غير محدد',
                'role'               => $primaryRole,
                'role_display'       => $roleTitle,
                'employee_code'      => 'MNG-' . date('Y') . '-' . str_pad($this->id, 3, '0', STR_PAD_LEFT), // رقم وظيفي افتراضي بناءً على ID
                'preferred_language' => 'العربية (الرئيسية)',
            ],

            // 2️⃣ بيانات المسجد المرتبط (إن وجد)
            'mosque_info' => $this->mosque ? [
                'id'            => $this->mosque->id,
                'name'          => $this->mosque->name,
                'code'          => $this->mosque->code ?? 'MSQ-' . str_pad($this->mosque->id, 4, '0', STR_PAD_LEFT),
                'imam_name'     => $this->mosque->imam ?? 'غير محدد',
                'khatib_name'   => $this->mosque->khatib ?? 'غير محدد',
                'city_district' => trim(($this->mosque->city ?? '') . ' - ' . ($this->mosque->district ?? ''), ' - ') ?: 'غير محدد',
                'status'        => $this->mosque->status ?? 'نشط',
            ] : null,

            // 3️⃣ أمان الحساب والحالة
            'account_security' => [
                'status'             => $this->status, // active / inactive
                'status_display'     => $this->status === 'active' ? 'نشط' : 'غير نشط',
                'verification_level' => $this->email_verified_at ? 'موثّق بالكامل' : 'غير موثّق',
                'is_email_verified'  => (bool) $this->email_verified_at,
                'has_fcm_token'      => (bool) $this->fcm_token,
                'created_at'         => $this->created_at?->format('Y-m-d'),
            ],
        ];
    }
}
