START TRANSACTION;

-- Modern design, calendar step: bottom bar labels (stay summary, empty state, clear). Russian/German added where the label is still the English text.

INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'modern_cal_stay', 'frontend', 'Modern design / modern_cal_stay', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'modern_cal_stay');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'Your stay', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'modern_cal_stay' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');
INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'modern_cal_none', 'frontend', 'Modern design / modern_cal_none', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'modern_cal_none');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'No dates selected', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'modern_cal_none' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');
INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'modern_cal_clear', 'frontend', 'Modern design / modern_cal_clear', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'modern_cal_clear');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'Clear', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'modern_cal_clear' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');


UPDATE `plugin_base_options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
