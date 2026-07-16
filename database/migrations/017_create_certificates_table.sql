-- Sprint 9: Course mastery certificates
-- Requires: 001_create_users_table.sql, 004_create_course_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS certificates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    verification_hash VARCHAR(64) NOT NULL,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_certificates_hash (verification_hash),
    UNIQUE KEY uq_certificates_user_course (user_id, course_id),
    KEY idx_certificates_course (course_id),
    CONSTRAINT fk_certificates_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_certificates_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
