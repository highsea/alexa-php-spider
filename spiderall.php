<?php 
//header('Content-Type: application/x-javascript');  
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_HOST', '');
define('QUERY_URL', 'http://www.alexa.com/topsites/');


$category = getSome('category');
$page = getSome('page');
$times = issetSome('times','1');

queryAll($category, $page, $times);


function queryAll($category, $page, $times){

    

    include '../phpQuery/phpQuery.php'; 

    switch ($category) {
        case 'global':
            $searchUrl = QUERY_URL.$category.';'.$page;
            $tableName = $category;
            $sql_c = sqlCREATE($tableName);
            break;

        case 'countries':
            $name = issetSome('name', 'CN');
            $searchUrl = QUERY_URL.$category.';'.$page.'/'.$name;
            $tableName = $category.'_'.$name;
            $sql_c = sqlCREATE($tableName);
            break;

        case 'category':
            $name = issetSome('name', 'Adult');
            $tableName = $category.'_'.$name;
            $sql_c = sqlCREATE($tableName);

            //这个name可以是这样 Adult/Arts
            $searchUrl = QUERY_URL.$category.';'.$page.'/Top/'.$name;
            break;
        
        default:
            customJsonRes('204', '没有内容！', 'check category & page !');
            break;
    }

    if ($times==0) {
        //第一次运行，创建表
        sql_insert_update_delete($sql_c);
    } 
    
    //增加 字段
    //$sqlALTER = "ALTER table alexa_top_china_1 add (rank int(3));";

    phpQuery::newDocumentFile($searchUrl);
    $artlist = pq(".page-product-content")->find('section')->find('div');
    foreach ($artlist['>ul'] as $LI) {
        $li_Rank = pq($LI)->find('.count')->text();
        $li_name = pq($LI)->find('.desc-paragraph')->find('a')->text();
        $li_intro = pq($LI)->find('.description')->text();
    }
    $array_Rank = explode ('===', preg_replace('/\s/','===', $li_Rank));
    $array_name = explode ('===', preg_replace('/\s/','===', $li_name));
    $array_intro = explode ('===' ,preg_replace('/\n/','===', $li_intro));

    if (empty($li_Rank)) {
        customJsonRes('404', 'no data', 'check page');
    } 

    sqlINSERT_query($array_Rank, $array_name, $array_intro, $tableName);

    $result = array(
        'tableName'=>$tableName,
        'page'=>$page
    );
    customJsonRes('200', 'success', $result);
}



/**
*  增 删 改 操作sql_insert_update_delete
*/
function sql_insert_update_delete($query){

    $con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
    if (!$con) {
        die('could not connect:'. mysql_error());
    } 
    mysql_select_db(DB_NAME,$con);
    if (!mysql_query($query,$con)){

        die('Error: ' . mysql_error());
    }
    return "add success";
    mysql_close($con);

}

/**
*   创建表 sqlCREATE
*   id 主键序号 date 写时间
*   rank 排名 name 域名 intro 简介 type 类型（1当天、7天、30天、6个月） 
*/
function sqlCREATE ($names){

    $names = strtolower(trim($names));
    //$names = preg_replace('/\\/', '_', $names);
    return  "CREATE TABLE alexa_top_".$names." (
        `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
        `rank` int(5) DEFAULT '0',
        `name` varchar(50) CHARACTER SET utf8 DEFAULT '0',
        `intro` varchar(255) CHARACTER SET utf8 DEFAULT '0',
        `date` datetime DEFAULT '0000-00-00 00:00:00',
        `type` int(1) DEFAULT '1',
        PRIMARY KEY (`id`)
    )";

}
/*

*/
function sqlINSERT_query ($array_Rank, $array_name, $array_intro, $names){

    $names = strtolower(trim($names));
    //$names = preg_replace('/^\\/i', '_', $names);

    for ($i=0; $i <count($array_Rank) ; $i++) { 

        $sql_I = "INSERT INTO `alexa_top_".$names."` (`name`, `intro`, `date`, `rank`)";
        $sql_I .= "VALUES ('".$array_name[$i]."', '".iconv('UTF-8', 'GBK', urlencode($array_intro[$i]))."', '".date("Y-m-d H:i:s",time())."', '".$array_Rank[$i]."')";
        //echo $sql_I;
        sql_insert_update_delete($sql_I);
    }
}

/**
*  customJsonRes 自定义生成json格式数据
*  code int； message char|array；data char|array
*  
*/
function customJsonRes($code, $message, $data){

    $callback = issetSome('callback', 'callback');

    if (!is_numeric($code)) {
        return 'code must be numeric';
    }
    $resultCacheDataJson = array(
        'code' => $code,
        'message' => $message,
        'data' => $data
    );
    echo $callback.'('.json_encode($resultCacheDataJson).')';
    die();
}

//判断是否存在变量
function issetSome($some, $foo){
    return isset($_GET[$some]) ? trim($_GET[$some]) : $foo;
}
//判断是否存在变量
function getSome($variable){
    if (!isset($_GET[$variable])) {
        customJsonRes('203', '非授权信息！', 'null');
    } else{
        if ($_GET[$variable]=='') {
            customJsonRes('204', '没有内容！', 'null');
        } else {
            return trim($_GET[$variable]);
        }
    }
}


?>