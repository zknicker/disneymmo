# phpbb_customizations table
DROP TABLE IF EXISTS phpbb_profile_customizations;
CREATE TABLE phpbb_profile_customizations (
	user_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
    customization_type smallint(8),
    customization_image varchar(12),
    PRIMARY KEY (user_id, customization_type)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;