-- Sprint 5: Quiz Engine & Readiness Gate
-- Requires: 004_create_course_tables.sql, 006_create_nuggets_tables.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS quizzes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    module_id BIGINT UNSIGNED NOT NULL,
    quiz_type ENUM('readiness', 'post_class') NOT NULL DEFAULT 'readiness',
    title VARCHAR(255) NOT NULL,
    passing_score_pct TINYINT UNSIGNED NOT NULL DEFAULT 80,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_quizzes_module (module_id),
    CONSTRAINT fk_quizzes_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE,
    CONSTRAINT chk_passing_limit CHECK (passing_score_pct <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    quiz_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('single_choice', 'multiple_choice', 'open') NOT NULL DEFAULT 'single_choice',
    points TINYINT UNSIGNED NOT NULL DEFAULT 10,
    PRIMARY KEY (id),
    KEY idx_questions_quiz (quiz_id),
    CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS options (
    question_id BIGINT UNSIGNED NOT NULL,
    option_number TINYINT UNSIGNED NOT NULL,
    option_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (question_id, option_number),
    CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    quiz_id BIGINT UNSIGNED NOT NULL,
    score_pct TINYINT UNSIGNED NOT NULL,
    completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_quiz_attempts_user_quiz (user_id, quiz_id),
    CONSTRAINT fk_quiz_attempts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_attempts_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_responses (
    quiz_attempt_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    selected_option_number TINYINT UNSIGNED NULL,
    PRIMARY KEY (quiz_attempt_id, question_id),
    KEY fk_quiz_responses_options (question_id, selected_option_number),
    CONSTRAINT fk_quiz_responses_attempt FOREIGN KEY (quiz_attempt_id) REFERENCES quiz_attempts (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_responses_question FOREIGN KEY (question_id) REFERENCES questions (id) ON DELETE CASCADE,
    CONSTRAINT fk_quiz_responses_options FOREIGN KEY (question_id, selected_option_number) REFERENCES options (question_id, option_number) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS readiness_tickets (
    user_id BIGINT UNSIGNED NOT NULL,
    cohort_id BIGINT UNSIGNED NOT NULL,
    module_id BIGINT UNSIGNED NOT NULL,
    status ENUM('locked', 'unlocked', 'overridden') NOT NULL DEFAULT 'locked',
    overridden_by BIGINT UNSIGNED NULL,
    overridden_at DATETIME NULL,
    unlocked_at DATETIME NULL,
    PRIMARY KEY (user_id, cohort_id, module_id),
    KEY idx_readiness_tickets_cohort_module (cohort_id, module_id),
    CONSTRAINT fk_readiness_tickets_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_readiness_tickets_cohort FOREIGN KEY (cohort_id) REFERENCES cohorts (id) ON DELETE RESTRICT,
    CONSTRAINT fk_readiness_tickets_module FOREIGN KEY (module_id) REFERENCES modules (id) ON DELETE CASCADE,
    CONSTRAINT fk_readiness_tickets_overridden_by FOREIGN KEY (overridden_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
