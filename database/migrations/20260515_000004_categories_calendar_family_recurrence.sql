-- Migration: expand family categories, support two parents for under-14 children,
-- make calendar events user-owned/shareable instead of child-related, remove admin
-- dashboard widgets, and store generated recurring family-task occurrences.
USE portale_familyplan;

ALTER TABLE users
  MODIFY category ENUM('nonno','nonna','zio','zia','papà','mamma','figlio','familiare') NOT NULL DEFAULT 'familiare',
  ADD COLUMN second_parent_id INT UNSIGNED NULL AFTER parent_id,
  ADD CONSTRAINT fk_users_second_parent FOREIGN KEY (second_parent_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE events
  ADD COLUMN shared TINYINT(1) NOT NULL DEFAULT 0 AFTER ends_at,
  DROP FOREIGN KEY fk_events_child,
  DROP COLUMN child_id,
  ADD INDEX idx_events_visibility (created_by, shared);

ALTER TABLE family_tasks
  ADD COLUMN recurrence ENUM('none','daily','weekly','monthly') NOT NULL DEFAULT 'none' AFTER notes,
  ADD COLUMN recurrence_group VARCHAR(36) NULL AFTER recurrence;

DELETE FROM dashboard_widgets WHERE widget_key IN ('settings', 'users', 'profile');
