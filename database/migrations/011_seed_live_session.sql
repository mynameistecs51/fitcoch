-- Sprint 6: Seed sample live session for Unit 1
-- Requires: 010_create_live_session_tables.sql, 005_seed_sample_course.sql

USE fitcoch;

SET @cohort_id = (
    SELECT co.id
    FROM cohorts co
    INNER JOIN courses c ON c.id = co.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
      AND co.name = 'ภาคเรียนที่ 1/2569'
    LIMIT 1
);

SET @module_id = (
    SELECT m.id
    FROM modules m
    INNER JOIN courses c ON c.id = m.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
      AND m.sequence_order = 1
    LIMIT 1
);

INSERT INTO live_sessions (cohort_id, module_id, title, start_time, end_time, status, room_id)
SELECT
    @cohort_id,
    @module_id,
    'Unit 1 Live Class: Squat Biomechanics Lab',
    DATE_ADD(NOW(), INTERVAL 1 DAY),
    DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 2 HOUR,
    'scheduled',
    CONCAT('fitcoch-u1-', @cohort_id, '-', @module_id)
WHERE @cohort_id IS NOT NULL
  AND @module_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM live_sessions WHERE room_id = CONCAT('fitcoch-u1-', @cohort_id, '-', @module_id)
  );
