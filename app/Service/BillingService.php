<?php

namespace App\Service;

use App\Models\MemberBillDetails as MemberBillDetailsModel;
use App\Models\User as UserModel;
use App\Models\HelpOrderRecord as HelpOrderRecordModel;

class BillingService
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var MemberBillDetailsModel
     */
    protected $memberBillDetailsModel;

    /**
     * @var HelpOrderRecordModel
     */
    protected $helpOrderRecordModel;

    /** 注入用户模型 与 开单记录模型 */
    public function __construct(UserModel $userModel,MemberBillDetailsModel $memberBillDetailsModel,HelpOrderRecordModel $helpOrderRecordModel)
    {
        $this->memberBillDetailsModel = $memberBillDetailsModel;
        $this->userModel = $userModel;
        $this->helpOrderRecordModel = $helpOrderRecordModel;
    }

    /**
     * 查询是否有开单记录
     * @param array $params
     */
    public function checkExistBillBill(array $params)
    {
        $user = $this->userModel->queryByIdOne($params['user_id']);
        if ($user->is_doctor == 1){
            /** 查询是否有用户请您（医生）开单 */
            $data = $this->memberBillDetailsModel->queryByExistUserFind($params['user_id']);
            $data->title = $data->realname .'请您为ta开方，点击查看!';
            $data->is_btn = false;
        }else{
            /** 查询是否有医生为您开单 */
            $data = $this->helpOrderRecordModel->queryByExistDoctorFind($params['user_id']);
            if ($data){
                $data->title = $data->realname .'医生已为您开具好膳食方,点击查看购物车!';
                $data->is_btn = false;
            }else{
                $data = $this->memberBillDetailsModel->queryByRefuseFind($params['user_id']);
                if(isset($data) && $data->status == 2){
                    $data->title = $data->realname .'医生已拒绝您的开方申请';
                    $data->is_btn = true;
                }
            }
        }

        return $data ?? [];
    }
}
