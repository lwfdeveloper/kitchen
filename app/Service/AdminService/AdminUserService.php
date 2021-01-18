<?php

namespace App\Service\AdminService;

use Illuminate\Support\Facades\Redis;
use App\Models\Admin\{AdminUserModel,AdminRoleMenuRelationModel};

class AdminUserService
{
    protected $adminUserModel;
    protected $adminRoleMenuModel;

    public function __construct(AdminUserModel $adminUserModel,AdminRoleMenuRelationModel $adminRoleMenuRelationModel)
    {
        $this->adminUserModel = $adminUserModel;
        $this->adminRoleMenuModel = $adminRoleMenuRelationModel;
    }

    /**
     * 管理员密码md5加密
     * @param string $password
     * @return string
     */
    protected function userPasswordEncryption(string $password)
    {
        return md5($password);
    }

    /**
     * 用户登录
     * @param array $params
     */
    public function userLogin(array $params)
    {
        $params['password'] = $this->userPasswordEncryption($params['password']);
        //判断用户是否存在，账户密码正确与否
        $user = $this->adminUserModel->queryAccountByFind($params['account'],$params['password']);

        if (!isset($user)) {
            return Result(0,'用户名或者密码错误，登录失败!');
        }

        if ($user->status == 0){
            return Result(0,'该账户已被禁用，请联系管理员!');
        }

        if (!isset($user->group_id)){
            return Result(0,'该用户暂无所属组织，登录失败!');
        }

        if (!isset($user->role_id)){
            return Result(0,'该用户暂未分配角色，登录失败!');
        }

        $user = $this->adminUserModel->getUserInfo($user->id);

        /** 获取所拥有的菜单权限列表 **/
        $menuList = $this->adminRoleMenuModel->queryByList($user->role_id);
        $menuList = getTreeMenuData($menuList,'menu_id');
        $user->menu_list = $menuList;

        return $user;
    }


    /**
     * 统计注册人数
     * @param array $params
     */
    public function getUserStatistics(array $params)
    {
        if(isset($params['status'])){
            if(!in_array($params['status'],['1','2'])){
                return Result(0,'status参数有误!');
            }
        }
        if(isset($params['month'])){
            $month = $params['month'];
            if($month > 12 ){
                return Result(0,'月份参数有误!');
            }
        }

        if (!empty($params['status'])){
            if ($params['status'] == 1){
                $date = date('Y-m-d',strtotime('-7 day'));
            }else{
                $date = date('Y-m-d',strtotime('-15 day'));
            }
            $params['date'] = $date ?? false;
        }

        $data = $this->adminUserModel->queryByCountRegister($params ,$date);
        return $data;
    }
}
