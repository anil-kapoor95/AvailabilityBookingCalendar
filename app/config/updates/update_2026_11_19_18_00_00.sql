START TRANSACTION;

-- Modern is the default design: switch calendars that still have the initial Classic value to Modern
UPDATE `options` SET `value` = 'classic|modern::modern' WHERE `key` = 'o_theme' AND `value` = 'classic|modern::classic';

COMMIT;
