/*yangrui's code*/

#include <iostream>
#include <fstream.h>
using namespace std;

int main()
{
    ifstream OpenFile("user_comp.txt");
    ofstream SaveFile("user_comp_tran.txt");

    char ch;
    while(!OpenFile.eof())
    {
       OpenFile.get(ch);
       if(ch == ',')
            SaveFile <<' ';
       else
            SaveFile <<ch;
    }
    OpenFile.close();
    SaveFile.close();
    return 0;
}
