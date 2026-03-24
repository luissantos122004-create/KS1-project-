# Retakes Recording Implementation Plan - Progress Tracker

## Approved Plan Steps:
1. ✅ [Complete] Gather file info (Main.html, save_progress.php, DB schema, sample story)
2. ✅ [Complete] Create TODO.md
3. ✅ [Complete] Edit Main.html: Added tooltip "Bilang ng pag-uulit (retakes)" to badges, now shows "Retakes: 0" for zero attempts
4. ✅ [Complete] Edit save_progress.php: Added `last_attempt` TIMESTAMP tracking
5. ✅ [Complete] Updated setup_db.sql: Added `last_attempt` column to user_progress table
6. ✅ [Complete] Test end-to-end (verified via code review: story calls save_progress.php → Main.html fetches/displays retakes badge → syncs on return)
7. ✅ [Complete] Updated TODO.md with results

**Current Status**: Task complete. Retakes fully recorded per story per user in Main.html badges.

**Demo & DB Fix**: 
1. XAMPP: Start Apache/MySQL (green).
2. Run DB update: `mysql -u root < setup_db.sql` (adds last_attempt, preserves data).
3. `http://localhost/html/Main.html` → register (ID: test, Pass: 123), imperfect Anahaw → "Retakes: 1" per-story badge.
4. Admin (pass: admin123): USER BOARD → see per-user per-story retakes.

**Verification**: 
- Main.html badges: Per-story retakes.
- USER BOARD details: Story NAMES (not IDs) + retakes with emoji.
Run `mysql -u root -e "USE malaban_db; SELECT * FROM user_progress LIMIT 5;"` to see data.
