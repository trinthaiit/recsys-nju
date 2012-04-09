begin;

drop table if exists rating;
drop table if exists dev;

create table if not exists rating (
    userID varchar(255) NOT NULL,
    itemID varchar(255) NOT NULL,
    ratingValue float(10) NOT NULL,
    datetimestamp TIMESTAMP NOT NULL
);

insert into rating(userID,itemID,ratingValue) 
select   keyword,item,weight from keyword_item_weight;

create table if not exists dev (
  itemID1 varchar(255) NOT NULL,
  itemID2 varchar(255) NOT NULL,
  count int(11) NOT NULL default '0',
  sum float(10) NOT NULL default '0'
);

commit;