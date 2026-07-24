-- Drops every table in the currently selected database, whatever they are —
-- not a hardcoded list, so it stays correct even as tables get added/renamed
-- over time. Foreign keys are disabled for the duration so drop order never
-- matters. Safe to run repeatedly (each DROP uses IF EXISTS).
--
-- Usage:
--   mysql -h <host> -P <port> -u <user> -p <database> < database/drop_all_tables.sql
--
-- WARNING: this deletes all data in every table of the target database. There
-- is no undo — make sure you're pointed at the right database/host first.

SET FOREIGN_KEY_CHECKS = 0;

SET @tables = NULL;
SELECT GROUP_CONCAT('`', table_name, '`') INTO @tables
FROM information_schema.tables
WHERE table_schema = DATABASE();

SET @drop_sql = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @tables), 'SELECT 1');
PREPARE stmt FROM @drop_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
