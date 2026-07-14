-- Sprint 5: Seed readiness quiz for Unit 1 + initialize locked tickets
-- Requires: 005_seed_sample_course.sql, 007_create_quiz_tables.sql

USE fitcoch;

SET @module_id = (
    SELECT m.id
    FROM modules m
    INNER JOIN courses c ON c.id = m.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
      AND m.sequence_order = 1
    LIMIT 1
);

INSERT INTO quizzes (module_id, quiz_type, title, passing_score_pct)
SELECT @module_id, 'readiness', 'Readiness Quiz — Unit 1', 80
WHERE @module_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM quizzes WHERE module_id = @module_id AND quiz_type = 'readiness');

SET @quiz_id = (
    SELECT id FROM quizzes WHERE module_id = @module_id AND quiz_type = 'readiness' LIMIT 1
);

INSERT INTO questions (quiz_id, question_text, question_type, points)
SELECT @quiz_id, 'What is the primary purpose of a readiness quiz in flipped learning?', 'single_choice', 10
WHERE @quiz_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'What is the primary purpose%');

INSERT INTO questions (quiz_id, question_text, question_type, points)
SELECT @quiz_id, 'Which joint action occurs during the lowering phase of a squat?', 'single_choice', 10
WHERE @quiz_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Which joint action%');

INSERT INTO questions (quiz_id, question_text, question_type, points)
SELECT @quiz_id, 'Biomechanics in exercise science primarily studies:', 'single_choice', 10
WHERE @quiz_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Biomechanics in exercise%');

INSERT INTO questions (quiz_id, question_text, question_type, points)
SELECT @quiz_id, 'Pre-class microlearning helps students:', 'single_choice', 10
WHERE @quiz_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Pre-class microlearning%');

INSERT INTO questions (quiz_id, question_text, question_type, points)
SELECT @quiz_id, 'A passing readiness score of 80% means:', 'single_choice', 10
WHERE @quiz_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'A passing readiness%');

SET @q1 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'What is the primary purpose%' LIMIT 1);
SET @q2 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Which joint action%' LIMIT 1);
SET @q3 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Biomechanics in exercise%' LIMIT 1);
SET @q4 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'Pre-class microlearning%' LIMIT 1);
SET @q5 = (SELECT id FROM questions WHERE quiz_id = @quiz_id AND question_text LIKE 'A passing readiness%' LIMIT 1);

INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q1, 1, 'Verify pre-class preparation before live sessions', 1 WHERE @q1 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q1, 2, 'Replace all live classroom teaching', 0 WHERE @q1 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q1, 3, 'Assign final course grades only', 0 WHERE @q1 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q1, 4, 'Track gym attendance automatically', 0 WHERE @q1 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);

INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q2, 1, 'Knee extension only', 0 WHERE @q2 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q2, 2, 'Hip and knee flexion (eccentric control)', 1 WHERE @q2 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q2, 3, 'Ankle plantarflexion only', 0 WHERE @q2 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q2, 4, 'Spinal rotation', 0 WHERE @q2 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);

INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q3, 1, 'Marketing strategies for gyms', 0 WHERE @q3 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q3, 2, 'Forces and movement in the human body', 1 WHERE @q3 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q3, 3, 'Nutrition supplement dosing only', 0 WHERE @q3 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q3, 4, 'Social media content planning', 0 WHERE @q3 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);

INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q4, 1, 'Arrive unprepared to live class', 0 WHERE @q4 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q4, 2, 'Build foundational knowledge before class', 1 WHERE @q4 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q4, 3, 'Skip all assessments', 0 WHERE @q4 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q4, 4, 'Avoid practical labs entirely', 0 WHERE @q4 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);

INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q5, 1, 'You may enter the live session gate', 1 WHERE @q5 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q5, 2, 'You are automatically certified', 0 WHERE @q5 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q5, 3, 'All modules are permanently locked', 0 WHERE @q5 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);
INSERT INTO options (question_id, option_number, option_text, is_correct) SELECT @q5, 4, 'No further study is required', 0 WHERE @q5 IS NOT NULL ON DUPLICATE KEY UPDATE option_text = VALUES(option_text);

SET @cohort_id = (
    SELECT co.id
    FROM cohorts co
    INNER JOIN courses c ON c.id = co.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
    LIMIT 1
);

INSERT IGNORE INTO readiness_tickets (user_id, cohort_id, module_id, status)
SELECT ce.user_id, ce.cohort_id, @module_id, 'locked'
FROM cohort_enrollments ce
WHERE @cohort_id IS NOT NULL
  AND @module_id IS NOT NULL
  AND ce.cohort_id = @cohort_id
  AND ce.status = 'active';
