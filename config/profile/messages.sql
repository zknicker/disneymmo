# phpbb_messages table
DROP TABLE IF EXISTS phpbb_wall_messages;
CREATE TABLE phpbb_wall_messages (
	message_id mediumint(8) UNSIGNED NOT NULL auto_increment,
	message_text mediumtext NOT NULL,
	message_time int(11) UNSIGNED DEFAULT '0' NOT NULL,
    PRIMARY KEY (message_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;

# phpbb_wall_messages_track table
DROP TABLE IF EXISTS phpbb_wall_messages_track;
CREATE TABLE phpbb_wall_messages_track (
	sender_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	recipient_id mediumint(8) UNSIGNED DEFAULT '0' NOT NULL,
	message_id mediumint(8) UNSIGNED NOT NULL,
    PRIMARY KEY (sender_id, recipient_id, message_id),
    KEY (sender_id),
    KEY (recipient_id),
    KEY (message_id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;