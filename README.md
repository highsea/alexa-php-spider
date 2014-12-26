alexa-php-
==========

一个php爬虫小程序；获取 www.alexa.com 多个分类 top500 的数据

#### Demo：

> +   1、可获取 http://www.alexa.com/topsites/ 
> +   2、mysql信息自行设置，基于 phpQuery
> +   3、若30s超时错误，请刷新重试即可，为了访问顺利，推荐代（**fan**）理（**qiang**）https://github.com/highsea/Hosts
> +   3、文件 *alexa_top_global.sql* 是对 **global** 分类的爬取

#### 执行爬虫，具体见 index.php

> + 参数 category类别：global、countries、category，
> + 参数 page页码（0-n） ，
> + 参数 name子类别：global下无子分类；county 下分类：见index.php；category 下分类：见index.php
> + 参数times ：0为第一次运行，创建表；1为表存在；默认1
> + 提交链接 demo: spiderall.php?category=global&page=0
