//这是一个很beta的系统，需要吐槽的地方很多。特别是keyword_link那张表我那么写跑的效率很低。这点要向俊哥学习。

1.dataprocess文件夹内是俊哥计算keyword_product_weight的过程，请确认先跑过里面的create_table.sql之后再跑compute_keyword_product_weight.php。

2.主目录下的各个文件说明：

	keyword_link_jaccard0.2.php——这个是计算关键字关联表的，有数据库操作，将结果写入keyword_link表。

	search.php/html——这个是测试页面了，最终的权重计算也在里面。

其他的先忽略。

接下来主要是测试，改代码，尝试一些新的关联方式，等商品属性到了之后再考虑改进关键字与商品属性的关联。

2012/2/16
一个手动的测试版本,初步感觉thesexylingerie.com那份数据还不如bagsok,具体原因我马上报告跟进。

2012/2/17
主要更新 hit_rate_detection.php

2012/2/20
主要更新 推荐测试(未完成).xlsx
目前没有采用比较正规的方法（尝试了下MAE，结果很不好看），只是先看了看我们推荐的列表（对于测试用户集）其中有商品会被浏览的概率。
目前来看，在测试数据很有限的情况下(特别是当我把训练集的比例提高到0.9之后，依然有将近一半的概率测试用户会去浏览训练用户没有浏览过的页面，而这些页面是永远不可能被推荐的)，命中率我感觉还不错。
在没有新数据到位的情况下，我这接着会尝试把系统转移到bagsok上去，再尝试做做测试。

2012/2/21
主要更新 文件精简
temp与backup中均属无用文件。
整个系统的测试流程
1.运行最初的thesexylingerie.sql;
2.运行dataprocess文件夹下的create_table_keyword.sql;
3.运行dataprocess文件夹下的data_preprocess.php（训练用户所占比例可通过修改preprocess_user_table.php的train_factor参数）;
4.运行keyword_link_jaccard0.2.php;
5.运行hit_rate_detection.php查看hit_rate结果;

2012/2/23
修正了命中率计算的一个重大错误，新的结果已上传。

2012/2/25
主要更新:hot_recommend_list.php；修复了之前几个bug。
做了关键字推荐和热门推荐的对比。

2012/3/1
修正了俊哥指出的代码错误。更新了测试结果。

