-- Sprint 9: Gamification (streaks, XP, badges)
-- Requires: 001_create_users_table.sql

USE fitcoch;

CREATE TABLE IF NOT EXISTS user_streaks (
    user_id BIGINT UNSIGNED NOT NULL,
    current_streak SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    longest_streak SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    last_activity_date DATE NULL,
    shields_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_user_streaks_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS xp_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    xp_amount SMALLINT SIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_xp_transactions_user (user_id, earned_at),
    CONSTRAINT fk_xp_transactions_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS badges (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL,
    icon_url VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_badges_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_badges (
    user_id BIGINT UNSIGNED NOT NULL,
    badge_id SMALLINT UNSIGNED NOT NULL,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    KEY idx_user_badges_badge (badge_id),
    CONSTRAINT fk_user_badges_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_user_badges_badge FOREIGN KEY (badge_id) REFERENCES badges (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO badges (name, description, icon_url)
SELECT 'first_nugget', 'Complete your first microlearning lesson', 'fa-circle-play'
WHERE NOT EXISTS (SELECT 1 FROM badges WHERE name = 'first_nugget');

INSERT INTO badges (name, description, icon_url)
SELECT 'quiz_passed', 'Pass your first readiness quiz', 'fa-clipboard-check'
WHERE NOT EXISTS (SELECT 1 FROM badges WHERE name = 'quiz_passed');

INSERT INTO badges (name, description, icon_url)
SELECT 'streak_7', 'Maintain a 7-day learning streak', 'fa-fire'
WHERE NOT EXISTS (SELECT 1 FROM badges WHERE name = 'streak_7');

INSERT INTO badges (name, description, icon_url)
SELECT 'review_10', 'Complete 10 spaced repetition reviews', 'fa-brain'
WHERE NOT EXISTS (SELECT 1 FROM badges WHERE name = 'review_10');
