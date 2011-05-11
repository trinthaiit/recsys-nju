/*yangrui's code*/

#include <iostream>
#include <fstream.h>
#include <cstring>

using namespace std;
//#define USER_NUM  943
//#define ITEM_NUM  1683

struct user_info
{
    int ID;
    int age;
    string sex;
    string occupation;
    string ZIP;

};

int main()
{
    ifstream OpenFile("user_comp_tran.txt");
    ofstream SaveFile("user_training.txt");
    ofstream SaveFile1("user_test.txt");

    int i,j,data;
    string str;
    for(i=0;i<500;i++)
    {
            OpenFile>>data;
            SaveFile <<data;
            SaveFile <<',';
            //user[i].ID = data;
            OpenFile>>data;
            SaveFile <<data;
            SaveFile <<',';
            //user[i].age = data;
            OpenFile>>str;
            SaveFile <<str;
            SaveFile <<',';
            //user[i].sex = str;
            OpenFile>>str;
            SaveFile <<str;
            SaveFile <<',';
            //user[i].occupation = str;
            OpenFile>>str;
            SaveFile <<str;
            SaveFile <<',';
            OpenFile>>data;
            SaveFile <<data<<',';
            OpenFile>>data;
            SaveFile <<data<<',';
            OpenFile>>data;
            SaveFile <<data<<'\n';
    }
    for(j=0;j<100;j++)
    {
            OpenFile>>data;
            SaveFile1 <<data;
            SaveFile1 <<',';
            //user[i].ID = data;
            OpenFile>>data;
            SaveFile1 <<data;
            SaveFile1 <<',';
            //user[i].age = data;
            OpenFile>>str;
            SaveFile1 <<str;
            SaveFile1 <<',';
            //user[i].sex = str;
            OpenFile>>str;
            SaveFile1 <<str;
            SaveFile1 <<',';
            //user[i].occupation = str;
            OpenFile>>str;
            SaveFile1 <<str;
            SaveFile1 <<',';
            OpenFile>>data;
            SaveFile1 <<data<<',';
            OpenFile>>data;
            SaveFile1 <<data<<',';
            OpenFile>>data;
            SaveFile1 <<data<<'\n';
    }
    OpenFile.close();
    SaveFile.close();
    SaveFile1.close();
    return 0;
}



