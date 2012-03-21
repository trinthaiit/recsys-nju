begin;

drop table if exists recsys_query;

create table if not exists recsys_query(
	sessionId bigint(20) primary key,
	query varchar(255) not null
);

drop table if exists recsys_session_item;

create table if not exists recsys_session_item(
	sessionId bigint(20) primary key,
	itemId bigint(20) not null,
	type integer(10) not  null -- 0 stands for visit, 1 stands for shopcart, 2 stands for order
);

drop table if exists recsys_item;

create table if not exists recsys_item(
	itemId bigint(20),
	itemName varchar(255) default '',
	itemDescription varchar(255)  default '',
	unique(itemName)
);

commit;