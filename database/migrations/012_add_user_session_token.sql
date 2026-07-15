-- Single active browser session per user
-- Requires: 001_create_users_table.sql

USE fitcoch;

ALTER TABLE users
    ADD COLUMN session_token VARCHAR(128) NULL DEFAULT NULL AFTER timezone,
    ADD KEY idx_users_session_token (session_token);
