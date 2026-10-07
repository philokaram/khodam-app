<?php
class ProfileApiController
{
    public function changePassword(): void
    {
        apiRequirePost();
        apiRequireLogin();
        apiVerifyCsrf();

        $in = apiInput();
        $currentPassword = (string)($in['current_password'] ?? '');
        $newPassword = (string)($in['new_password'] ?? '');

        $me = currentUser();

        $user = Database::one("SELECT password_hash FROM users WHERE id = ?", [(int)$me['id']]);
        if (!$user) {
            apiError('المستخدم غير موجود', [], 404);
        }

        if (!password_verify($currentPassword, $user['password_hash'])) {
            apiError('كلمة المرور الحالية غير صحيحة', ['current_password' => 'خطأ'], 422);
        }

        if (strlen($newPassword) < 6) {
            apiError('كلمة المرور الجديدة يجب أن تكون 6 أحرف على الأقل', [], 422);
        }

        if ($currentPassword === $newPassword) {
            apiError('كلمة المرور الجديدة يجب أن تكون مختلفة', [], 422);
        }

        Database::update('users', ['password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)], 'id = ?', [(int)$me['id']]);

        (new AuditLog())->write(
            (int)$me['id'],
            'CHANGE_OWN_PASSWORD',
            'user',
            (int)$me['id']
        );

        apiSuccess(null, 'تم تغيير كلمة المرور بنجاح');
    }
}