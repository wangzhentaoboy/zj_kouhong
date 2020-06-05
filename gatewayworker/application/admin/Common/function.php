<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 11:51
 */
/**
 *
 * @param $count 要分页的总记录数
 * @param int $pagesize 每页查询条数
 * @param  $where  传递过来的查询条件
 * @return \Think\Page
 */
function getNewPage($count, $pagesize,$where) {
    $p = new Think\Page($count, $pagesize);
  foreach($where as $key=>$val) {
//echo $key.$val;
    $p->parameter  .=  "$key=".urlencode($val).'&';
}
    $p->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录 第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $p->setConfig('prev', '上一页');
    $p->setConfig('next', '下一页');
    $p->setConfig('last', '末页');
    $p->setConfig('first', '首页');
    $p->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p->lastSuffix = false;//最后一页不显示为总页数
    return $p;


}

