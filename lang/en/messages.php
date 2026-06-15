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
    'evaluation_already_exists' => 'Evaluation already exists',
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


    //Donation
    // lang/en/messages.php  — add these keys
    'campaign_already_completed' => 'This campaign has already reached its target and is no longer accepting donations.',
    'exceeds_remaining'          => 'Your donation amount exceeds the remaining balance of :remaining :currency for this campaign.',
    'mosque_need_already_fulfilled' => 'This mosque need has already been fulfilled and is no longer accepting donations.',

    // --- Volunteer Section ---
    'opportunity_created' => 'Volunteer opportunity created successfully.',
    'opportunity_updated' => 'Volunteer opportunity updated successfully.',
    'opportunity_closed' => 'Volunteer opportunity closed successfully.',
    'opportunity_retrieved' => 'Volunteer opportunity retrieved successfully.',
    'opportunities_retrieved' => 'Volunteer opportunities retrieved successfully.',
    'application_submitted' => 'Application submitted successfully.',
    'application_approved' => 'Application approved successfully.',
    'application_rejected' => 'Application rejected successfully.',
    'applications_retrieved' => 'Applications retrieved successfully.',
    'my_applications_retrieved' => 'My applications retrieved successfully.',
    'task_assigned' => 'Task assigned successfully.',
    'task_completed' => 'Task marked as completed successfully.',
    'tasks_retrieved' => 'Tasks retrieved successfully.',
    'hours_logged' => 'Hours logged and evaluation saved successfully.',
    'hours_retrieved' => 'Total hours retrieved successfully.',
    'logs_retrieved' => 'Logs retrieved successfully.',
    'certificate_issued' => 'Certificate issued successfully.',
    'certificates_retrieved' => 'Certificates retrieved successfully.',
    'volunteer_not_found' => 'Volunteer or opportunity data not found.',
    'opportunity_closed_app' => 'This opportunity is closed for applications.',
    'pending_application_exists' => 'You already have a pending application for this opportunity.',
    'task_only_approved' => 'Tasks can only be assigned to approved applications.',
    'certificate_already_exists' => 'A certificate has already been issued for this volunteer and opportunity.',
    'no_hours_for_certificate' => 'No logged hours found. Cannot issue a certificate.',
    'upload_failed' => 'Failed to upload certificate: :error',

];
