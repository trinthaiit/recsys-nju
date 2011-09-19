engine_stat.txt是统计各个搜索引擎在browse？表中的出现次数

extract_keywords.php是从url字符串中提取关键词串的工具（只是作为一个字符串提取出来，要分隔单个关键词使用keywords_aggregate.php中的keywords_array函数）

keywords_aggregate.php是我写的用于关键词聚合的工具，里面包含：
	keywords_array: 将一个关键词串分割成关键词的array
	array_contains: 判断一个array里是否包含特定元素
	occurrence: 计算$keywords_set这个array在$all_keywords_arr这个所有的关键词数组的数组中的出现次数
	expand_dimension：将一个一维数组转成二维数组，如('a', 'b', 'c')转成(('a'), ('b'), ('c'))——仅用于将一维变成二维是使用！其实是为了和上面的array_contains函数配套使用，才有这个函数的
	最后，generate_next：从n长度的关键词组生成n+1大的关键词组，$curr_arr中每个数组长度都是n。比如$keywords_arr = ('a', 'b', 'c')，$curr_arr = (('a'), ('c'))，最终的结果是(('a', 'b'), ('a', 'c'), ('b', 'c')).

keyword_stat.php统计了每一个独立的关键词的出现次数

其他的文件可以忽略。