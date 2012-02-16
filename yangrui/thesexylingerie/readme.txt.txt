//这是一个很beta的系统，需要吐槽的地方很多。特别是keyword_link那张表我那么写跑的效率很低。这点要向俊哥学习。

1.dataprocess文件夹内是俊哥计算keyword_product_weight的过程，请确认先跑过里面的create_table.sql之后再跑compute_keyword_product_weight.php。

2.主目录下的各个文件说明：

	keyword_link_jaccard0.2.php——这个是计算关键字关联表的，有数据库操作，将结果写入keyword_link表。

	search.php/html——这个是测试页面了，最终的权重计算也在里面。

其他的先忽略。

接下来主要是测试，改代码，尝试一些新的关联方式，等商品属性到了之后再考虑改进关键字与商品属性的关联。

2012/2/16
一个手动的测试版本,初步感觉thesexylingerie.com那份数据还不如bagsok,具体原因我马上报告跟进。

