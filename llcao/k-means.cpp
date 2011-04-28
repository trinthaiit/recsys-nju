#include<iostream>
#include<stdlib.h>
#include<math.h>
#include<time.h>
#include<fstream>
#include<sstream>
using namespace std;

#define NA 4
#define K 3
#define PS 150
#define PC 0.5
#define ED 0.0000001
#define GeneNum 50
int xxx;
typedef struct
{
    double p[NA];
    double distance[K];
}Point;
Point instance[PS];
typedef struct
{
    Point clustercenter[K];
}Pop;

Pop pop;          //聚类中心
int cluster[K][PS];
int clusternum[K];
double clustermean[K];
double innerdistance;
double oldinnerdistance;
double btdistance;
double fitness;

void input()//读入待聚类数据
{
    fstream fin("iris.data");
    int i;
    int j;
    string line;
    double word;
    for(i=0;getline(fin,line)&&i<PS;i++)
    {
        istringstream stream(line);
        for(j=0;stream>>word&&j<NA;j++)
        {
            instance[i].p[j]=word;
        }
    }
}
int find(int a[],int n,int b)
{
    int ii;
    for(ii=0;ii<n;ii++)
    if(a[ii]==b) return 1;
    return 0;
}
void sort(int a[],int n)//冒泡排序
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
    for(;num<K;)
    {
        random=rand()%PS;
        if(!find(rt,num,random))
        rt[num++]=random;
    }
    sort(rt,K);
    for(i=0;i<K;i++)
       for(j=0;j<NA;j++)
       {
           pop.clustercenter[i].p[j]=instance[rt[i]].p[j];
       }
}
void printfpop()
{
    int i,j,k;
	for(i=0;i<k;i++)
    cout<<"个体"<<i+1<<" :";
    for(j=0;j<K;j++)
    {
        cout<<"(";
        for(k=0;k<NA;k++)
        {
            cout<<pop.clustercenter[j].p[k]<<" ";
        }
        cout<<") ";
    }
    printf(" 准则函数值为：%4.5f\n",fitness);//
}
double eucliddistance(int x,int y)
{
	int i;
	double distance=0;
	for(i=0;i<NA;i++)
	{
		distance+=pow((instance[x].p[i]-pop.clustercenter[y].p[i]),2);
	}
	distance=sqrt(distance);
	return distance;
}
void calcuatedistance()
{
	int i,j;
	for(i=0;i<PS;i++)
	   for(j=0;j<K;j++)
	   {
		  instance[i].distance[j]=eucliddistance(i,j);
	   }
}
void Cluster()
{
    int i,j,k,l;
    double min;
    double count[NA];
    int index;
    for(k=0;k<K;k++)
       clusternum[k]=0;
    for(i=0;i<PS;i++)
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
        cluster[index][clusternum[index]++]=i;
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
void updataclustercenter()
{
    int i,j,k;
    double sum;
    for(i=0;i<K;i++)
    {
        for(j=0;j<NA;j++)
        {
            sum=0;
            for(k=0;k<clusternum[i];k++)
            {
                sum+=instance[cluster[i][k]].p[j];
            }
            pop.clustercenter[i].p[j]=sum/clusternum[i];
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
    int i,j,k;
    btdistance=1;
    innerdistance=100000;
    input();
    init();
    for(i=0;(i<GeneNum)&&(btdistance>ED);i++)
    {
        evaluatepop();
        btdistance=oldinnerdistance-innerdistance;
        cout<<"第"<<i<<"代"<<endl;
        printfpop();
        cout<<"优化值："<<btdistance<<endl;
        updataclustercenter();
    }
    system("pause");
    return 0;
}
