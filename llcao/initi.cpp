#include <iostream>
#include <fstream>
#include <string>
#include<set>

using namespace std;

#define USER_NUM  100
#define ITEM_NUM  1683

struct user_info
{
    int id;
    int age;
    string sex;
    string occupation;
    int zip;

};

struct user_position
{
	set<int> cluster_age;
	set<int> cluster_sex;
	set<int> cluster_occupation;
	set<int> cluster_location;
};

int main()
{
    user_info user[USER_NUM];
	user_position user_pos[USER_NUM];
	
	set<int> children,teenagers,adults,seniors;  //按年龄分类
	set<int> male,female;                        //按性别分类
	set<int> administrator,artist,executive,engineer,entertainment,healthcare,
		homemaker,librarian,marketing,none,programmer,scientist,student,technician,writer,other;//按职业分类
	set<int> loc0,loc1,loc2,loc3,loc4,loc5,loc6,loc7,loc8,loc9; //按地区分类
	
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
	//按年龄分类
    for(int j = 0;j<USER_NUM;j++)
	{
		if(user[j].age<12)
			children.insert(j);
        else if(user[j].age>= 12 && user[j].age < 18)
			teenagers.insert(j);
		else if(user[j].age >= 18 && user[j].age < 40)
			adults.insert(j);
		else 
			seniors.insert(j);
   }
   //按性别分类
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].sex=="M")
		   male.insert(j);
	   else
		   female.insert(j);
   }
   //按职业分类
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].occupation=="administrator")
		   administrator.insert(j);
	   else if(user[j].occupation=="artist")
		   artist.insert(j);
	    else if(user[j].occupation=="executive")
		   executive.insert(j);
	    else if(user[j].occupation=="engineer")
		   engineer.insert(j);
		else if(user[j].occupation=="entertainment")
		   entertainment.insert(j);
		else if(user[j].occupation=="healthcare")
		   healthcare.insert(j);
		else if(user[j].occupation=="homemaker")
		   homemaker.insert(j);
		else if(user[j].occupation=="librarian")
		   librarian.insert(j);
	    else if(user[j].occupation=="marketing")
		   marketing.insert(j);
	    else if(user[j].occupation=="none")
		   none.insert(j);
		else if(user[j].occupation=="programmer")
		   programmer.insert(j);
		else if(user[j].occupation=="scientist")
		   scientist.insert(j);
		else if(user[j].occupation=="student")
		   student.insert(j);
	    else if(user[j].occupation=="technician")
		   technician.insert(j);
		else if(user[j].occupation=="writer")
		   writer.insert(j);
		else
		   other.insert(j);
   }
   //按用户所在地区分类
   for(int j = 0;j<USER_NUM;j++)
   {
	    if(user[j].zip/10000==0)
		   loc0.insert(j);
	   else if(user[j].zip/10000==1)
		   loc1.insert(j);
		else if(user[j].zip/10000==2)
		   loc2.insert(j);
	   else if(user[j].zip/10000==3)
		   loc3.insert(j);
		else if(user[j].zip/10000==4)
		   loc4.insert(j);
	   else if(user[j].zip/10000==4)
		   loc4.insert(j);
		else if(user[j].zip/10000==5)
		   loc5.insert(j);
	   else if(user[j].zip/10000==6)
		   loc6.insert(j);
		else if(user[j].zip/10000==7)
		   loc7.insert(j);
	   else if(user[j].zip/10000==8)
		   loc8.insert(j);
        else
		   loc9.insert(j);
   }

   //按年龄分类,设置位置向量
    for(int j = 0;j<USER_NUM;j++)
	{
		if(user[j].age<12)
			user_pos[j].cluster_age = children;
        else if(user[j].age>= 12 && user[j].age < 18) 
			user_pos[j].cluster_age = teenagers;
		else if(user[j].age >= 18 && user[j].age < 40)
			user_pos[j].cluster_age = adults;
		else
			user_pos[j].cluster_age = seniors;
   }
   //按性别分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].sex=="M")
		   user_pos[j].cluster_sex = male;
	   else
		   user_pos[j].cluster_sex = female;
   }
   //按职业分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	   if(user[j].occupation=="administrator")
		   user_pos[j].cluster_occupation = administrator;
	   else if(user[j].occupation=="artist")
		   user_pos[j].cluster_occupation = artist;
	    else if(user[j].occupation=="executive")
		   user_pos[j].cluster_occupation = executive;
	    else if(user[j].occupation=="engineer")
		   user_pos[j].cluster_occupation = engineer;
		else if(user[j].occupation=="entertainment")
		   user_pos[j].cluster_occupation = entertainment;
		else if(user[j].occupation=="healthcare")
		   user_pos[j].cluster_occupation = healthcare;
		else if(user[j].occupation=="homemaker")
		   user_pos[j].cluster_occupation = homemaker;
		else if(user[j].occupation=="librarian")
		   user_pos[j].cluster_occupation = librarian;
	    else if(user[j].occupation=="marketing")
		   user_pos[j].cluster_occupation = marketing;
	    else if(user[j].occupation=="none")
		   user_pos[j].cluster_occupation = none;
		else if(user[j].occupation=="programmer")
		   user_pos[j].cluster_occupation = programmer;
		else if(user[j].occupation=="scientist")
		   user_pos[j].cluster_occupation = scientist;
		else if(user[j].occupation=="student")
		   user_pos[j].cluster_occupation = student;
	    else if(user[j].occupation=="technician")
		   user_pos[j].cluster_occupation = technician;
		else if(user[j].occupation=="writer")
		   user_pos[j].cluster_occupation = writer;
		else
		   user_pos[j].cluster_occupation = other;
   }
   //按用户所在地区分类,设置位置向量
   for(int j = 0;j<USER_NUM;j++)
   {
	    if(user[j].zip/10000==0)
		   user_pos[j].cluster_location = loc0;
	   else if(user[j].zip/10000==1)
		   user_pos[j].cluster_location = loc1;
		else if(user[j].zip/10000==2)
		   user_pos[j].cluster_location = loc2;
	   else if(user[j].zip/10000==3)
		   user_pos[j].cluster_location = loc3;
		else if(user[j].zip/10000==4)
		   user_pos[j].cluster_location = loc4;
	   else if(user[j].zip/10000==4)
		   user_pos[j].cluster_location = loc4;
		else if(user[j].zip/10000==5)
		   user_pos[j].cluster_location = loc5;
	   else if(user[j].zip/10000==6)
		   user_pos[j].cluster_location = loc6;
		else if(user[j].zip/10000==7)
		   user_pos[j].cluster_location = loc7;
	   else if(user[j].zip/10000==8)
		   user_pos[j].cluster_location = loc8;
        else
		   user_pos[j].cluster_location = loc9;
   }

   for(int j = 0;j<USER_NUM;j++)
   {
	          if(j<=8)
	          cout<<"用户"<<j+1<<"  的位置向量为：  ";
	          if(j>8&&j<=98)
	          cout<<"用户"<<j+1<<" 的位置向量为：  ";
              if(j>98)
	          cout<<"用户"<<j+1<<"的位置向量为：  ";

			  if(user_pos[j].cluster_age == children)
				  cout<<"["<<"children,";
			  if(user_pos[j].cluster_age == teenagers)
				  cout<<"["<<"teenagers,";
			  if(user_pos[j].cluster_age == adults)
				  cout<<"["<<"adults,";
			  if(user_pos[j].cluster_age == seniors)
				  cout<<"["<<"seniors,";
		   
			  if(user_pos[j].cluster_sex == male)
				  cout<<" M,";
			  if(user_pos[j].cluster_sex == female)
				  cout<<" F,";

			  if(user_pos[j].cluster_occupation == administrator)
				  cout<<" administrator,";
			  if(user_pos[j].cluster_occupation == artist)			 
				  cout<<" artist,";
			  if(user_pos[j].cluster_occupation == executive)
				  cout<<" executive,";
			  if(user_pos[j].cluster_occupation == engineer)
				  cout<<" engineer,";
			  if(user_pos[j].cluster_occupation == entertainment)			  
					cout<<" entertainment,";
			  if(user_pos[j].cluster_occupation == healthcare)
				   cout<<" healthcare,";
			  if(user_pos[j].cluster_occupation == homemaker)
				  cout<<" homemaker,";
			  if(user_pos[j].cluster_occupation == librarian)
				  cout<<" librarian,";
			  if(user_pos[j].cluster_occupation == marketing)
				  cout<<" marketing,";
			  if(user_pos[j].cluster_occupation == none)
				  cout<<" none,";
			  if(user_pos[j].cluster_occupation == programmer)
				  cout<<" programmer,";
			  if(user_pos[j].cluster_occupation == scientist)
				  cout<<" scientist,";
			  if(user_pos[j].cluster_occupation == student)
				  cout<<" student,";
			  if(user_pos[j].cluster_occupation == technician)
				  cout<<" technician,";
			  if(user_pos[j].cluster_occupation == writer)
				  cout<<" writer,";
			  if(user_pos[j].cluster_occupation == other)
				  cout<<" other,";

			  if(user_pos[j].cluster_location == loc0)
				  cout<<" loction 0]";
			  if(user_pos[j].cluster_location == loc1)
				  cout<<" loction 1]";
			  if(user_pos[j].cluster_location == loc2)
				  cout<<" loction 2]";
			  if(user_pos[j].cluster_location == loc3)
				  cout<<" loction 3]";
			  if(user_pos[j].cluster_location == loc4)
				  cout<<" loction 4]";
			  if(user_pos[j].cluster_location == loc5)
				  cout<<" loction 5]";
			  if(user_pos[j].cluster_location == loc6)
				  cout<<" loction 6]";
			  if(user_pos[j].cluster_location == loc7)
				  cout<<" loction 7]";
			  if(user_pos[j].cluster_location == loc8)
				  cout<<" loction 8]";
			  if(user_pos[j].cluster_location == loc9)
				  cout<<" loction 9]";

			  cout<<"\n";
	}
}
