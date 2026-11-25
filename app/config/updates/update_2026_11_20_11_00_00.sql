START TRANSACTION;

-- Modern design: progress text (%s = step number / total steps) and booking reference label.

INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'modern_step_of', 'frontend', 'Modern design / modern_step_of', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'modern_step_of');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'Step %s of %s', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'modern_step_of' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');
INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'modern_done_ref', 'frontend', 'Modern design / modern_done_ref', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'modern_done_ref');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'Booking ID', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'modern_done_ref' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');

UPDATE `plugin_base_options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
