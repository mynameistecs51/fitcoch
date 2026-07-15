-- Sprint 6: Virtual Classroom & WebRTC Workspace
-- Requires: 004_create_course_tables.sql, 007_create_quiz_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS live_sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cohort_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('scheduled', 'active', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    room_id VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_live_sessions_room (room_id),
    KEY idx_live_sessions_cohort_module (cohort_id, module_id),
    CONSTRAINT fk_live_sessions_cohort FOREIGN KEY (cohort_id) REFERENCES cohorts (id) ON DELETE CASCADE,
    CONSTRAINT fk_live_sessions_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE RESTRICT,
    CONSTRAINT chk_live_session_dates CHECK (end_time > start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS live_attendance (
    live_session_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME NULL,
    total_seconds INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (live_session_id, user_id),
    KEY idx_live_attendance_user (user_id),
    CONSTRAINT fk_live_attendance_session FOREIGN KEY (live_session_id) REFERENCES live_sessions (id) ON DELETE CASCADE,
    CONSTRAINT fk_live_attendance_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
