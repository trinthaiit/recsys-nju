begin;

drop table if exists fptree_keyword_count;

create table if not exists fptree_keyword_count(
	id bigint(20) primary key auto_increment,
	keyword varchar(255) not null,
	count int(20) default 1,
	unique(keyword)
);

drop table if exists fptree_frequent_query;

create table if not exists fptree_frequent_query(
	id bigint(10) primary key auto_increment,
	query varchar(255) not null
);

commit;