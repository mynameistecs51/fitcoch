-- Track when instructors view module discussions (unread learner messages)
-- Requires: 004_create_course_tables.sql, 015_create_module_discussions.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS discussion_reads (
    user_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    last_read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, module_id),
    KEY idx_discussion_reads_module (module_id, last_read_at),
    CONSTRAINT fk_discussion_reads_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_discussion_reads_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
