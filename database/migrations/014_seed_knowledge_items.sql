-- Sprint 8: Seed knowledge items from course modules
-- Requires: 013_create_spaced_repetition_tables.sql
-- รันด้วย UTF-8: .\scripts\run-sql.ps1 database\migrations\014_seed_knowledge_items.sql

USE fitcoch;
INSERT INTO knowledge_items (course_id, concept_name, description)
SELECT m.course_id, m.title, CONCAT('ทบทวนแนวคิดหลักจาก ', m.title)
FROM modules m
WHERE NOT EXISTS (
    SELECT 1
    FROM knowledge_items ki
    WHERE ki.course_id = m.course_id
      AND ki.concept_name = m.title
);
