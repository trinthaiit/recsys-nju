#include<iostream>
#include<fstream>
#include<string>
#include<set>
#include<stdlib.h>
#include<math.h>
#include<time.h>
#include<sstream>

using namespace std;

#define K 20
#define ED 0.0000001
#define GeneNum 50     //"聚类"优化次数
#define USER_NUM  943
#define ITEM_NUM  1683
#define TRATING  3
#define TAGREEMENT 0.25

struct user_info   //用户基本信息
{
    int id;
    int age;
    string sex;
    string occupation;
    string zip;
	double rate[ITEM_NUM];
    double distance[K];
};

struct Pop //聚类中心（根据评分聚类）
{
    user_info clustercenter[K];
};

struct user_position    //用户的位置向量
{
	set<int> cluster_age;
	set<int> cluster_sex;
	set<int> cluster_occupation;
	set<int> cluster_location;
	set<int> cluster_rate;
};

typedef set<int> rateSet;

struct commu_re{
	set<int> community;
	set<int> reList;
};

user_info user[USER_NUM];      //定义943个用户
user_position user_pos[USER_NUM];  //定义用户对应的位置向量
Pop pop;               //定义聚类中心的实例
int cluster[K][USER_NUM];
int clusternum[K];
double clustermean[K];
double innerdistance;
double oldinnerdistance;
double btdistance;
double fitness;

commu_re children,teenagers,adults,seniors;  //按年龄分类
commu_re male,female;                        //按性别分类
commu_re administrator,artist,executive,engineer,entertainment,healthcare,
		homemaker,librarian,marketing,none,programmer,scientist,student,technician,writer,other;//按职业分类
commu_re loc0,loc1,loc2,loc3,loc4,loc5,loc6,loc7,loc8,loc9,loc10; //按地区分类
commu_re rateSetList[K];//定义用户评分聚类集合的数组

void input()//读入数据
{
	ifstream in_f1("user.txt");
	if(!in_f1)
		cerr<<"open error!";
     
    for(int i = 0;!in_f1.eof();i++)
    {
        in_f1>>user[i].id;
        in_f1>>user[i].age;
        in_f1>>user[i].sex;
        in_f1>>user[i].occupation;
        in_f1>>user[i].zip;
    }

    in_f1.close();

    int data,num_u,num_i;
    string str;
    ifstream in_f2("u1.base");
	if(!in_f2){
		cerr<<"open error!";
		exit(-1);
	}

    for( ;!in_f2.eof(); )
    {
        in_f2>>num_u;
        in_f2>>num_i;
        in_f2>>data;
        in_f2>>str;
		user[num_u-1].rate[num_i-1] = data;
    }
    in_f2.close();
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
void init()//初始化聚类中心
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
           pop.clustercenter[i].rate[j]=user[rt[i]].rate[j];
       }
}
void printfpop()    //打印聚类结果
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
		sum+=user[x].rate[i]*pop.clustercenter[y].rate[i];
		len_a+=pow(user[x].rate[i],2.0);
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
		  user[i].distance[j]=eucliddistance(i,j);
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
        min=user[i].distance[0];
        for(j=1;j<K;j++)
        {
            if(user[i].distance[j]<min)
            {
                min=user[i].distance[j];
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
            innerdistance+=pow(user[cluster[i][j]].distance[i],2);
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
                sum+=user[cluster[i][k]].rate[j];
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

void user_infoCluster(){
	//按年龄分类
    for(int j = 0;j<USER_NUM;j++)
	{
		if(user[j].age<12)
			children.community.insert(j);
        else if(user[j].age>= 12 && user[j].age < 18)
			teenagers.community.insert(j);
		else if(user[j].age >= 18 && user[j].age < 40)
			adults.community.insert(j);
		else 
			seniors.community.insert(j);
   }
   //按性别分类
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].sex=="M")
		   male.community.insert(j);
	   else
		   female.community.insert(j);
   }
   //按职业分类
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].occupation=="administrator")
		   administrator.community.insert(j);
	   else if(user[j].occupation=="artist")
		   artist.community.insert(j);
	    else if(user[j].occupation=="executive")
		   executive.community.insert(j);
	    else if(user[j].occupation=="engineer")
		   engineer.community.insert(j);
		else if(user[j].occupation=="entertainment")
		   entertainment.community.insert(j);
		else if(user[j].occupation=="healthcare")
		   healthcare.community.insert(j);
		else if(user[j].occupation=="homemaker")
		   homemaker.community.insert(j);
		else if(user[j].occupation=="librarian")
		   librarian.community.insert(j);
	    else if(user[j].occupation=="marketing")
		   marketing.community.insert(j);
	    else if(user[j].occupation=="none")
		   none.community.insert(j);
		else if(user[j].occupation=="programmer")
		   programmer.community.insert(j);
		else if(user[j].occupation=="scientist")
		   scientist.community.insert(j);
		else if(user[j].occupation=="student")
		   student.community.insert(j);
	    else if(user[j].occupation=="technician")
		   technician.community.insert(j);
		else if(user[j].occupation=="writer")
		   writer.community.insert(j);
		else
		   other.community.insert(j);
   }
   //按用户所在地区分类
   for(int j = 0;j<USER_NUM;j++)
   {
	    if(user[j].zip.at(0)=='0')
		   loc0.community.insert(j);
	   else if(user[j].zip.at(0)=='1')
		   loc1.community.insert(j);
		else if(user[j].zip.at(0)=='2')
		   loc2.community.insert(j);
	   else if(user[j].zip.at(0)=='3')
		   loc3.community.insert(j);
		else if(user[j].zip.at(0)=='4')
		   loc4.community.insert(j);
	   else if(user[j].zip.at(0)=='5')
		   loc5.community.insert(j);
		else if(user[j].zip.at(0)=='6')
		   loc6.community.insert(j);
	   else if(user[j].zip.at(0)=='7')
		   loc7.community.insert(j);
		else if(user[j].zip.at(0)=='8')
		   loc8.community.insert(j);
	   else if(user[j].zip.at(0)=='9')
		   loc9.community.insert(j);
        else
		   loc10.community.insert(j);
   }

    btdistance=1;
    innerdistance=100000;
    for(int i=0;(i<GeneNum)&&(btdistance>ED);i++)
    {
        evaluatepop();
        btdistance=oldinnerdistance-innerdistance;
        updateclustercenter();
    }

	for(int i=0;i<K;i++)
		for(int j=0;j<clusternum[i];j++)
			rateSetList[i].community.insert(cluster[i][j]);
}
void setUser_pos(){
 //按年龄分类,设置位置向量
    for(int j = 0;j<USER_NUM;j++)
	{
		if(user[j].age<12)
			user_pos[j].cluster_age = children.community;
        else if(user[j].age>= 12 && user[j].age < 18) 
			user_pos[j].cluster_age = teenagers.community;
		else if(user[j].age >= 18 && user[j].age < 40)
			user_pos[j].cluster_age = adults.community;
		else
			user_pos[j].cluster_age = seniors.community;
   }
   //按性别分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].sex=="M")
		   user_pos[j].cluster_sex = male.community;
	   else
		   user_pos[j].cluster_sex = female.community;
   }
   //按职业分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].occupation=="administrator")
		   user_pos[j].cluster_occupation = administrator.community;
	   else if(user[j].occupation=="artist")
		   user_pos[j].cluster_occupation = artist.community;
	    else if(user[j].occupation=="executive")
		   user_pos[j].cluster_occupation = executive.community;
	    else if(user[j].occupation=="engineer")
		   user_pos[j].cluster_occupation = engineer.community;
		else if(user[j].occupation=="entertainment")
		   user_pos[j].cluster_occupation = entertainment.community;
		else if(user[j].occupation=="healthcare")
		   user_pos[j].cluster_occupation = healthcare.community;
		else if(user[j].occupation=="homemaker")
		   user_pos[j].cluster_occupation = homemaker.community;
		else if(user[j].occupation=="librarian")
		   user_pos[j].cluster_occupation = librarian.community;
	    else if(user[j].occupation=="marketing")
		   user_pos[j].cluster_occupation = marketing.community;
	    else if(user[j].occupation=="none")
		   user_pos[j].cluster_occupation = none.community;
		else if(user[j].occupation=="programmer")
		   user_pos[j].cluster_occupation = programmer.community;
		else if(user[j].occupation=="scientist")
		   user_pos[j].cluster_occupation = scientist.community;
		else if(user[j].occupation=="student")
		   user_pos[j].cluster_occupation = student.community;
	    else if(user[j].occupation=="technician")
		   user_pos[j].cluster_occupation = technician.community;
		else if(user[j].occupation=="writer")
		   user_pos[j].cluster_occupation = writer.community;
		else
		   user_pos[j].cluster_occupation = other.community;
   }
   //按用户所在地区分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	    if(user[j].zip.at(0)=='0')
		   user_pos[j].cluster_location = loc0.community;
	   else if(user[j].zip.at(0)=='1')
		   user_pos[j].cluster_location = loc1.community;
		else if(user[j].zip.at(0)=='2')
		   user_pos[j].cluster_location = loc2.community;
	   else if(user[j].zip.at(0)=='3')
		   user_pos[j].cluster_location = loc3.community;
		else if(user[j].zip.at(0)=='4')
		   user_pos[j].cluster_location = loc4.community;
	   else if(user[j].zip.at(0)=='5')
		   user_pos[j].cluster_location = loc5.community;
		else if(user[j].zip.at(0)=='6')
		   user_pos[j].cluster_location = loc6.community;
	   else if(user[j].zip.at(0)=='7')
		   user_pos[j].cluster_location = loc7.community;
		else if(user[j].zip.at(0)=='8')
		   user_pos[j].cluster_location = loc8.community;
	   else if(user[j].zip.at(0)=='9')
		   user_pos[j].cluster_location = loc9.community;
        else
		   user_pos[j].cluster_location = loc10.community;
   }
   for(int j=0;j<USER_NUM;j++)
   {
	   for(int m=0;m<K;m++)
		   for(int n=0;n<clusternum[m];n++)
			   if(j==cluster[m][n])
				   user_pos[j].cluster_rate = rateSetList[m].community; 
   }

}
void printUser_pos(){
	 for(int j = 0;j<USER_NUM;j++)
    {
	          if(j<=8)
	          cout<<"用户"<<j+1<<"  的位置向量为：  ";
	          if(j>8&&j<=98)
	          cout<<"用户"<<j+1<<" 的位置向量为：  ";
              if(j>98)
	          cout<<"用户"<<j+1<<"的位置向量为：  ";

			  if(user_pos[j].cluster_age == children.community)
				  cout<<"["<<"children,";
			  if(user_pos[j].cluster_age == teenagers.community)
				  cout<<"["<<"teenagers,";
			  if(user_pos[j].cluster_age == adults.community)
				  cout<<"["<<"adults,";
			  if(user_pos[j].cluster_age == seniors.community)
				  cout<<"["<<"seniors,";
		   
			  if(user_pos[j].cluster_sex == male.community)
				  cout<<" M,";
			  if(user_pos[j].cluster_sex == female.community)
				  cout<<" F,";

			  if(user_pos[j].cluster_occupation == administrator.community)
				  cout<<" administrator,";
			  if(user_pos[j].cluster_occupation == artist.community)			 
				  cout<<" artist,";
			  if(user_pos[j].cluster_occupation == executive.community)
				  cout<<" executive,";
			  if(user_pos[j].cluster_occupation == engineer.community)
				  cout<<" engineer,";
			  if(user_pos[j].cluster_occupation == entertainment.community)			  
					cout<<" entertainment,";
			  if(user_pos[j].cluster_occupation == healthcare.community)
				   cout<<" healthcare,";
			  if(user_pos[j].cluster_occupation == homemaker.community)
				  cout<<" homemaker,";
			  if(user_pos[j].cluster_occupation == librarian.community)
				  cout<<" librarian,";
			  if(user_pos[j].cluster_occupation == marketing.community)
				  cout<<" marketing,";
			  if(user_pos[j].cluster_occupation == none.community)
				  cout<<" none,";
			  if(user_pos[j].cluster_occupation == programmer.community)
				  cout<<" programmer,";
			  if(user_pos[j].cluster_occupation == scientist.community)
				  cout<<" scientist,";
			  if(user_pos[j].cluster_occupation == student.community)
				  cout<<" student,";
			  if(user_pos[j].cluster_occupation == technician.community)
				  cout<<" technician,";
			  if(user_pos[j].cluster_occupation == writer.community)
				  cout<<" writer,";
			  if(user_pos[j].cluster_occupation == other.community)
				  cout<<" other,";

			  if(user_pos[j].cluster_location == loc0.community)
				  cout<<" loction 0,";
			  if(user_pos[j].cluster_location == loc1.community)
				  cout<<" loction 1,";
			  if(user_pos[j].cluster_location == loc2.community)
				  cout<<" loction 2,";
			  if(user_pos[j].cluster_location == loc3.community)
				  cout<<" loction 3,";
			  if(user_pos[j].cluster_location == loc4.community)
				  cout<<" loction 4,";
			  if(user_pos[j].cluster_location == loc5.community)
				  cout<<" loction 5,";
			  if(user_pos[j].cluster_location == loc6.community)
				  cout<<" loction 6,";
			  if(user_pos[j].cluster_location == loc7.community)
				  cout<<" loction 7,";
			  if(user_pos[j].cluster_location == loc8.community)
				  cout<<" loction 8,";
			  if(user_pos[j].cluster_location == loc9.community)
				  cout<<" loction 9,";

			  for(int i=0;i<K;i++){
                  if(user_pos[j].cluster_rate == rateSetList[i].community)
					  cout<<" RateSet"<<i+1<<"]";
			  }

			  cout<<endl;
	 }
}

void getReList(commu_re cr){
	set<int>::iterator x,xend;
	xend=cr.community.end();
	for(x=cr.community.begin();x!=xend;x++)
	{
		for(int i=0;i<ITEM_NUM;i++)
		    if(user[*x].rate[i]!=0)
			    cr.reList.insert(i);
	}

	set<int>::iterator y,yend;
	yend=cr.reList.end();
	double sum=0;
	double count=0;
	double Rcount=0;
	for(y=cr.reList.begin();y!=yend;)
	{
	    for(x=cr.community.begin();x!=xend;x++)
	   {
		   if(user[*x].rate[*y]!=0){
		       sum+=user[*x].rate[*y];
			   Rcount++;
		   }
		   count++;
		}
		if(sum/Rcount<TRATING||Rcount/count<TAGREEMENT)
			y=cr.reList.erase(y);
		else{ 
			y++;
			cout<<*y<<" ";
		}
	}	
}

int main()
{
	input();
	init();
    user_infoCluster();
	setUser_pos();
	printUser_pos();

	cout<<"For teenagers:";
	getReList(teenagers);
	cout<<endl;

	cout<<"For female:";
	getReList(female);
	cout<<endl;

	cout<<"For student:";
	getReList(student);
	cout<<endl;
	cout<<"For loction 4:";
	getReList(loc4);
	cout<<endl;

	cout<<"For setReList 6:";
	getReList(rateSetList[6]);
	cout<<endl;

}
