-- Migration: align existing installations with the requested database name.
-- Run this before the other migrations if your hosting provider does not
-- automatically create/select the database for you.
CREATE DATABASE IF NOT EXISTS portale_familyplan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portale_familyplan;
