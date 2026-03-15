<?php

namespace app\api\controller\discover;
use app\common\controller\Api;
use app\api\controller\discover\Base;
use think\Db;
use think\Config;


header('Access-Control-Allow-Origin:*');//允许跨域
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers:x-requested-with,content-type,token');
    //浏览器页面ajax跨域请求会请求2次，第一次会发送OPTIONS预请求,不进行处理，直接exit返回，但因为下次发送真正的请求头部有带token
    //所以这里设置允许下次请求头带token否者下次请求无法成功
    exit();
}

/**
 * 基本类，初始化请求
 * Class Base
 * @package app\api\controller\coupon
 */

class Common extends Base
{

    protected $noNeedLogin = ['index','type','detail','showCommentLists','locationData'];
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('third');
        $this->user_id = $this->auth->id;
    }


     /**
     * 获取首页数据
     * @param string $keywords     关键字
     * @param string $type         分类
     * @param string $location     定位
     * @param string $user_id      用户id
     * @param string $page         页码
     * @param string $limit        每页数量
     */
    public function index()
    {   
        $keywords = $this->request->request("keywords");
        $type = $this->request->request("type");
        $location = $this->request->request("location");
        $school_id = (int)$this->request->request("school_id", 0);
        $user_id = $this->auth->id ? $this->auth->id : 0;
        $page = $this->request->request("page");
        $limit = $this->request->request("limit")?$this->request->request("limit"):4;
        $list = $this->indexData($keywords,$type,$location,$user_id,$page,$limit,$school_id);
        $this->success('请求成功', $list);
    }
 /**
     * 分类
     */
    public function type()
    {   $type=$this->request->request('type')?$this->request->request('type'):0;
        $list = $this->typeData($type);
        $this->success('请求成功', $list);
        
        
    }
    /**
     * 获取发现详情
     * @param string $id     id

     */
    public function detail()
    {   
        $id = $this->request->request("id");
        $user_id = $this->request->request("user_id")?$this->request->request("user_id"):0;
        if(!isset($id) || $id==''){
            $this->error('参数错误');
        }
        $detail = $this->detailData($id,$user_id);
        $this->success('请求成功', $detail);   
    }
    /**
     * 发布 动态
     * @param string $title        标题
     * @param string $content      内容
     * @param string $coverimage   封面图 
     * @param string $coverimages  组图
     * @param string $tag_ids      标签
     * @param string $top_ids      话题
     * @param string $city         省市区
     * @param string $address      具体地址（非必填）
     * @param string $latlng        经纬度
     * @param string $iscommentdata 是否开启评论:1=开启,2=关闭
     * //$title,$content,$coverimage,$coverimages,$tag_ids,$top_ids,$city,$address,$latlng,$iscommentdata,$id

     */
    public function publish()
    {   $data=$this->request->request();
        // $id = $this->request->request("id")?$this->request->request("id"):'';
        // if(!isset($id) || $id==''){
        //     $this->error('参数错误');
        // }
        if(!isset($data['id'])){

        }
        if(!isset($data['title'])){
            $this->error('请填写动态标题');
        }else{
           // $data['title']=$this->userTextEncode($data['title']);
        }
        if(!isset($data['description'])){
            $this->error('请填写动态描述');
        }else{
           // $data['content']=$this->userTextEncode($data['content']);
        }
         if(!isset($data['content'])){
            $this->error('请填写动态内容');
        }else{
          //  $data['content']=$this->userTextEncode($data['content']);
        }
         if(!isset($data['coverimages'])){
            $this->error('请至少上传一张图片');
        }
        if(!isset($data['coverimage'])){

        }
        if($data['latlng']!=''){
            $discoverConfig=get_addon_config['discover'];
            $mapKey=$discoverConfig['tmapkey']['key'];
            $url='https://apis.map.qq.com/ws/geocoder/v1/?location='.$data['latlng'].'&key='.$mapKey; 
            $result= $this ->http_curl($url);      
            $result=json_decode($result,true);
            $result2=$result['result']['address_component'];
            $result3=$result['result']['formatted_addresses'];
            $municipality=array('上海市','重庆市','天津市','北京市');
            foreach ($municipality as $key => $value) {
              if($result2['province']==$value){
                $result2['province']=str_replace('市','',$value);
              }
            }
            $data['city']=$result2['province'].'/'.$result2['city'].'/'.$result2['district'];
            $data['address']=$result3['rough'];
        }
        $newDescover = $this->publishData($data);
        if($newDescover){
            $this->success('发布成功');
        }else{
            $this->error('发布失败');
        }
    }
    /**
     * 地址逆解析
     * @param string $latlng        经纬度
     */

    public function locationData(){
        $data=$this->request->request();
        if(isset($data['latlng']) && $data['latlng']!=''){
            $discoverConfig=get_addon_config['discover'];
            $mapKey=$discoverConfig['tmapkey']['key'];
            $url='https://apis.map.qq.com/ws/geocoder/v1/?location='.$data['latlng'].'&key='.$mapKey; 
            $result= $this ->http_curl($url);      
            $result=json_decode($result,true);
            $result2=$result['result']['address_component'];
            $result3=$result['result']['formatted_addresses'];
            $datas['province']=$result2['province'];
            $municipality=array('上海市','重庆市','天津市','北京市');
            foreach ($municipality as $key => $value) {
              if($datas['province']==$value){
                $datas['province']=str_replace('市','',$value);
              }
            }
            //var_dump($datas['province']);exit;
            //if($datas['province'])
            $datas['city']=$result2['city'].'/'.$result2['district'];
            $datas['address']=$result3['rough'];
            $this->success('获取成功',$datas);
        }else{
            $this->error('参数错误');
        }
    }
    
    /**
     * 获取评论列表
     * @param string $discover_id
     * @param string $page 第几页，1开始 
     * @param string $limit 每一页显示多少数量
    
     */
    
 public function showCommentLists()
    {   
        $user_id=$this->request->request('user_id')?$this->request->request('user_id'):0;
        $page=$this->request->request('page');
        $discover_id=$this->request->request('discover_id');
        $limit=$this->request->request('limit')?$this->request->request('limit'):10;
        $listData = $this->showCommentListsMethod($discover_id,$user_id,$page,$limit);
        $this->success('获取成功',$listData);
        
        
    }
    /**
     * 是否点赞了某个动态
     * @param string $discover_id 
     */
    
 public function isFavor()
    {   
        $discover_id=$this->request->request('discover_id')?$this->request->request('discover_id'):0;;
        $listData = $this->isFavorMethod($discover_id);
        $this->success('获取成功',$listData);
        
        
    }
     /**
     * 获取小程序Token
     *
     * 
     */
    public function getAccessToken()
    {   
        $config = get_addon_config('third');
        //var_dump($config);exit;
        $appid=$config['wechat']['app_id'];
        $appsecret =$config['wechat']['app_secret'];
        $isExpires = $this->isExpires();

        if($isExpires === false){
            //到期，获取新的
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $appsecret;
            $res = $this->https_curl_json($url,'','json');
            // dump($res);
            $arr = json_decode($res,true);
            if($arr && !isset($arr['errcode'])){
                $arr['time'] = time();
                file_put_contents(APP_PATH . '/access_token.json', json_encode($arr));
                return $arr['access_token'];
            }else{
                echo 'error on get access_token';die;
            }
        }else{
            return $isExpires;
        }
        
    }
    
}
