/*yangrui's code*/

#include <iostream>
#include <fstream.h>
#include <cstring>

using namespace std;
#define USER_NUM  943
#define ITEM_NUM  1683

struct user_info
{
    int ID;
    int age;
    string sex;
    string occupation;
    string ZIP;

};


/*void user_init()
{
    int i = 0;
    string str;
    int data;
    ifstream OpenFile("user.txt");
    while(!OpenFile.eof())
    {
        OpenFile>>data;
        user[i].ID = data;
        OpenFile>>data;
        user[i].age = data;
        OpenFile>>str;
        user[i].sex = str;
        OpenFile>>str;
        user[i].occupation = str;
        OpenFile>>str;
        user[i].ZIP = str;
        i++;
    }
    OpenFile.close();
}*/


int main()
{
    user_info user[USER_NUM+1];
    char rate[USER_NUM+1][ITEM_NUM+1] = {0};
    short item_to_young[ITEM_NUM+1];
    int i = 0;
    int data,num_u,num_i;
    int top[11];
    //user_init();
    string str;
    ifstream OpenFile("user.txt");
    ifstream OpenFile1("u1.base");

    while(!OpenFile.eof())
    {
        OpenFile>>data;
        user[i].ID = data;
        OpenFile>>data;
        user[i].age = data;
        OpenFile>>str;
        user[i].sex = str;
        OpenFile>>str;
        user[i].occupation = str;
        OpenFile>>str;
        user[i].ZIP = str;
        i++;
    }
    while(!OpenFile1.eof())
    {
        OpenFile1>>num_u;
        OpenFile1>>num_i;
        OpenFile1>>data;
        OpenFile1>>str;
        rate[num_u][num_i] = data;
    }
    OpenFile.close();
    OpenFile1.close();

    for(int j = 1;j<USER_NUM+1;j++)
        if(user[j-1].age<25 && user[j-1].age >= 15)
        {    for(int k =1;k<ITEM_NUM+1;k++)
                item_to_young[k] += rate[j][k];
        }
    int n = 0;
    int temp_k,temp;
    while(n<10)
    {   int max = 0;
        for(int m = 0;m<ITEM_NUM-n;m++)
        {
            if(max <= item_to_young[m+1])
            {
                temp_k = m+1;
                max = item_to_young[m+1];
            }
        }
        temp = item_to_young[temp_k];
        item_to_young[temp_k] = item_to_young[ITEM_NUM-n];
        item_to_young[ITEM_NUM-n] = temp;
        top[n] = temp_k;
        n++;
    }
    for(int i1= 0; i1<10;i1++)
        cout<<top[i1]<<' ';
    return 0;
}
