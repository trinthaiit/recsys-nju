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

commit;