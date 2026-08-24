<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

class ProfileEndpoints
{
    #[OA\Get(
        path: '/profile',
        operationId: 'getProfile',
        tags: ['Profile'],
        summary: 'عرض بيانات الملف الشخصي',
        description: 'يُرجع جميع البيانات الخاصة بالبروفايل للمستخدم المسجل دخوله (المعلومات الشخصية، بيانات المسجد المرتبط، وأمان الحساب).',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم جلب بيانات الملف الشخصي بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'personal_info',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'first_name', type: 'string', example: 'أحمد', nullable: true),
                                        new OA\Property(property: 'last_name', type: 'string', example: 'العتيبي', nullable: true),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'Mosque Manager'),
                                        new OA\Property(property: 'email', type: 'string', example: 'manager@test.com'),
                                        new OA\Property(property: 'phone', type: 'string', example: '0559876543', nullable: true),
                                        new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                                        new OA\Property(property: 'role_display', type: 'string', example: 'مدير مسجد'),
                                        new OA\Property(property: 'employee_code', type: 'string', example: 'MNG-2026-001'),
                                        new OA\Property(property: 'preferred_language', type: 'string', example: 'العربية (الرئيسية)'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'mosque_info',
                                    type: 'object',
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 42),
                                        new OA\Property(property: 'name', type: 'string', example: 'مسجد الرحمة الجامع'),
                                        new OA\Property(property: 'code', type: 'string', example: 'MSQ-7049'),
                                        new OA\Property(property: 'imam_name', type: 'string', example: 'الشيخ د. عبد العزيز العتيبي'),
                                        new OA\Property(property: 'khatib_name', type: 'string', example: 'الشيخ د. محمد آل الشيخ'),
                                        new OA\Property(property: 'city_district', type: 'string', example: 'الرياض - حي النزهة'),
                                        new OA\Property(property: 'status', type: 'string', example: 'نشط'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'account_security',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                        new OA\Property(property: 'status_display', type: 'string', example: 'نشط'),
                                        new OA\Property(property: 'verification_level', type: 'string', example: 'موثّق بالكامل'),
                                        new OA\Property(property: 'is_email_verified', type: 'boolean', example: true),
                                        new OA\Property(property: 'has_fcm_token', type: 'boolean', example: false),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-08-08'),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated - Missing or invalid token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                        new OA\Property(property: 'data', type: 'object', nullable: true, example: null),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
        ]
    )]
    public function getProfile() {}

    #[OA\Put(
        path: '/profile',
        operationId: 'updateProfile',
        tags: ['Profile'],
        summary: 'تحديث البيانات الشخصية للمستخدم',
        description: 'تحديث الاسم الأول، اسم العائلة، الاسم الكامل، البريد الإلكتروني، ورقم الهاتف للحساب الحالي.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'first_name', type: 'string', example: 'أحمد', minLength: 2, maxLength: 100, nullable: true),
                    new OA\Property(property: 'last_name', type: 'string', example: 'العتيبي', minLength: 2, maxLength: 100, nullable: true),
                    new OA\Property(property: 'name', type: 'string', example: 'أحمد العتيبي', minLength: 2, maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'manager@test.com'),
                    new OA\Property(property: 'phone', type: 'string', example: '0559876543', maxLength: 20, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تحديث الملف الشخصي بنجاح'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'personal_info',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                                        new OA\Property(property: 'last_name', type: 'string', example: 'العتيبي'),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'أحمد العتيبي'),
                                        new OA\Property(property: 'email', type: 'string', example: 'manager@test.com'),
                                        new OA\Property(property: 'phone', type: 'string', example: '0559876543'),
                                        new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                                        new OA\Property(property: 'role_display', type: 'string', example: 'مدير مسجد'),
                                        new OA\Property(property: 'employee_code', type: 'string', example: 'MNG-2026-001'),
                                        new OA\Property(property: 'preferred_language', type: 'string', example: 'العربية (الرئيسية)'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'mosque_info',
                                    type: 'object',
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 42),
                                        new OA\Property(property: 'name', type: 'string', example: 'مسجد الرحمة الجامع'),
                                        new OA\Property(property: 'code', type: 'string', example: 'MSQ-7049'),
                                        new OA\Property(property: 'imam_name', type: 'string', example: 'الشيخ د. عبد العزيز العتيبي'),
                                        new OA\Property(property: 'khatib_name', type: 'string', example: 'الشيخ د. محمد آل الشيخ'),
                                        new OA\Property(property: 'city_district', type: 'string', example: 'الرياض - حي النزهة'),
                                        new OA\Property(property: 'status', type: 'string', example: 'نشط'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'account_security',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                        new OA\Property(property: 'status_display', type: 'string', example: 'نشط'),
                                        new OA\Property(property: 'verification_level', type: 'string', example: 'موثّق بالكامل'),
                                        new OA\Property(property: 'is_email_verified', type: 'boolean', example: true),
                                        new OA\Property(property: 'has_fcm_token', type: 'boolean', example: false),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-08-08'),
                                    ]
                                ),
                            ]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - Request validation failed',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Validation error.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            example: ['email' => ['The email has already been taken.']]
                        ),
                        new OA\Property(property: 'pagination', type: 'object', nullable: true, example: null),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated - Missing or invalid token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
                    ]
                )
            ),
        ]
    )]
    public function updateProfile() {}

    #[OA\Post(
        path: '/profile/confirm-email',
        operationId: 'confirmEmailChange',
        tags: ['Profile'],
        summary: 'تأكيد تغيير البريد الإلكتروني بواسطة OTP',
        description: 'يتم إرسال رمز OTP المكون من 6 أرقام لتأكيد البريد الجديد المعلق (pending_email).',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['otp'],
                properties: [
                    new OA\Property(property: 'otp', type: 'string', example: '123456', maxLength: 6),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email changed and verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'تم تغيير البريد الإلكتروني وتأكيده بنجاح.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'personal_info',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 1),
                                        new OA\Property(property: 'first_name', type: 'string', example: 'أحمد'),
                                        new OA\Property(property: 'last_name', type: 'string', example: 'العتيبي'),
                                        new OA\Property(property: 'full_name', type: 'string', example: 'أحمد العتيبي'),
                                        new OA\Property(property: 'email', type: 'string', example: 'new_manager@test.com'),
                                        new OA\Property(property: 'phone', type: 'string', example: '0559876543'),
                                        new OA\Property(property: 'role', type: 'string', example: 'mosque_manager'),
                                        new OA\Property(property: 'role_display', type: 'string', example: 'مدير مسجد'),
                                        new OA\Property(property: 'employee_code', type: 'string', example: 'MNG-2026-001'),
                                        new OA\Property(property: 'preferred_language', type: 'string', example: 'العربية (الرئيسية)'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'mosque_info',
                                    type: 'object',
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: 'id', type: 'integer', example: 42),
                                        new OA\Property(property: 'name', type: 'string', example: 'مسجد الرحمة الجامع'),
                                        new OA\Property(property: 'code', type: 'string', example: 'MSQ-7049'),
                                        new OA\Property(property: 'imam_name', type: 'string', example: 'الشيخ د. عبد العزيز العتيبي'),
                                        new OA\Property(property: 'khatib_name', type: 'string', example: 'الشيخ د. محمد آل الشيخ'),
                                        new OA\Property(property: 'city_district', type: 'string', example: 'الرياض - حي النزهة'),
                                        new OA\Property(property: 'status', type: 'string', example: 'نشط'),
                                    ]
                                ),
                                new OA\Property(
                                    property: 'account_security',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                                        new OA\Property(property: 'status_display', type: 'string', example: 'نشط'),
                                        new OA\Property(property: 'verification_level', type: 'string', example: 'موثّق بالكامل'),
                                        new OA\Property(property: 'is_email_verified', type: 'boolean', example: true),
                                        new OA\Property(property: 'has_fcm_token', type: 'boolean', example: false),
                                        new OA\Property(property: 'created_at', type: 'string', example: '2026-08-08'),
                                    ]
                                ),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid OTP code',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'رمز التحقق غير صحيح أو منتهي الصلاحية.'),
                    ]
                )
            ),
        ]
    )]
    public function confirmEmailChange() {}
}
