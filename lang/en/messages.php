<?php

return [

    'messages.otp_resent_successfully' => 'OTP verification code has been resent to your email.',

    // --- Students Section ---
    'students_retrieved' => 'Student list retrieved successfully.',
    'student_stored' => 'Student data registered successfully, please wait for supervisor approval to activate the account.',
    'student_retrieved' => 'Student data retrieved successfully.',
    'student_approved_and_assigned' => 'Student has been approved and assigned to the halaqa successfully.',
    'student_updated' => 'Student data updated successfully.',
    'student_deleted' => 'Student record deleted successfully.',
    'student_approved' => 'Student approved successfully.',
    'student_rejected' => 'Registration request rejected.',
    'student_already_active' => 'This student is already active.',
    'cannot_approve_rejected' => 'Cannot approve an already rejected student.',
    'cannot_reject_active' => 'Cannot reject an already active student.',
    'student_already_rejected' => 'This request is already rejected.',
    'target_halaqa_invalid' => 'Target halaqa not found or does not belong to your mosque.',
    'student_not_in_old_halaqa' => 'Sorry, the student is not registered in the selected old halaqa, please verify data.',
    'transfer_success' => 'Student transferred successfully to halaqa (:name)',

    // --- Halqas Section (New) ---
    'halaqat_retrieved' => 'Halaqat list retrieved successfully.',
    'halaqa_created' => 'Halaqa created successfully.',
    'halaqa_details' => 'Halaqa details retrieved successfully.',
    'halaqa_updated' => 'Halaqa updated successfully.',
    'halaqa_deleted' => 'Halaqa deleted successfully.',
    'students_attached' => 'Students added to halaqa successfully.',
    'student_detached' => 'Student removed from halaqa successfully.',

    // --- Validation & Service Logic ---
    'supervisor_no_mosque' => 'This supervisor is not linked to a mosque; cannot perform this action.',
    'student_not_found' => 'The following IDs do not exist in the system: :ids',
    'student_another_mosque' => 'Student (:name) belongs to another mosque.',
    'student_not_active' => 'Student (:name) is currently (:status) and cannot be added to a halaqa.',
    'student_already_exists' => 'Student (:name) is already registered in this halaqa.',
    'capacity_full' => 'Sorry, the halaqa cannot accommodate this number. Remaining seats: :remaining',
    'student_not_in_halaqa' => 'This student is not registered in this halaqa.',

    // --- Evaluations Section ---
    'evaluation_stored' => 'Student evaluated successfully.',
    'evaluation_retrieved' => 'Evaluations retrieved successfully.',
    'evaluation_updated' => 'Evaluation updated successfully.',
    'evaluation_deleted' => 'Evaluation deleted successfully.',
    'unauthorized_evaluation' => 'You are not authorized to evaluate for this halaqa.',
    'student_not_in_halaqa' => 'This student does not belong to this halaqa.',
    'unauthorized_edit_evaluation' => 'You are not authorized to edit this evaluation.',
    'unauthorized_delete_evaluation' => 'You are not authorized to delete this evaluation.',
    'evaluation_not_belongs_to_mosque' => 'This evaluation does not belong to your mosque.',

    // --- Notifications Section ---
    'notifications_retrieved' => 'Notifications retrieved successfully.',
    'notification_marked_read' => 'Notification marked as read.',
    'notifications_all_marked_read' => 'All notifications marked as read.',
    'notification_deleted' => 'Notification deleted successfully.',
];
