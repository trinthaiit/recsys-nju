#include <iostream>
#include <fstream>
#include <map>
#include <vector>

#define TARGET_USER 926
#define K 10
#define COLLAB_THRESHOLD 10

using namespace std;

struct UserProf
{
    int id;
    int age;
    char gender;
    string occup;
    string zip;
};

struct RatingInfo
{
    int user_id;
    int item_id;
    int rating;
    int timestamp;
};

struct CollabItemInfo
{
    int item_id;
    int collab_num;
    double total_rating;
};

typedef vector<UserProf*>::iterator UPVIterator;
typedef vector<RatingInfo*>::iterator RIVIterator;

void printProf(UserProf *prof);
int getInfo(char *filename, int *users, int *items, int *ratings);
int getUserProfs(char *filename, int users, vector<UserProf*> &userProfs);
void getUsersOfSameOccup(UserProf *prof, vector<UserProf*> &allProfs, vector<UserProf*> &result);
int getRatings(char *filename, int ratings, vector<RatingInfo*> &ratingsVec);
void transformToMap(vector<RatingInfo*> &ratingsVec, map<int, vector<RatingInfo*>*> &ratingMap);
template <typename Iterator> void releaseRes(Iterator begin, Iterator end);
vector<int> getRecommendedItems(map<int, vector<RatingInfo*>*> &ratingMap, vector<UserProf*> &collabGroup, int target);
bool greaterCollabItem(CollabItemInfo *ci1, CollabItemInfo *ci2);

int main(int argc, char **argv)
{
    if(argc < 4)
    {
        cout<<"Too few args."<<endl
            <<"format: "<<argv[0]
            <<" info_file"<<" user_info"<<" training_file"<<endl;
        return -1;
    }

    int users, items, ratings;
    if(getInfo(argv[1], &users, &items, &ratings))
    {
        cout<<"Error happened."<<endl;
        return -1;
    }

    //cout<<"total ratings: "<<ratings<<endl;

    vector<UserProf*> userProfs;
    if(getUserProfs(argv[2], users, userProfs))
    {
        cout<<"Error happened."<<endl;
        return -1;
    }
    
    vector<RatingInfo*> ratingsVec;
    //traning data : test data == 80 : 20
    if(getRatings(argv[3], ratings*8/10, ratingsVec))
    {
        cout<<"Error happened."<<endl;
        return -1;
    }

    map<int, vector<RatingInfo*>*> ratingMap;
    transformToMap(ratingsVec, ratingMap);

    vector<UserProf*> resProfs;
    getUsersOfSameOccup(userProfs[TARGET_USER], userProfs, resProfs);

    vector<int> rec_items = getRecommendedItems(ratingMap, resProfs, TARGET_USER);

    for(vector<int>::iterator itr = rec_items.begin();
            itr != rec_items.end();
            itr ++)
    {
        cout<<"Recommend "<<(*itr)<<" for user "<<TARGET_USER<<endl;
    }

    releaseRes(userProfs.begin(), userProfs.end());
    //how to solve this?
    //releaseRes(ratingMap
    releaseRes(ratingsVec.begin(), ratingsVec.end());

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

int getUserProfs(char *filename, int users, vector<UserProf*> &userProfs)
{
    ifstream user_info(filename);

    if(!user_info)
        return -1;

    UserProf *prof;
    while(user_info)
    {
        prof = new UserProf;
        user_info>>prof->id>>prof->age>>prof->gender>>prof->occup>>prof->zip;
        userProfs.push_back(prof);

        //if(prof->id < 10 || prof->id == users)
        //printProf(prof);
        //if get the last user, get out of loop
        if(prof->id == users)
        {
            //cout<<"break\n";
            break;
        }
    }

    user_info.close();
    return 0;
}

void getUsersOfSameOccup(UserProf *prof, vector<UserProf*> &allProfs, vector<UserProf*> &result)
{
    for(UPVIterator itr = allProfs.begin();
            itr != allProfs.end();
            itr++)
    {
        if((*itr)->occup == prof->occup && (*itr)->id != prof->id)
        {
            result.push_back(*itr);
            //printProf(*itr);
        }
    }
}

template <typename Iterator> void releaseRes(Iterator begin, Iterator end)
{
    while(begin != end)
    {
        delete *begin;
        begin++;
    }
}

void printProf(UserProf *prof)
{
    cout<<prof->id<<' '<<prof->age<<' '<<prof->gender<<' '<<prof->occup<<' '<<prof->zip<<endl;
}

int getRatings(char *filename, int ratings, vector<RatingInfo*> &ratingsVec)
{
    ifstream ratingFile(filename);

    if(!ratingFile)
        return -1;

    RatingInfo *r_info;
    int count = 0;
    while(ratingFile)
    {
        r_info = new RatingInfo;
        ratingFile>>r_info->user_id>>r_info->item_id>>r_info->rating>>r_info->timestamp;
        ratingsVec.push_back(r_info);
        
        count++;
        if(count == ratings)
            break;
    }

    //cout<<"Total ratings: "<<count<<endl;
    ratingFile.close();
    return 0;
}

void transformToMap(vector<RatingInfo*> &ratingsVec, map<int, vector<RatingInfo*>*> &ratingMap)
{
    RatingInfo *temp;
    vector<RatingInfo*> *riv;
    for(RIVIterator itr = ratingsVec.begin();
            itr != ratingsVec.end();
            itr++)
    {
        temp = *itr;
        if(ratingMap.find(temp->user_id) == ratingMap.end())
        {
            //the user vector should be instantiated
            riv = new vector<RatingInfo*>;
            ratingMap[temp->user_id] = riv;
        }
        else
        {
            //the vector is there!
            riv = ratingMap[temp->user_id];
        }

        riv->push_back(temp);
    }
}

vector<int> getRecommendedItems(map<int, vector<RatingInfo*>*> &ratingMap, vector<UserProf*> &collabGroup, int target)
{
    map<int, CollabItemInfo*> collabItemMap;
    vector<RatingInfo*> *tempv;
    RatingInfo *tempRatingInfo;
    CollabItemInfo *tempItemInfo;

    //get all items data
    //the items are rated by users in collabrotive group
    for(UPVIterator itr = collabGroup.begin();
            itr != collabGroup.end();
            itr++)
    {
        //get collabrative user's rating items
        tempv = ratingMap[(*itr)->id];

        for(vector<RatingInfo*>::iterator itr2 = tempv->begin();
                itr2 != tempv->end();
                itr2++)
        {
            tempRatingInfo = *itr2;
            
            if(collabItemMap.find(tempRatingInfo->item_id) == collabItemMap.end())
            {
                tempItemInfo = new CollabItemInfo;
                tempItemInfo->item_id = tempRatingInfo->item_id;
                tempItemInfo->collab_num = 0;
                tempItemInfo->total_rating = 0;
                collabItemMap[tempRatingInfo->item_id] = tempItemInfo;
            }
            else
            {
                tempItemInfo = collabItemMap[tempRatingInfo->item_id];
            }

            tempItemInfo->collab_num++;
            tempItemInfo->total_rating += tempRatingInfo->rating;
        }
    }

    //move data from map to vector for the sake of sorting
    vector<CollabItemInfo*> collabItemVec;
    for(map<int, CollabItemInfo*>::iterator itr3 = collabItemMap.begin();
            itr3 != collabItemMap.end();
            itr3++)
    {
        CollabItemInfo *tempItemInfo = itr3->second;
        if(tempItemInfo->collab_num >= COLLAB_THRESHOLD)
            collabItemVec.push_back(tempItemInfo);
    }
    
    //sort rating
    sort(collabItemVec.begin(), collabItemVec.end(), greaterCollabItem);

    //get results
    vector<int> results;
    int count = 0;
    for(vector<CollabItemInfo*>::iterator itr4 = collabItemVec.begin();
            itr4 != collabItemVec.end() && count < K;
            itr4++)
    {
        results.push_back((*itr4)->item_id);
        count++;
    }

    cout<<"Item\t\tAverage\t\tCollabNum"<<endl;
    for(vector<CollabItemInfo*>::iterator itr5 = collabItemVec.begin();
            itr5 != collabItemVec.end();
            itr5++)
    {
        CollabItemInfo *tempItem = *itr5;
        cout<<tempItem->item_id<<"\t\t"<<(tempItem->total_rating/tempItem->collab_num)<<"\t\t"<<tempItem->collab_num<<endl;
    }
    //release res
    releaseRes(collabItemVec.begin(), collabItemVec.end());

    return results;
}

bool greaterCollabItem(CollabItemInfo *ci1, CollabItemInfo *ci2)
{
    double ave1, ave2;
    ave1 = ci1->total_rating / ci1->collab_num;
    ave2 = ci2->total_rating / ci2->collab_num;

    return ave1 > ave2;
}

