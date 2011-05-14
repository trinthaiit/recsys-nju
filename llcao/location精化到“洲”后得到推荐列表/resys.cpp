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
    int zip;
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
commu_re Alabama,Alaska,Arizona,Arkansas,California,Colorado,Connecticut,Delaware,Columbia,Florida,Georgia,
	     Hawaii,Idaho,Illinois,Indiana,Iowa,Kansas,Kentucky,Louisiana,Maine,Maryland,
		 Massachusetts,Michigan,Minnesota,Mississippi,Missouri,Montana,Nebraska,Nevada,NewHampshire,NewJersey,
		 NewMexico,NewYork,NorthCarolina,NorthDakota,Ohio,Oklahoma,Oregon,Pennsylvania,RhodeIsland,SouthCarolina,
		 SouthDakota,Tennessee,Texas,Utah,Vermont,Virginia,Washington,WestVirginia,Wisconsin,Wyoming,Unknown; //按地区(洲）分类
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
	   if(user[j].zip>=35004&&user[j].zip<=36925)
		   Alabama.community.insert(j);
	   else if(user[j].zip>=99501&&user[j].zip<=99950)
		   Alaska.community.insert(j);
		else if(user[j].zip>=85001&&user[j].zip<=86556)
		   Arizona.community.insert(j);
	   else if(user[j].zip>=71601&&user[j].zip<=72959)
		   Arkansas.community.insert(j);
	   else if(user[j].zip>=90001&&user[j].zip<=96162)
		   California.community.insert(j);
	   else if(user[j].zip>=80001&&user[j].zip<=81658)
		   Colorado.community.insert(j);
	   else if(user[j].zip>=6001&&user[j].zip<=6928)
		   Connecticut.community.insert(j);
	   else if(user[j].zip>=19701&&user[j].zip<=19980)
		   Delaware.community.insert(j);
	   else if(user[j].zip>=20001&&user[j].zip<=20599)
		   Columbia.community.insert(j);
	   else if(user[j].zip>=32004&&user[j].zip<=34997)
		   Florida.community.insert(j);
	   else if(user[j].zip>=30002&&user[j].zip<=39901)
		   Georgia.community.insert(j);
	   else if(user[j].zip>=96701&&user[j].zip<=96898)
		   Hawaii.community.insert(j);
	   else if(user[j].zip>=83201&&user[j].zip<=83888)
		   Idaho.community.insert(j);
	   else if(user[j].zip>=60001&&user[j].zip<=62999)
		   Illinois.community.insert(j);
	   else if(user[j].zip>=46001&&user[j].zip<=47997)
		   Indiana.community.insert(j);
	   else if(user[j].zip>=50001&&user[j].zip<=52809)
		   Iowa.community.insert(j);
	   else if(user[j].zip>=66002&&user[j].zip<=67954)
		   Kansas.community.insert(j);
	   else if(user[j].zip>=40003&&user[j].zip<=42788)
		   Kentucky.community.insert(j);
	   else if(user[j].zip>=70001&&user[j].zip<=71497)
		   Louisiana.community.insert(j);
	   else if(user[j].zip>=3901&&user[j].zip<=4069)
		   Maine.community.insert(j);
	   else if(user[j].zip>=20601&&user[j].zip<=21930)
		   Maryland.community.insert(j);
	   else if(user[j].zip>=01001&&user[j].zip<=05544)
		   Massachusetts.community.insert(j);
	   else if(user[j].zip>=48001&&user[j].zip<=49971)
		   Michigan.community.insert(j);
	   else if(user[j].zip>=55001&&user[j].zip<=56763)
		   Minnesota.community.insert(j);
	   else if(user[j].zip>=38601&&user[j].zip<=39776)
		   Mississippi.community.insert(j);
	   else if(user[j].zip>=63001&&user[j].zip<=65899)
		   Missouri.community.insert(j);
	   else if(user[j].zip>=59001&&user[j].zip<=59937)
		   Montana.community.insert(j);
	   else if(user[j].zip>=68001&&user[j].zip<=69367)
		   Nebraska.community.insert(j);
	   else if(user[j].zip>=88901&&user[j].zip<=89883)
		   Nevada.community.insert(j);
	   else if(user[j].zip>=210&&user[j].zip<=3897)
		   NewHampshire.community.insert(j);
	   else if(user[j].zip>=7001&&user[j].zip<=8989)
		   NewJersey.community.insert(j);
	   else if(user[j].zip>=87001&&user[j].zip<=88441)
		   NewMexico.community.insert(j);
	   else if(user[j].zip>=501&&user[j].zip<=14925)
		   NewYork.community.insert(j);
	   else if(user[j].zip>=27001&&user[j].zip<=28909)
		   NorthCarolina.community.insert(j);
	   else if(user[j].zip>=58001&&user[j].zip<=58856)
		   NorthDakota.community.insert(j);
	   else if(user[j].zip>=43001&&user[j].zip<=45999)
		   Ohio.community.insert(j);
	   else if(user[j].zip>=73001&&user[j].zip<=74966)
		   Oklahoma.community.insert(j);
	   else if(user[j].zip>=97001&&user[j].zip<=97920)
		   Oregon.community.insert(j);
	   else if(user[j].zip>=15001&&user[j].zip<=19640)
		   Pennsylvania.community.insert(j);
	   else if(user[j].zip>=2801&&user[j].zip<=2940)
		   RhodeIsland.community.insert(j);
	   else if(user[j].zip>=29001&&user[j].zip<=29945)
		   SouthCarolina.community.insert(j);
	   else if(user[j].zip>=57001&&user[j].zip<=57799)
		   SouthDakota.community.insert(j);
	   else if(user[j].zip>=37010&&user[j].zip<=38589)
		   Tennessee.community.insert(j);
	   else if(user[j].zip>=73301&&user[j].zip<=88595)
		   Texas.community.insert(j);
	   else if(user[j].zip>=84001&&user[j].zip<=84791)
		   Utah.community.insert(j);
	   else if(user[j].zip>=5001&&user[j].zip<=5907)
		   Vermont.community.insert(j);
	   else if(user[j].zip>=20101&&user[j].zip<=24658)
		   Virginia.community.insert(j);
	   else if(user[j].zip>=98001&&user[j].zip<=99403)
		   Washington.community.insert(j);
	   else if(user[j].zip>=24701&&user[j].zip<=26886)
		   WestVirginia.community.insert(j);
	   else if(user[j].zip>=53001&&user[j].zip<=54990)
		   Wisconsin.community.insert(j);
	   else if(user[j].zip>=82001&&user[j].zip<=83128)
		   Wyoming.community.insert(j);
       else
		   Unknown.community.insert(j);
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
	   if(user[j].zip>=35004&&user[j].zip<=36925)
		   user_pos[j].cluster_location = Alabama.community;
	   else if(user[j].zip>=99501&&user[j].zip<=99950)
		   user_pos[j].cluster_location = Alaska.community;
		else if(user[j].zip>=85001&&user[j].zip<=86556)
		   user_pos[j].cluster_location = Arizona.community;
	   else if(user[j].zip>=71601&&user[j].zip<=72959)
		   user_pos[j].cluster_location = Arkansas.community;
	   else if(user[j].zip>=90001&&user[j].zip<=96162)
		   user_pos[j].cluster_location = California.community;
	   else if(user[j].zip>=80001&&user[j].zip<=81658)
		   user_pos[j].cluster_location = Colorado.community;
	   else if(user[j].zip>=6001&&user[j].zip<=6928)
		   user_pos[j].cluster_location = Connecticut.community;
	   else if(user[j].zip>=19701&&user[j].zip<=19980)
		   user_pos[j].cluster_location = Delaware.community;
	   else if(user[j].zip>=20001&&user[j].zip<=20599)
		   user_pos[j].cluster_location = Columbia.community;
	   else if(user[j].zip>=32004&&user[j].zip<=34997)
		   user_pos[j].cluster_location = Florida.community;
	   else if(user[j].zip>=30002&&user[j].zip<=39901)
		   user_pos[j].cluster_location = Georgia.community;
	   else if(user[j].zip>=96701&&user[j].zip<=96898)
		   user_pos[j].cluster_location = Hawaii.community;
	   else if(user[j].zip>=83201&&user[j].zip<=83888)
		   user_pos[j].cluster_location = Idaho.community;
	   else if(user[j].zip>=60001&&user[j].zip<=62999)
		   user_pos[j].cluster_location = Illinois.community;
	   else if(user[j].zip>=46001&&user[j].zip<=47997)
		   user_pos[j].cluster_location = Indiana.community;
	   else if(user[j].zip>=50001&&user[j].zip<=52809)
		   user_pos[j].cluster_location = Iowa.community;
	   else if(user[j].zip>=66002&&user[j].zip<=67954)
		   user_pos[j].cluster_location = Kansas.community;
	   else if(user[j].zip>=40003&&user[j].zip<=42788)
		   user_pos[j].cluster_location = Kentucky.community;
	   else if(user[j].zip>=70001&&user[j].zip<=71497)
		   user_pos[j].cluster_location = Louisiana.community;
	   else if(user[j].zip>=3901&&user[j].zip<=4069)
		   user_pos[j].cluster_location = Maine.community;
	   else if(user[j].zip>=20601&&user[j].zip<=21930)
		   user_pos[j].cluster_location = Maryland.community;
	   else if(user[j].zip>=01001&&user[j].zip<=05544)
		   user_pos[j].cluster_location = Massachusetts.community;
	   else if(user[j].zip>=48001&&user[j].zip<=49971)
		   user_pos[j].cluster_location = Michigan.community;
	   else if(user[j].zip>=55001&&user[j].zip<=56763)
		   user_pos[j].cluster_location = Minnesota.community;
	   else if(user[j].zip>=38601&&user[j].zip<=39776)
		   user_pos[j].cluster_location = Mississippi.community;
	   else if(user[j].zip>=63001&&user[j].zip<=65899)
		   user_pos[j].cluster_location = Missouri.community;
	   else if(user[j].zip>=59001&&user[j].zip<=59937)
		   user_pos[j].cluster_location = Montana.community;
	   else if(user[j].zip>=68001&&user[j].zip<=69367)
		   user_pos[j].cluster_location = Nebraska.community;
	   else if(user[j].zip>=88901&&user[j].zip<=89883)
		   user_pos[j].cluster_location = Nevada.community;
	   else if(user[j].zip>=210&&user[j].zip<=3897)
		   user_pos[j].cluster_location = NewHampshire.community;
	   else if(user[j].zip>=7001&&user[j].zip<=8989)
		   user_pos[j].cluster_location = NewJersey.community;
	   else if(user[j].zip>=87001&&user[j].zip<=88441)
		   user_pos[j].cluster_location = NewMexico.community;
	   else if(user[j].zip>=501&&user[j].zip<=14925)
		   user_pos[j].cluster_location = NewYork.community;
	   else if(user[j].zip>=27001&&user[j].zip<=28909)
		   user_pos[j].cluster_location = NorthCarolina.community;
	   else if(user[j].zip>=58001&&user[j].zip<=58856)
		   user_pos[j].cluster_location = NorthDakota.community;
	   else if(user[j].zip>=43001&&user[j].zip<=45999)
		   user_pos[j].cluster_location = Ohio.community;
	   else if(user[j].zip>=73001&&user[j].zip<=74966)
		   user_pos[j].cluster_location = Oklahoma.community;
	   else if(user[j].zip>=97001&&user[j].zip<=97920)
		   user_pos[j].cluster_location =  Oregon.community;
	   else if(user[j].zip>=15001&&user[j].zip<=19640)
		   user_pos[j].cluster_location =  Pennsylvania.community;
	   else if(user[j].zip>=2801&&user[j].zip<=2940)
		   user_pos[j].cluster_location =  RhodeIsland.community;
	   else if(user[j].zip>=29001&&user[j].zip<=29945)
		   user_pos[j].cluster_location =  SouthCarolina.community;
	   else if(user[j].zip>=57001&&user[j].zip<=57799)
		   user_pos[j].cluster_location =  SouthDakota.community;
	   else if(user[j].zip>=37010&&user[j].zip<=38589)
		   user_pos[j].cluster_location =  Tennessee.community;
	   else if(user[j].zip>=73301&&user[j].zip<=88595)
		   user_pos[j].cluster_location =  Texas.community;
	   else if(user[j].zip>=84001&&user[j].zip<=84791)
		   user_pos[j].cluster_location =  Utah.community;
	   else if(user[j].zip>=5001&&user[j].zip<=5907)
		   user_pos[j].cluster_location =  Vermont.community;
	   else if(user[j].zip>=20101&&user[j].zip<=24658)
		   user_pos[j].cluster_location =  Virginia.community;
	   else if(user[j].zip>=98001&&user[j].zip<=99403)
		   user_pos[j].cluster_location =  Washington.community;
	   else if(user[j].zip>=24701&&user[j].zip<=26886)
		   user_pos[j].cluster_location =  WestVirginia.community;
	   else if(user[j].zip>=53001&&user[j].zip<=54990)
		   user_pos[j].cluster_location =  Wisconsin.community;
	   else if(user[j].zip>=82001&&user[j].zip<=83128)
		   user_pos[j].cluster_location =  Wyoming.community;
       else
		   user_pos[j].cluster_location =  Unknown.community;
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

			  if(user_pos[j].cluster_location == Alabama.community)
				  cout<<" Alabama,";
			  if(user_pos[j].cluster_location == Alaska.community)
				  cout<<" Alaska,";
			  if(user_pos[j].cluster_location == Arizona.community)
				  cout<<" Arizona,";
			  if(user_pos[j].cluster_location == Arkansas.community)
				  cout<<" Arkansas,";
			  if(user_pos[j].cluster_location == California.community)
				  cout<<" California,";
			  if(user_pos[j].cluster_location == Colorado.community)
				  cout<<" Colorado,";
			  if(user_pos[j].cluster_location == Connecticut.community)
				  cout<<" Connecticut,";
			  if(user_pos[j].cluster_location == Delaware.community)
				  cout<<" Delaware,";
			  if(user_pos[j].cluster_location == Columbia.community)
				  cout<<" District of Colnmbia,";
			  if(user_pos[j].cluster_location == Florida.community)
				  cout<<" Florida,";
			  if(user_pos[j].cluster_location == Georgia.community)
				  cout<<" Georgia,";
			  if(user_pos[j].cluster_location == Hawaii.community)
				  cout<<" Hawaii,";
			  if(user_pos[j].cluster_location == Idaho.community)
				  cout<<" Idaho,";
			  if(user_pos[j].cluster_location == Illinois.community)
				  cout<<" Illinois,";
			  if(user_pos[j].cluster_location == Indiana.community)
				  cout<<" Indiana,";
			  if(user_pos[j].cluster_location == Iowa.community)
				  cout<<" Iowa,";
			  if(user_pos[j].cluster_location == Kansas.community)
				  cout<<" Kansas,";
			  if(user_pos[j].cluster_location == Kentucky.community)
				  cout<<" Kentucky,";
			  if(user_pos[j].cluster_location == Louisiana.community)
				  cout<<" Louisiana,";
			  if(user_pos[j].cluster_location == Maine.community)
				  cout<<" Maine,";
			  if(user_pos[j].cluster_location == Maryland.community)
				  cout<<" Maryland,";
			  if(user_pos[j].cluster_location == NewMexico.community)
				  cout<<" New Mexico,";
			  if(user_pos[j].cluster_location == NewYork.community)
				  cout<<" New York,";
			  if(user_pos[j].cluster_location == NorthCarolina.community)
				  cout<<" North Carolina,";
			  if(user_pos[j].cluster_location == NorthDakota.community)
				  cout<<" North Dakota,";
			  if(user_pos[j].cluster_location == Ohio.community)
				  cout<<" Ohio,";
			  if(user_pos[j].cluster_location == Oklahoma.community)
				  cout<<" Oklahoma,";
			  if(user_pos[j].cluster_location == Oregon.community)
				  cout<<" Oregon,";
			  if(user_pos[j].cluster_location == Pennsylvania.community)
				  cout<<" Pennsylvania,";
			  if(user_pos[j].cluster_location == RhodeIsland.community)
				  cout<<" Rhode Island,";
			  if(user_pos[j].cluster_location == SouthCarolina.community)
				  cout<<" South Carolina,";
			  if(user_pos[j].cluster_location == SouthDakota.community)
				  cout<<" South Dakota,";
			  if(user_pos[j].cluster_location == Tennessee.community)
				  cout<<" Tennessee,";
			  if(user_pos[j].cluster_location == Texas.community)
				  cout<<" Texas,";
			  if(user_pos[j].cluster_location == Utah.community)
				  cout<<" Utah,";
			  if(user_pos[j].cluster_location == Vermont.community)
				  cout<<" Vermont,";
			  if(user_pos[j].cluster_location == Virginia.community)
				  cout<<" Virginia,";
			  if(user_pos[j].cluster_location == Washington.community)
				  cout<<" Washington,";
			  if(user_pos[j].cluster_location == WestVirginia.community)
				  cout<<" West Virginia.,";
			  if(user_pos[j].cluster_location == Wisconsin.community)
				  cout<<" Wisconsin,";
			  if(user_pos[j].cluster_location == Wyoming.community)
				  cout<<" Wyoming,";
			  if(user_pos[j].cluster_location == Unknown.community)
				  cout<<" Unknown,";

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
	cout<<"For California:";
	getReList(California);
	cout<<endl;

	cout<<"For setReList 6:";
	getReList(rateSetList[6]);
	cout<<endl;

}
