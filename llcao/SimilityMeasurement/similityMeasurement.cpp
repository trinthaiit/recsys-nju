#include <iostream>
#include <fstream>
#include <string>
#include <cmath>
#include <set>

using namespace std;

#define USER_NUM  943
#define ITEM_NUM  1683

double consine(short a[ITEM_NUM],short b[ITEM_NUM]){
	double sum=0,len_a=0,len_b=0;
	double result;
	for(int i=0;i<ITEM_NUM;i++)
    {
		sum+=a[i]*b[i];
		len_a+=pow(a[i],2.0);
		len_b+=pow(b[i],2.0);
	}
	result=sum/(sqrt(len_a)*sqrt(len_b));
	return result;
}

set<int> clothest_N(int index,short rate[USER_NUM][ITEM_NUM]){
    set<int> clothest;
	double distance[USER_NUM];
    for(int i = 0;i<USER_NUM;i++)
    {
		if(i!=index)
		    distance[i]=consine(rate[i],rate[index]);
		else
			distance[i]=1.0;
    }

	double dis[USER_NUM];
	for(int i=0;i<USER_NUM;i++)
            dis[i]=distance[i];

	 for(int n=0;n<USER_NUM;n++)
    {   
		double max = 0,temp = 0;
		int max_index=0;
        for(int m = 0;m<USER_NUM-n;m++)
        {
            if(max <= distance[m])
            {
                max_index = m;
                max = distance[m];
            }
        }
        temp = distance[max_index];
        distance[max_index] = distance[USER_NUM-n-1];
        distance[USER_NUM-n-1] = temp;
    }

	cout<<"For user"<<index<<" the clothest 20th are:"<<"\n";
    for(int j=0;j<USER_NUM;j++){
		if(dis[j]>=distance[USER_NUM-20]){
		cout<<j<<" ";
		clothest.insert(j);
		}
	}
	cout<<endl;

	return clothest;
}


int main()
{
    short rate[USER_NUM][ITEM_NUM] = {0};
    int data,num_u,num_i;
    string str;
    ifstream OpenFile("u1.txt");
	if(!OpenFile){
		cerr<<"open error!";
		exit(-1);
	}

    while(!OpenFile.eof())
    {
        OpenFile>>num_u;
        OpenFile>>num_i;
        OpenFile>>data;
        OpenFile>>str;
        rate[num_u-1][num_i-1] = data;
    }
    OpenFile.close();

    set<int> out;
    for(int i = 0;i<USER_NUM;i++)
	{
		 out=clothest_N(i,rate);
	}
        
    return 0;
}
