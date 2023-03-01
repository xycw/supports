<?php
namespace Customers\Model;
use Think\Model;

class PromotionDepartMentModel extends Model {
    //ym_site订单表 连接 promotion_department部门表
    const PROMOTION_DEPARTMENT = '__PROMOTION_DEPARTMENT__ p ON s.system_depart=p.department_id';

	protected $tableName = 'promotion_department';

}