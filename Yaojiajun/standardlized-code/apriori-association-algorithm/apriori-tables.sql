begin;

drop table if exists recsys_apriori_keyword_count;

create table if not exists recsys_apriori_keyword_count(
	id bigint(20) primary key auto_increment,
	keyword varchar(255) not null,
	count int(20) default 1,
	unique(keyword)
);

commit;