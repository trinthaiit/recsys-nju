begin;

use thesexylingerie_test;

drop table if exists preprocessed_user;

create table if not exists preprocessed_user(
	id bigint(20) primary key auto_increment,
	userid varchar(255),
	date varchar(255),
	keywords varchar(255)
);

drop table if exists preprocessed_user_train;

create table if not exists preprocessed_user_train(
	id bigint(20) primary key auto_increment,
	userid varchar(255),
	date varchar(255),
	keywords varchar(255)
);

drop table if exists preprocessed_user_test;

create table if not exists preprocessed_user_test(
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

drop table if exists keyword_train;

create table if not exists keyword_train(
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

drop table if exists keyword_product_weight_train;

create table if not exists keyword_product_weight_train(
	id bigint(20) primary key auto_increment,
	keyword varchar(255),
	product varchar(255),
	weight float(20)
);

drop table if exists keyword_link;

CREATE  TABLE keyword_link(

  `keyword` VARCHAR(255) NULL ,

  `count` INT NULL ,

  `keyword_expand` VARCHAR(255) NULL ,

  `ex_count` INT NULL ,

  `jaccard_0.2` FLOAT NULL );


commit;