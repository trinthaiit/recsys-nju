begin;

use bagsok;

drop table if exists keyword;

create table if not exists keyword(
	id bigint(20) primary key auto_increment,
	keyword varchar(255),
	unique (keyword)
);

commit;