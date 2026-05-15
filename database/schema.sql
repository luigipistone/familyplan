CREATE DATABASE IF NOT EXISTS familyplan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE familyplan;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NULL UNIQUE,
  email VARCHAR(190) NULL UNIQUE,
  birth_date DATE NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','familiare') NOT NULL DEFAULT 'familiare',
  category ENUM('nonno','zia','papà','mamma','figlio','familiare') NOT NULL DEFAULT 'familiare',
  parent_id INT UNSIGNED NULL,
  personal_info TEXT NULL,
  theme ENUM('light','dark','system') NOT NULL DEFAULT 'system',
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_parent FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_users_role (role),
  INDEX idx_users_category (category)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_resets_token (token_hash)
) ENGINE=InnoDB;

CREATE TABLE dashboard_widgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  widget_key VARCHAR(40) NOT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_widgets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_widget (user_id, widget_key)
) ENGINE=InnoDB;

CREATE TABLE events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  description TEXT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NULL,
  child_id INT UNSIGNED NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_events_child FOREIGN KEY (child_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_events_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_events_starts (starts_at)
) ENGINE=InnoDB;

CREATE TABLE shopping_lists (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  list_date DATE NOT NULL,
  owner_id INT UNSIGNED NOT NULL,
  shared TINYINT(1) NOT NULL DEFAULT 0,
  completed_at DATETIME NULL,
  archived_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lists_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_lists_owner (owner_id, shared, archived_at)
) ENGINE=InnoDB;

CREATE TABLE shopping_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  list_id INT UNSIGNED NOT NULL,
  label VARCHAR(180) NOT NULL,
  checked TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_items_list FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE family_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  child_id INT UNSIGNED NOT NULL,
  assignee_id INT UNSIGNED NULL,
  task_date DATE NOT NULL,
  task_time TIME NULL,
  type VARCHAR(80) NOT NULL,
  notes TEXT NULL,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tasks_child FOREIGN KEY (child_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_tasks_assignee FOREIGN KEY (assignee_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_tasks_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_tasks_day (task_date, task_time)
) ENGINE=InnoDB;

CREATE TABLE reminders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  due_at DATETIME NULL,
  recurrence ENUM('none','daily','weekly','monthly') NOT NULL DEFAULT 'none',
  owner_id INT UNSIGNED NOT NULL,
  shared TINYINT(1) NOT NULL DEFAULT 0,
  completed_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reminders_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_reminders_due (due_at, shared)
) ENGINE=InnoDB;

CREATE TABLE notes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  body TEXT NULL,
  owner_id INT UNSIGNED NOT NULL,
  archived_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_notes_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  actor_id INT UNSIGNED NULL,
  type VARCHAR(40) NOT NULL,
  title VARCHAR(160) NOT NULL,
  body TEXT NULL,
  read_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_notifications_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_notifications_user (user_id, read_at, created_at)
) ENGINE=InnoDB;

CREATE TABLE app_settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (name, phone, email, birth_date, password_hash, role, category, personal_info, theme)
VALUES ('Luigi', '3497591581', NULL, NULL, '$2y$12$cS4DmgO1MqUm7hepcP9/lOzLAoZcn0Rtdqzc6h8TNvwldHDvSBTOS', 'admin', 'familiare', 'Account admin iniziale', 'system')
ON DUPLICATE KEY UPDATE name=VALUES(name), role='admin', active=1;

INSERT IGNORE INTO dashboard_widgets (user_id, widget_key, enabled, sort_order)
SELECT id, widget_key, 1, sort_order FROM users
JOIN (
  SELECT 'calendar' widget_key, 0 sort_order UNION ALL SELECT 'shopping', 1 UNION ALL SELECT 'family', 2 UNION ALL
  SELECT 'reminders', 3 UNION ALL SELECT 'notes', 4 UNION ALL SELECT 'profile', 5 UNION ALL SELECT 'settings', 6 UNION ALL SELECT 'users', 7
) defaults WHERE phone='3497591581';

INSERT INTO app_settings (setting_key, setting_value) VALUES
('family_name', 'FamilyPlan'),
('shopping_archive_days', '7'),
('quiet_hours', '22:00-07:00'),
('child_privacy_age', '14')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
