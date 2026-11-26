START TRANSACTION;

-- Descriptions of the {Voucher} and {Discount} e-mail/SMS tokens (Email Notifications > Available Tokens).
-- Editable under System Options > Multi Lang (Backend); built-in English texts are used if a label is missing.

INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'notifications_token_voucher', 'backend', 'Notifications / notifications_token_voucher', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'notifications_token_voucher');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'voucher code applied to the reservation', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'notifications_token_voucher' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');
INSERT INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) SELECT NULL, 'notifications_token_discount', 'backend', 'Notifications / notifications_token_discount', 'script', NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `plugin_base_fields` WHERE `key` = 'notifications_token_discount');
INSERT INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`) SELECT NULL, f.`id`, 'pjBaseField', '::LOCALE::', 'title', 'discount given by the voucher', 'script' FROM `plugin_base_fields` f WHERE f.`key` = 'notifications_token_discount' AND NOT EXISTS (SELECT 1 FROM `plugin_base_multi_lang` m WHERE m.`foreign_id` = f.`id` AND m.`model` = 'pjBaseField' AND m.`locale` = '::LOCALE::' AND m.`field` = 'title');
UPDATE `plugin_base_options` SET `value` = MD5(UUID()) WHERE `key` = 'o_fields_index';

COMMIT;
