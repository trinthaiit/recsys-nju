begin;

drop table if exists Query_Item;
drop table if exists Query;
drop table if exists Item;

create table if not exists Query(
	id bigint(10) primary key auto_increment,
	userId varchar(255) not null,
	query varchar(255) not null
);

create table if not exists Item(
	id bigint(10) primary key auto_increment,
	name varchar(255) not null,
	unique(name)
);

create table if not exists Query_Item(
	queryId bigint(10),
	itemId varchar(255),
	bought int(5), -- 0 stands for visit 1 stands for add to shopcart 2 stands for buy
	primary key (queryId, itemId),
	foreign key (queryId) references Query(id),
	foreign key (itemId) references Item(name)
);


create index visit_userid on visit (userid, pagetype) using btree;

commit;