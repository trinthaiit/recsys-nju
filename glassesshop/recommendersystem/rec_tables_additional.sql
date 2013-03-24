begin;

drop table if exists weight_matrix; 
drop table if exists rating_matrix; 

create table if not exists weight_matrix(
	id bigint(10) primary key auto_increment,
	keyword varchar(255) not null,
	item varchar(255) not null,
	weight bigint(10)
);

create table if not exists rating_matrix(
	id bigint(10) primary key auto_increment,
	keyword varchar(255) not null,
	item varchar(255) not null,
    kf float,
    iif float,
	rating float
);

create table if not exists keyword_query(
	id bigint(10) primary key auto_increment,
	keyword varchar(255) not null,
	queryId bigint(10) not null
);

commit;
