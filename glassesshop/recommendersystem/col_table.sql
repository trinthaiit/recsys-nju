begin;

drop table if exists oso_user_ratings;
drop table if exists Keyword;
drop table if exists Keyword_Link;

CREATE TABLE IF NOT EXISTS oso_user_ratings (
user_id int(11) NOT NULL, item_id int(11) NOT NULL, rating decimal(14,4) NOT NULL default '0.0000', KEY item_id (item_id), KEY user_id (user_id,item_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS oso_slope_one (
item_id1 int(11) NOT NULL, item_id2 int(11) NOT NULL, times int(11) NOT NULL, rating decimal(14,4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

commit;