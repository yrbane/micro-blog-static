-- Migration: Mise à jour des rôles utilisateurs vers le nouveau format
-- Les anciens rôles 'admin' deviennent 'ADMIN'

UPDATE users SET role = 'ADMIN' WHERE role = 'admin';
UPDATE users SET role = 'REDACTOR' WHERE role = 'redactor';
UPDATE users SET role = 'USER' WHERE role = 'user' OR role IS NULL OR role = '';
