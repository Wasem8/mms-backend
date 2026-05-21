<?php

return [
    'messages.otp_resent_successfully' => 'تم إعادة إرسال رمز التحقق OTP إلى بريدك الإلكتروني.',

    // --- قسم الطلاب ---
    'students_retrieved' => 'تم استعادة قائمة الطلاب بنجاح.',
    'student_stored' => 'تم تسجيل بيانات الطالب بنجاح، يرجى انتظار موافقة المشرف لتفعيل الحساب.',
    'student_retrieved' => 'تم جلب بيانات الطالب بنجاح.',
    'student_updated' => 'تم تحديث بيانات الطالب بنجاح.',
    'student_approved_and_assigned' => 'تم قبول الطالب وإسناده للحلقة بنجاح.',
    'student_deleted' => 'تم حذف سجل الطالب بنجاح.',
    'student_approved' => 'تم قبول الطالب وتفعيل حسابه بنجاح.',
    'student_rejected' => 'تم رفض طلب التسجيل.',
    'student_already_active' => 'هذا الطالب مفعل مسبقاً.',
    'cannot_approve_rejected' => 'لا يمكن قبول طالب تم رفضه بالفعل.',
    'cannot_reject_active' => 'لا يمكن رفض طالب مقبول بالفعل.',
    'student_already_rejected' => 'هذا الطلب مرفوض مسبقاً.',
    'target_halaqa_invalid' => 'الحلقة المستهدفة غير موجودة أو لا تتبع لمسجدك.',
    'student_not_in_old_halaqa' => 'عذراً، الطالب غير مسجل في الحلقة القديمة المختارة، يرجى التأكد من البيانات.',
    'transfer_success' => 'تم نقل الطالب بنجاح إلى حلقة (:name)',

    // --- قسم الحلقات ---
    'halaqat_retrieved' => 'تم جلب قائمة الحلقات بنجاح.',
    'halaqa_created' => 'تم إنشاء الحلقة بنجاح.',
    'halaqa_details' => 'تم جلب تفاصيل الحلقة بنجاح.',
    'halaqa_updated' => 'تم تحديث بيانات الحلقة بنجاح.',
    'halaqa_deleted' => 'تم حذف الحلقة بنجاح.',
    'students_attached' => 'تم إضافة الطلاب إلى الحلقة بنجاح.',
    'student_detached' => 'تم إزالة الطالب من الحلقة بنجاح.',

    // --- المنطق البرمجي والتحقق ---
    'supervisor_no_mosque' => 'هذا المشرف غير مرتبط بمسجد، لا يمكنه القيام بهذه العملية.',
    'student_not_found' => 'المعرفات التالية غير موجودة في النظام: :ids',
    'student_another_mosque' => 'الطالب (:name) يتبع لمسجد آخر.',
    'student_not_active' => 'الطالب (:name) حالته (:status) ولا يمكن إضافته للحلقة.',
    'student_already_exists' => 'الطالب (:name) موجود بالفعل في هذه الحلقة.',
    'capacity_full' => 'عذراً، الحلقة لا تستوعب هذا العدد. المقاعد المتبقية: :remaining',
    'student_not_in_halaqa' => 'هذا الطالب غير مسجل في هذه الحلقة.',

    // --- قسم التقييمات ---
    'evaluation_stored' => 'تم تقييم الطالب بنجاح.',
    'evaluation_retrieved' => 'تم جلب التقييمات بنجاح.',
    'evaluation_updated' => 'تم تحديث التقييم بنجاح.',
    'evaluation_deleted' => 'تم حذف التقييم بنجاح.',
    'unauthorized_evaluation' => 'غير مصرح لك بالتقييم لهذه الحلقة.',
    'student_not_in_halaqa' => 'هذا الطالب غير تابع لهذه الحلقة.',
    'unauthorized_edit_evaluation' => 'غير مصرح لك بتعديل هذا التقييم.',
    'unauthorized_delete_evaluation' => 'غير مصرح لك بحذف هذا التقييم.',
    'evaluation_not_belongs_to_mosque' => 'التقييم لا يتبع لمسجدك.',

    // --- قسم الإشعارات ---
    'notifications_retrieved' => 'تم جلب الإشعارات بنجاح.',
    'notification_marked_read' => 'تم تحديد الإشعار كمقروء.',
    'notifications_all_marked_read' => 'تم تحديد كل الإشعارات كمقروءة.',
    'notification_deleted' => 'تم حذف الإشعار بنجاح.',
];
