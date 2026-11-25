START TRANSACTION;

INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'front_no_rate', 'frontend', 'Booking / front_no_rate', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'front_no_rate');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'Rates are not available for the selected dates.', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'front_no_rate' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');

UPDATE `plugin_base_options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
