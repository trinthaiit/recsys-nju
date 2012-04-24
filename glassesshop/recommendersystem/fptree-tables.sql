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

drop table if exists fptree_frequent_query_item_weight;

create table if not exists fptree_frequent_query_item_weight(
	id bigint(10) primary key auto_increment,
	frequent_query varchar(255) not null,
	item varchar(255) not null,
	weight float(10)
);

create index frequent_query_index on fptree_frequent_query_item_weight (frequent_query) using btree;

commit;