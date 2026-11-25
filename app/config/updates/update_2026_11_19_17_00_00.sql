START TRANSACTION;

-- Modern design: options for every existing calendar. Modern is the default for all calendars (existing and new);
-- the Design setting can still be switched back to Classic per calendar.
INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`)
SELECT c.`id`, 'o_theme', 2, 'classic|modern::modern', 'Classic|Modern', 'enum', 0, 1, '' FROM `calendars` c
WHERE NOT EXISTS (SELECT 1 FROM `options` o WHERE o.`foreign_id` = c.`id` AND o.`key` = 'o_theme');

INSERT INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`)
SELECT c.`id`, 'o_modern_colors', 2, '', '', 'string', 0, 0, '' FROM `calendars` c
WHERE NOT EXISTS (SELECT 1 FROM `options` o WHERE o.`foreign_id` = c.`id` AND o.`key` = 'o_modern_colors');

-- Label of the "Design" setting (the other texts of the Customize design panel have built-in English defaults)
INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`)
SELECT NULL, 'opt_o_theme', 'backend', 'Options / Design', 'script', NOW() FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'opt_o_theme');
SET @id := (SELECT `id` FROM `plugin_base_fields` WHERE `key` = 'opt_o_theme' LIMIT 1);
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, @id, 'pjBaseField', '::LOCALE::', 'title', 'Design', 'script' FROM DUAL
WHERE @id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` WHERE `foreign_id` = @id AND `model` = 'pjBaseField' AND `locale` = '::LOCALE::' AND `field` = 'title');

-- The admin keeps the labels in the login session and reloads them when this value changes
UPDATE `plugin_base_options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
