-- แก้คำอธิบายแนวคิดทบทวนที่ถูกบันทึกเป็น ? จาก charset ผิดตอน seed
-- รัน: .\scripts\run-sql.ps1 database\fixes\002_fix_knowledge_item_descriptions.sql

USE fitcoch;

UPDATE knowledge_items
SET description = 'ทบทวนแนวคิดหลักจาก Unit 1: Biomechanics & Squat Analysis'
WHERE concept_name = 'Unit 1: Biomechanics & Squat Analysis';

UPDATE knowledge_items
SET description = 'ทบทวนแนวคิดหลักจาก Unit 2: Health Screening (PAR-Q+)'
WHERE concept_name = 'Unit 2: Health Screening (PAR-Q+)';

SELECT id, concept_name, description FROM knowledge_items;
