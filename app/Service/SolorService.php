<?php

namespace App\Service;

use App\Models\SolorTerms as SolorTermsModel;
use App\Models\News as NewsModel;

class SolorService
{
    /** 节气model层模型 */
    protected $solorTermsModel;
    protected $newsModel;
    protected $list = [];

    public function __construct(SolorTermsModel $solorTermsModel,NewsModel $newsModel)
    {
        $this->solorTermsModel = $solorTermsModel;
        $this->newsModel = $newsModel;
    }

    /**
     * 获取首页符合当前节气内容
     * return array
     */
    public function getSolorList()
    {
        $list = $this->list;
        $stampTime = time();
        $month = intval(date('m',$stampTime));
        $day = date('d');
        $data = $this->solorTermsModel->queryByMonthlist($month);
        if (!isset($data)){
            return Result(0,'暂无节气数据!');
        }

        foreach ($data as $item){
            $result = abs($item->day - $day);
            $id = $item->id;
            array_push($list,[
               'id' => $id,
               'count' => $result
            ]);
        }

        usort($list,function ($newValue ,$oldValue){
                $item = $newValue['count'];
                $NewItem = $oldValue['count'];
                if($item == $NewItem) return 0;
                return ($item > $NewItem) ? 1 : -1;
        });
        $id = $list[0]['id'];

        if (!empty($id)){
            $data = $this->newsModel->queryBySolorIdFind($id);
        }else{
            /** 否则就返回最新一条文章 */
            $data = $this->newsModel->queryByFind();
        }

        return $data;
    }
}
