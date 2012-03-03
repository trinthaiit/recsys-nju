create table user(id int not null primary key auto_increment,
			      userid varchar(255),
		      	  country varchar(255),
			      region varchar(255),
			      city varchar(255),
			      platform varchar(255),
		          entrypage varchar(255),
			      refer varchar(255),
			      username varchar(255),
			      time varchar(255)); 

create table visit(id int not null primary key auto_increment,
	               userid varchar(255),
			       pagetype varchar(255),
			       pageinfo varchar(255),
			       time varchar(255));

create table recclick(id int not null primary key auto_increment,
	                  userid varchar(255),
			          pagetype varchar(255),
			          pageinfo varchar(255),
	                  item varchar(255),
			          time varchar(255));
			          
create table orderrecord(id int not null primary key auto_increment,
	                     userid varchar(255),
			             username varchar(255),
	                     item varchar(255),
	                     payment varchar(255),
			             time varchar(255));
			  
create table recmethodmatcher(id int not null primary key auto_increment,
							  recid varchar(255),
			                  recmethodid int not null);