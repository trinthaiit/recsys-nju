/*yangrui's code*/

#include <iostream>
#include <fstream.h>
using namespace std;

int main()
{
    ifstream OpenFile("u.user");
    ofstream SaveFile("user.txt");

    char ch;
    while(!OpenFile.eof())
    {
       OpenFile.get(ch);
       if(ch == '|')
            SaveFile <<' ';
       else
            SaveFile <<ch;
    }
    OpenFile.close();
    SaveFile.close();
    return 0;
}
