DROP TABLE IF EXISTS phpbb_discussion_filters;
CREATE TABLE `phpbb_discussion_filters` (
  `forum_id` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `abbr` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dmmo',
  `category` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'DisneyMMO',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general.png',
  `is_default` Boolean NOT NULL DEFAULT 0,
  `posts_articles` Boolean NOT NULL DEFAULT 0
);

INSERT INTO phpbb_discussion_filters VALUES

    (26, 'cp', 'Club Penguin', 'category.png', 0, 0),
    (32, 'cp', 'Club Penguin', 'news.png', 0, 1),
    (37, 'cp', 'Club Penguin', 'general.png', 1, 0),
    (38, 'cp', 'Club Penguin', 'items.png', 0, 0),
    (40, 'cp', 'Club Penguin', 'mini-games.png', 0, 0),
    (42, 'cp', 'Club Penguin', 'igloos.png', 0, 0),
    (39, 'cp', 'Club Penguin', 'guides.png', 0, 0),
    (58, 'cp', 'Club Penguin', 'bugs.png', 0, 0),
    (59, 'cp', 'Club Penguin', 'media.png', 0, 0),
 
    (27, 'ph', 'Pixie Hollow', 'category.png', 0, 0),
    (33, 'ph', 'Pixie Hollow', 'news.png', 0, 1),
    (43, 'ph', 'Pixie Hollow', 'general.png', 1, 0),
    (44, 'ph', 'Pixie Hollow', 'items.png', 0, 0),
    (86, 'ph', 'Pixie Hollow', 'quests.png', 0, 0),
    (46, 'ph', 'Pixie Hollow', 'mini-games.png', 0, 0),
    (47, 'ph', 'Pixie Hollow', 'homes.png', 0, 0),
    (45, 'ph', 'Pixie Hollow', 'guides.png', 0, 0),
    (62, 'ph', 'Pixie Hollow', 'badges.png', 0, 0),
    (60, 'ph', 'Pixie Hollow', 'bugs.png', 0, 0),
    (61, 'ph', 'Pixie Hollow', 'media.png', 0, 0),
 
    (28, 'po', 'Pirates Online', 'category.png', 0, 0),
    (34, 'po', 'Pirates Online', 'news.png', 0, 1),
    (48, 'po', 'Pirates Online', 'general.png', 1, 0),
    (49, 'po', 'Pirates Online', 'items.png', 0, 0),
    (50, 'po', 'Pirates Online', 'quests.png', 0, 0),
    (51, 'po', 'Pirates Online', 'mini-games.png', 0, 0),
    (55, 'po', 'Pirates Online', 'weapons.png', 0, 0),
    (84, 'po', 'Pirates Online', 'guilds.png', 0, 0),
	(85, 'po', 'Pirates Online', 'test-realm.png', 0, 0),
    (53, 'po', 'Pirates Online', 'pvp.png', 0, 0),
    (54, 'po', 'Pirates Online', 'svs.png', 0, 0),
    (52, 'po', 'Pirates Online', 'guides.png', 0, 0),
    (56, 'po', 'Pirates Online', 'bugs.png', 0, 0),
    (57, 'po', 'Pirates Online', 'media.png', 0, 0),
 
    (29, 'tt', 'ToonTown', 'category.png', 0, 0),
    (35, 'tt', 'ToonTown', 'news.png', 0, 1),
    (63, 'tt', 'ToonTown', 'general.png', 1, 0),
    (64, 'tt', 'ToonTown', 'items.png', 0, 0),
    (66, 'tt', 'ToonTown', 'quests.png', 0, 0),
    (69, 'tt', 'ToonTown', 'mini-games.png', 0, 0),
    (73, 'tt', 'ToonTown', 'gags.png', 0, 0),
    (68, 'tt', 'ToonTown', 'estates.png', 0, 0),
    (82, 'tt', 'ToonTown', 'clans.png', 0, 0),
    (72, 'tt', 'ToonTown', 'cog-hq.png', 0, 0),
    (83, 'tt', 'ToonTown', 'test-realm.png', 0, 0),
    (41, 'tt', 'ToonTown', 'guides.png', 0, 0),
    (65, 'tt', 'ToonTown', 'bugs.png', 0, 0),
    (81, 'tt', 'ToonTown', 'media.png', 0, 0),
   
    (4, 'og', 'Other Games', 'category.png', 0, 0),
    (5, 'og', 'Other Games', 'general.png', 1, 0),
    (10, 'og', 'Other Games', 'online.png', 0, 0),
    (7, 'og', 'Other Games', 'console-games.png', 0, 0),
    (8, 'og', 'Other Games', 'world-of-cars.png', 0, 0),
    (9, 'og', 'Other Games', 'virtual-magic-kingdom.png', 0, 0),
   
    (18, 'dis', 'Disney General', 'category.png', 0, 0),
    (19, 'dis', 'Disney General', 'general.png', 1, 0),
    (6, 'dis', 'Disney General', 'television-and-film.png', 0, 0),
	(30, 'dis', 'Disney General', 'wdw.png', 0, 0),
    (74, 'dis', 'Disney General', 'dca.png', 0, 0),
    (75, 'dis', 'Disney General', 'foreign-parks.png', 0, 0),
   
    (31, 'crt', 'Creativity', 'category.png', 0, 0),
    (13, 'crt', 'Creativity', 'general.png', 1, 0),
    (76, 'crt', 'Creativity', 'avatars.png', 0, 0),
    (77, 'crt', 'Creativity', 'signatures.png', 0, 0),
    (78, 'crt', 'Creativity', 'writers-at-heart.png', 0, 0),
   
    (11, 'ot', 'Off-Topic', 'category.png', 0, 0),
    (12, 'ot', 'Off-Topic', 'general.png', 1, 0),
    (22, 'ot', 'Off-Topic', 'games-and-puzzles.png', 0, 0),
    (23, 'ot', 'Off-Topic', 'role-playing.png', 0, 0),
    (79, 'ot', 'Off-Topic', 'entertainment.png', 0, 0),
    (80, 'ot', 'Off-Topic', 'other-parks.png', 0, 0),
   
    (1, 'dmmo', 'DisneyMMO', 'category.png', 0, 0),
    (2, 'dmmo', 'DisneyMMO', 'news.png', 0, 1),
    (3, 'dmmo', 'DisneyMMO', 'feedback.png', 1, 0),
	(67, 'dmmo', 'DisneyMMO', 'contests.png', 0, 0),
    (36, 'dmmo', 'DisneyMMO', 'polls.png', 0, 0),
   
    (14, 'staff', 'Forbidden Magic', 'category.png', 0, 0),
    (15, 'staff', 'Forbidden Magic', 'administrators.png', 0, 0),
    (21, 'staff', 'Forbidden Magic', 'senior-moderators.png', 0, 0),
    (16, 'staff', 'Forbidden Magic', 'moderators.png', 0, 0),
    (20, 'staff', 'Forbidden Magic', 'drafting-and-editing.png', 0, 0),
    (17, 'staff', 'Forbidden Magic', 'the-fun-house.png', 1, 0),
    (24, 'staff', 'Forbidden Magic', 'deleted-topics.png', 0, 0);