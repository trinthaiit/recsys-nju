#include<iostream>
#include<stdlib.h>
#include<math.h>
#include<time.h>
#include<fstream>
#include<sstream>
using namespace std;

#define K 20
#define ED 0.0000001
#define GeneNum 50     //优化次数
#define USER_NUM  943
#define ITEM_NUM  1683

typedef struct
{
    double rate[ITEM_NUM];
    double distance[K];
}User;
User instance[USER_NUM];

typedef struct
{
    User clustercenter[K];
}Pop;

Pop pop;                //聚类中心
int cluster[K][USER_NUM];
int clusternum[K];
double clustermean[K];
double innerdistance;
double oldinnerdistance;
double btdistance;
double fitness;

void input()//读入待聚类数据
{
    int data,num_u,num_i;
    string str;
    ifstream OpenFile("u1.base");
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
		instance[num_u-1].rate[num_i-1] = data;
    }
    OpenFile.close();
}

int find(int a[],int n,int b)
{
    int ii;
    for(ii=0;ii<n;ii++)
    if(a[ii]==b) return 1;
    return 0;
}
void sort(int a[],int n)  //冒泡排序
{
    int temp;
    int i,j;
    for(i=0;i<n;i++)
        for(j=0;j<n-i-1;j++)
        {
            if(a[j]>a[j+1])
            {
                temp=a[j];
                a[j]=a[j+1];
                a[j+1]=temp;
            }
        }
}
void init()
{
    int i,j;
    int num=0;
    int random;
    int rt[K];
    srand(time(NULL));
    for( ;num<K; )
    {
        random=rand()%USER_NUM;
        if(!find(rt,num,random))
           rt[num++]=random;
    }
    sort(rt,K);
    for(i=0;i<K;i++)
       for(j=0;j<ITEM_NUM;j++)
       {
           pop.clustercenter[i].rate[j]=instance[rt[i]].rate[j];
       }
}
void printfpop()
{
    int i,j;
    for(i=0;i<K;i++)
    {
        cout<<"Cluster "<<i+1<<" includes: "<<"(";
		for(j=0;j<clusternum[i];j++)
		{
			cout<<cluster[i][j]<<" ";
		}
		cout<<")"<<endl;
    }
	//printf("准则函数值为：%4.5f\n",fitness);
}
double eucliddistance(int x,int y)
{
	double sum=0,len_a=0,len_b=0;
	double result;
	for(int i=0;i<ITEM_NUM;i++)
    {
		sum+=instance[x].rate[i]*pop.clustercenter[y].rate[i];
		len_a+=pow(instance[x].rate[i],2.0);
		len_b+=pow(pop.clustercenter[y].rate[i],2.0);
	}
	result=sum/(sqrt(len_a)*sqrt(len_b));
	return result;
}

void calcuatedistance()
{
	int i,j;
	for(i=0;i<USER_NUM;i++)
	   for(j=0;j<K;j++)
	   {
		  instance[i].distance[j]=eucliddistance(i,j);
	   }
}
void Cluster()
{
    int i,j,k;
    double min;
    int index;
    for(k=0;k<K;k++)
       clusternum[k]=0;
    for(i=0;i<USER_NUM;i++)
    {
        index=0;
        min=instance[i].distance[0];
        for(j=1;j<K;j++)
        {
            if(instance[i].distance[j]<min)
            {
                min=instance[i].distance[j];
                index=j;
            }
        }
        cluster[index][clusternum[index]++]=i;//放到群中，该群的数量+1
    }
	
	oldinnerdistance=innerdistance;  
    innerdistance=0.0;               
    for(i=0;i<K;i++)
    {
        for(j=0;j<clusternum[i];j++)
            innerdistance+=pow(instance[cluster[i][j]].distance[i],2);
    }
    fitness=1/(1+innerdistance);
}
void updateclustercenter()
{
    int i,j,k;
    double sum;
    for(i=0;i<K;i++)
    {
        for(j=0;j<ITEM_NUM;j++)
        {
            sum=0;
            for(k=0;k<clusternum[i];k++)
            {
                sum+=instance[cluster[i][k]].rate[j];
            }
            pop.clustercenter[i].rate[j]=sum/clusternum[i];
        }
    }
}
void evaluatepop()
{
    calcuatedistance();
    Cluster();
}
int main()
{
    int i;
    btdistance=1;
    innerdistance=100000;
    input();
    init();
    for(i=0;(i<GeneNum)&&(btdistance>ED);i++)
    {
        evaluatepop();
        btdistance=oldinnerdistance-innerdistance;
        cout<<"第"<<i+1<<"代"<<endl;
        printfpop();
        cout<<"优化值："<<btdistance<<endl;
        updateclustercenter();
    }
    system("pause");
    return 0;
}
