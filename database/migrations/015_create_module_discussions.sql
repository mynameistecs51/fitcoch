-- Sprint 6 replacement: Module Discussion Board (per lesson unit)
-- Requires: 004_create_course_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS module_discussions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_module_discussions_module (module_id, created_at),
    CONSTRAINT fk_module_discussions_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE,
    CONSTRAINT fk_module_discussions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
