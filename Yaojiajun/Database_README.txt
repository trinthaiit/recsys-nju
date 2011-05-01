/*********************************************/
      /*这里写的是数据库表所对应的类*/
/*********************************************/
/*对应数据库表 User */
class User
{
	long id;
    /******Cold user data*****/
	int age;
	char gender;  //M for man F for female
	string occupation;
	string zipCode;

    /******α-community data*****/
	/*通过计算得到该用户所在的community*/
	AgeCommunity ageCommunity;
	OccupationCommunity occupationCommunity;
	LocationCommunity locationCommunity;
	GenreCommunity genreCommunity;
	RatingCommunity ratingCommunity;
}

/*对应数据库表 Movie */
class Movie
{
	long id;
	string title;
	Date releaseDate;
	string IMDBUrl
	/* The following 19 fields are the genres, a 1 indicates the movie
	 * is of that genre, a 0 indicates it is not; movies can be in
	 * several genres at once.
	 */
	int unknowGenre;
	int actionGenre;
	int adventureGenre;
	int animationGenre;
	int childrenGenre;
	int comedyGenre;
	int crimeGenre;
	int documentaryGenre;
	int dramaGenre;
	int fantasyGenre;
	int film-NoirGenre;
	int horrorGenre;
	int musicalGenre;
	int mysteryGenre;
	int romanceGenre;
	int sci-FiGenre;
	int thrillerGenre;
	int warGenre;
	int westernGenre;
}

/*对应数据库表 GenreCommunity, User_GenreCommunity */
class GenreCommunity
{
	long id;
	string name;
	vector<User> userList;
}

/*对应数据库表 RatingCommunity, User_RatingCommunity */
class RatingCommunity
{
	long id;
	string name;
	vector<User> userList;
}

/*对应数据库表 AgeCommunity, User_AgeCommunity */
class AgeCommunity
{
	long id;
	string name;
	vector<User> userList;
}

/*对应数据库表 OccupationCommunity, User_OccupationCommunity */
class OccupationCommunity
{
	long id;
	string name;
	vector<User> userList;
}

/*对应数据库表 LocationCommunity, User_LocationCommunity */
class LocationCommunity
{
	long id;
	string name;
	vector<User> userList;
}

/*对应数据库表 User_Movie */
/*表明某个用户对某个电影的评分*/
class User_Movie
{
	long id;
	User user;
	Movie movie;
	int rating;
}