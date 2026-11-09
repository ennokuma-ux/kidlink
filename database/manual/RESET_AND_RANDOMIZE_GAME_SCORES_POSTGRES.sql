-- KidLink demo-data score reset/randomization (PostgreSQL / Render PostgreSQL)
-- BACK UP game_scores before running. This intentionally overwrites every saved game score.
-- Run manually in your database SQL editor only when you want to replace real scores.
BEGIN;

WITH randomized AS (
    SELECT id, GREATEST(COALESCE(total, 10), 1) AS safe_total,
           FLOOR((0.40 + RANDOM() * 0.60) * GREATEST(COALESCE(total, 10), 1))::integer AS new_score
    FROM game_scores
)
UPDATE game_scores AS gs
SET total = r.safe_total,
    score = r.new_score,
    mistakes = r.safe_total - r.new_score,
    percentage = ROUND(100.0 * r.new_score / r.safe_total)::integer,
    updated_at = CURRENT_TIMESTAMP
FROM randomized AS r
WHERE gs.id = r.id;

COMMIT;

-- Verify after running:
-- SELECT COUNT(*) AS score_rows, MIN(percentage) AS min_percentage, MAX(percentage) AS max_percentage FROM game_scores;
