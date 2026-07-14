-- Sprint 4 (partial): Nuggets & progress tracking
-- Requires: 004_create_course_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS nuggets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    nugget_type ENUM('video', 'reading', 'quiz') NOT NULL DEFAULT 'video',
    content_url VARCHAR(512) NULL,
    content_body MEDIUMTEXT NULL,
    duration_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    sequence_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_nuggets_module_sequence (module_id, sequence_order),
    CONSTRAINT fk_nuggets_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nugget_progress (
    user_id BIGINT UNSIGNED NOT NULL,
    nugget_id BIGINT UNSIGNED NOT NULL,
    progress_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('in_progress', 'completed') NOT NULL DEFAULT 'in_progress',
    completed_at DATETIME NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, nugget_id),
    KEY idx_nugget_progress_nugget (nugget_id),
    CONSTRAINT fk_nugget_progress_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_nugget_progress_nugget FOREIGN KEY (nugget_id) REFERENCES nuggets (id) ON DELETE CASCADE,
    CONSTRAINT chk_progress_limit CHECK (progress_percentage <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
