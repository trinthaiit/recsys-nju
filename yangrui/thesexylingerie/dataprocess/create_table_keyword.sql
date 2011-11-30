begin;

use thesexylingerie;

drop table if exists preprocessed_user;

create table if not exists preprocessed_user(
	id bigint(20) primary key auto_increment,
	userid varchar(255),
	date varchar(255),
	keywords varchar(255)
);

drop table if exists keyword;

create table if not exists keyword(
	id bigint(20) primary key auto_increment,
	keyword varchar(255),
	unique (keyword)
);

drop table if exists keyword_product_weight;

create table if not exists keyword_product_weight(
	id bigint(20) primary key auto_increment,
	keyword varchar(255),
	product varchar(255),
	weight float(20)
);

drop table if exists keyword_link;

create table if not exists keyword_product_weight(
	id bigint(28) primary key auto_increment,
	keyword varchar(255),
	count bigint(20),
	keyword_expand varchar(255),
	ex_count bigint(20)
);

commit;
