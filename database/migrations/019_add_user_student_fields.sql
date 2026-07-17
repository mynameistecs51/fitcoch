-- Add student ID and title prefix for registration / import

USE fitcoch;

ALTER TABLE users
    ADD COLUMN student_id VARCHAR(20) NULL AFTER id,
    ADD COLUMN title_prefix VARCHAR(20) NOT NULL DEFAULT '' AFTER student_id,
    ADD UNIQUE KEY uq_users_student_id (student_id);
