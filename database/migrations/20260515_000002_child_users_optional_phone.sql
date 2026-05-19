-- Migration: children are stored as normal users, but can be linked to a parent
-- and do not require a phone number. Existing schemas already generated from
-- database/schema.sql have this shape; this migration is safe to run to align
-- older installs.
USE portale_familyplan;

ALTER TABLE users
  MODIFY phone VARCHAR(30) NULL,
  MODIFY parent_id INT UNSIGNED NULL;
