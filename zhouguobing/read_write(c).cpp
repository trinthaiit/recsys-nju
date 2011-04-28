#include<iostream>
#include<stdio.h>
using namespace std;

struct user 
{
    int id;
	int age;
	char s;
	char job[15];
	int info;
}users[944];

int user_item[463][1684]; 

int main()
{
	int i = 0;

	FILE *fp1 = fopen("user.txt","rb");
    FILE *fp2 = fopen("u1.test","rb");
	if(!fp1)
	{
		printf("fp1 faile");
		exit(1);
	}
	if(!fp2)
	{
		printf("fp2 faile");
		exit(1);
	}
    printf("id  age  s   job  info\n");
	while (!feof(fp1))
	{
		/*
		fread(&users[i].id,sizeof(users[i].id),1,fp1);
		fread(&users[i].age,sizeof(users[i].age),1,fp1);
		fread(&users[i].s,sizeof(users[i].s),1,fp1);
		fread(&users[i].job[0],15,1,fp1);
		*/
		fscanf(fp1,"%d %d ",&users[i].id,&users[i].age);
        fscanf(fp1,"%c %s ",&users[i].s,&users[i].job);
		fscanf(fp1,"%d ",&users[i].info);
		printf("%d,%d,%c,%s,%d\n",users[i].id,users[i].age,users[i].s,users[i].job,users[i].info);
		i++;
	}
    fclose(fp1);

	int u_id;
	int i_id;
	int rate;
	int str;
	while (!feof(fp2))
	{
		fscanf(fp2,"%d %d %d %d",&u_id,&i_id,&rate,&str);
        user_item[u_id][i_id] = rate;
		printf("%d %d %d %d\n",u_id,i_id,rate,str);
	}
	fclose(fp2);
	return 0;
}