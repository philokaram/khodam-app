<?php
class AuditLog
{
    public function write(?int $userId, string $action, string $entity, ?int $entityId, $old = null, $new = null): void
    {
        Database::insert('audit_logs', [
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entity,
            'entity_id'   => $entityId,
            'old_data'    => $old !== null ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
            'new_data'    => $new !== null ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}