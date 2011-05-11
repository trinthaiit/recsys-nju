#include<iostream>
#include<stdio.h>
using namespace std;
/*
struct user 
{
    int id;
	int age;
	char s;
	char job[15];
	int info;
}users[944];
*/

struct item 
{
	 int id;
	 char name[50];
	 char time[12];
	 int genre[19];
}item[1683];

int user_item[462][1683]; 

int main()
{
	int i = 0,j=0;
	char temp;

	FILE *fp1 = fopen("u.item","rb");
	
    FILE *fp2 = fopen("u1.test","rb");
	

//initial
	for (i=0;i<462;i++)
		for(j=0;j<1683;j++)
			user_item[i][j] = 0;

    i=0;
	



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
	
    printf("fp1\n");
	while (!feof(fp1))
	{
		fscanf(fp1,"%d",&item[i].id);
		if(item[i].id == 0)
			break;
		fscanf(fp1,"%c",&temp);
		fscanf(fp1,"%c",&temp);
		int k = 0;
		while (temp!='|')
		{
           item[i].name[k] = temp;
		   fscanf(fp1,"%c",&temp);
		   k++;
		}


		fscanf(fp1,"%c",&temp);
		k = 0;
		while (temp!='|')
		{
			item[i].time[k] = temp;
			fscanf(fp1,"%c",&temp);
			k++;
		}

		fscanf(fp1,"%c",&temp);
		fscanf(fp1,"%c",&temp);
		while (temp!='|')
		{
			fscanf(fp1,"%c",&temp);
		}
	    
		for (k=0;k<19;)
		{
			
			fscanf(fp1,"%c",&temp);
			if(temp!='|')
			{
				item[i].genre[k]=(int)(temp-'0');
				k++;
			}
		}
       /* 
		printf("%d,%s,%s",item[i].id,item[i].name,item[i].time);
		for (k=0;k<19;k++)
		{
			printf("   %d",item[i].genre[k]);
		}
		printf("\n");
		*/
		i++;
	}
    fclose(fp1);
    printf("fp2\n");
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



    //*********************´¦Àíº¯Êý**********************
    


	
	int m=0;
	
	for (i=0;i<462;i++)
	{
		int temp_genre[19];
		int user_genre[3];
		for(m=0;m<19;m++)
		{
			temp_genre[m] = 0;
		
		}
		for(j=0;j<1683;j++)
		{
			if (user_item[i+1][j+1]!=0)
			{
                for(m=0;m<19;m++)
				{
                     temp_genre[m] +=item[j].genre[m];	
					 
				}
				 
			}
		}

		for(m=0;m<19;m++)
		{
			printf("%d ",temp_genre[m] );	
			
		}
		printf("\n");

        int max=0;
		for(m=0;m<19;m++)
		{
			if (temp_genre[max]<temp_genre[m])
			{
				max = m;
			}
		}
		user_genre[0] = max;

		 max=0;
		for(m=0;m<19;m++)
		{
			if (temp_genre[max]<temp_genre[m]&&m!=user_genre[0])
			{
				max = m;
			}
		}
		user_genre[1] = max;

		 max=0;
		for(m=0;m<19;m++)
		{
			if (temp_genre[max]<temp_genre[m]&&m!=user_genre[0]&&m!=user_genre[1])
			{
				max = m;
			}
		}
		user_genre[2] = max;


	 printf("user  %d   genre  %d    %d     %d\n",i+1,user_genre[0],user_genre[1],user_genre[2]);

    } 

    
	return 0;
}