-- Sprint 8: Spaced Repetition (SM-2 Engine)
-- Requires: 004_create_course_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS knowledge_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id BIGINT UNSIGNED NOT NULL,
    concept_name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    PRIMARY KEY (id),
    KEY idx_knowledge_items_course (course_id),
    CONSTRAINT fk_knowledge_items_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS spaced_rep_schedules (
    user_id BIGINT UNSIGNED NOT NULL,
    knowledge_item_id BIGINT UNSIGNED NOT NULL,
    interval_days SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    easiness_factor DECIMAL(4,3) NOT NULL DEFAULT 2.500,
    repetition_number SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    next_review_date DATE NOT NULL,
    last_reviewed_at DATETIME NULL,
    PRIMARY KEY (user_id, knowledge_item_id),
    KEY idx_spaced_rep_schedules_next_date (user_id, next_review_date),
    CONSTRAINT fk_spaced_rep_schedules_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_spaced_rep_schedules_item FOREIGN KEY (knowledge_item_id) REFERENCES knowledge_items (id) ON DELETE CASCADE,
    CONSTRAINT chk_ef_min CHECK (easiness_factor >= 1.300)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
