#include <iostream>
#include <fstream>

using namespace std;

int getInfo(char *filename, int *users, int *items, int *ratings);

int main(int argc, char **argv)
{
    if(argc < 2)
    {
        cout<<"Too few args."<<endl
            <<"format: "<<argv[0]
            <<" info_file"<<endl;
        return -1;
    }

    int users, items, ratings;
    if(getInfo(argv[1], &users, &items, &ratings))
    {
        cout<<"Error happened."<<endl;
        return -1;
    }
    else
    {
        cout<<"Users: "<<users<<endl
            <<"Items: "<<items<<endl
            <<"Ratins: "<<ratings<<endl
            <<endl;
    }

    return 0;
}

int getInfo(char *filename, int *users, int *items, int *ratings)
{
    string s;

    ifstream userInfo(filename);
    if(!userInfo)
    {
        cerr<<"Cannot open file!"<<endl;
        return -1;
    }

    userInfo>>(*users);
    userInfo>>s;

    userInfo>>(*items);
    userInfo>>s;

    userInfo>>(*ratings);
    userInfo>>s;

    userInfo.close();

    return 0;
}

