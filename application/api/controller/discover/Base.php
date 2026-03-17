<?php

namespace app\api\controller\discover;
use app\common\controller\Api;
use addons\third\model\Third;
use app\api\controller\discover\Basic;
use app\admin\model\discover\Discover;
use app\admin\model\discover\Favor;
use app\admin\model\discover\Tag;
use app\admin\model\discover\Topic;
use app\admin\model\discover\Comment;
use app\admin\model\discover\Attentions;
use app\admin\model\discover\Collect;
use app\admin\model\discover\Log;
use think\Db;
use think\Config;

header('Access-Control-Allow-Origin:*');//允许跨域
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Headers:x-requested-with,content-type,token');
    exit();
}

/**
 * 基本类，初始化请求
 * Class Base
 * @package app\api\controller\moyi
 */
class Base extends Api
{

    protected $noNeedLogin = ['indexData','typeData','detailData','getAccessToken','isExpires','areaJson','areaOrderABCJson'];
    protected $noNeedRight = ['*'];
    protected static $schemaCache = [];
    public function _initialize()
    {
        parent::_initialize();
        $this->discoverModel=new Discover;
        $this->favorModel=new Favor;
        $this->tagModel=new Tag;
        $this->topicModel=new Topic;
        $this->commentModel=new Comment;
        $this->attentionsModel=new Attentions;
        $this->collectModel=new Collect;
        $this->logModel=new Log;
        $this->user_id = $this->auth->id;
        $this->serverImgHost=($this->isHTTPS() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    }

    protected function hasTable($table)
    {
        $key = 'table:' . $table;
        if (!array_key_exists($key, self::$schemaCache)) {
            $fullTable = config('database.prefix') . $table;
            $result = Db::query("SHOW TABLES LIKE '" . addslashes($fullTable) . "'");
            self::$schemaCache[$key] = !empty($result);
        }
        return self::$schemaCache[$key];
    }

    protected function hasColumn($table, $column)
    {
        $key = 'column:' . $table . ':' . $column;
        if (!array_key_exists($key, self::$schemaCache)) {
            if (!$this->hasTable($table)) {
                self::$schemaCache[$key] = false;
            } else {
                $fullTable = config('database.prefix') . $table;
                $result = Db::query("SHOW COLUMNS FROM `{$fullTable}` LIKE '" . addslashes($column) . "'");
                self::$schemaCache[$key] = !empty($result);
            }
        }
        return self::$schemaCache[$key];
    }

    protected function hasSchoolTable()
    {
        return $this->hasTable('school');
    }

    protected function hasUserSchoolSchema()
    {
        return $this->hasColumn('user', 'school_id') && $this->hasColumn('user', 'school_locked');
    }

    protected function hasDiscoverSchoolSchema()
    {
        return $this->hasColumn('discover', 'school_id');
    }

    protected function hasDiscoverModerationSchema()
    {
        return $this->hasColumn('discover', 'audit_status') && $this->hasColumn('discover', 'is_top');
    }

    protected function getDiscoverBlockedWords()
    {
        if ($this->hasTable('discover_blocked_word')) {
            $rows = Db::name('discover_blocked_word')
                ->where('status', 'normal')
                ->order('weigh desc,id desc')
                ->column('word');
            $words = [];
            foreach ($rows as $row) {
                $row = trim((string)$row);
                if ($row !== '') {
                    $words[$row] = $row;
                }
            }
            return array_values($words);
        }
        $raw = config('site.discover_blocked_words');
        if (is_array($raw)) {
            $raw = implode(PHP_EOL, $raw);
        }
        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }
        $items = preg_split('/[\r\n,，;；]+/u', $raw);
        $words = [];
        foreach ($items as $item) {
            $item = trim((string)$item);
            if ($item !== '') {
                $words[$item] = $item;
            }
        }
        return array_values($words);
    }



    protected function normalizeDiscoverModerationText($text)
    {
        return preg_replace('/\s+/u', '', trim((string)$text));
    }

    protected function containsDiscoverBlockedWord($text, $blockedWord)
    {
        $text = (string)$text;
        $blockedWord = trim((string)$blockedWord);
        if ($text === '' || $blockedWord === '') {
            return false;
        }
        if (function_exists('mb_stripos')) {
            return mb_stripos($text, $blockedWord, 0, 'UTF-8') !== false;
        }
        return stripos($text, $blockedWord) !== false;
    }

    protected function assertDiscoverPublishAllowed($data)
    {
        $blockedWords = $this->getDiscoverBlockedWords();
        if (empty($blockedWords)) {
            return;
        }
        $scanFields = ['title', 'description', 'content'];
        foreach ($scanFields as $field) {
            $text = isset($data[$field]) ? (string)$data[$field] : '';
            if ($text === '') {
                continue;
            }
            $normalizedText = $this->normalizeDiscoverModerationText($text);
            foreach ($blockedWords as $blockedWord) {
                if ($this->containsDiscoverBlockedWord($text, $blockedWord) || $this->containsDiscoverBlockedWord($normalizedText, $blockedWord)) {
                    $this->error('内容包含敏感违规信息，请修改后再发布');
                }
            }
        }
    }

    protected function getSchoolSelectFields()
    {
        $fields = ['id', 'name', 'short_name', 'province', 'city', 'area', 'address', 'latitude', 'longitude'];
        $optionalFields = ['logo', 'header_bg_image', 'theme_primary', 'theme_secondary', 'theme_text_color'];
        foreach ($optionalFields as $field) {
            if ($this->hasColumn('school', $field)) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    protected function normalizeSchoolInfo($schoolInfo)
    {
        if (!$schoolInfo) {
            return null;
        }
        foreach (['logo', 'header_bg_image'] as $field) {
            $schoolInfo[$field] = isset($schoolInfo[$field]) ? (string)$schoolInfo[$field] : '';
            if ($schoolInfo[$field] !== '') {
                $schoolInfo[$field] = cdnUrl($schoolInfo[$field], true);
            }
        }
        foreach (['theme_primary', 'theme_secondary', 'theme_text_color'] as $field) {
            $schoolInfo[$field] = isset($schoolInfo[$field]) ? trim((string)$schoolInfo[$field]) : '';
        }
        return $schoolInfo;
    }

    protected function getSchoolInfoById($schoolId)
    {
        $schoolId = (int)$schoolId;
        if ($schoolId <= 0 || !$this->hasSchoolTable()) {
            return null;
        }
        $row = Db::name('school')
            ->where('id', $schoolId)
            ->where('status', 'normal')
            ->field(implode(',', $this->getSchoolSelectFields()))
            ->find();
        return $this->normalizeSchoolInfo($row);
    }

    protected function appendSchoolInfo($userInfo)
    {
        if (!$userInfo) {
            return $userInfo;
        }
        if ($userInfo instanceof \think\Model) {
            $userInfo = $userInfo->toArray();
        } elseif (is_object($userInfo)) {
            $userInfo = (array)$userInfo;
        }
        if (!$this->hasUserSchoolSchema()) {
            $userInfo['school_id'] = 0;
            $userInfo['school_locked'] = '0';
            $userInfo['school_confirm_time'] = null;
            $userInfo['school_name'] = '';
            $userInfo['school_info'] = null;
            return $userInfo;
        }
        $userInfo['school_id'] = isset($userInfo['school_id']) ? (int)$userInfo['school_id'] : 0;
        $userInfo['school_locked'] = isset($userInfo['school_locked']) ? (string)$userInfo['school_locked'] : '0';
        $userInfo['school_confirm_time'] = isset($userInfo['school_confirm_time']) ? $userInfo['school_confirm_time'] : null;
        $schoolInfo = $this->getSchoolInfoById($userInfo['school_id']);
        $userInfo['school_name'] = $schoolInfo ? $schoolInfo['name'] : '';
        $userInfo['school_info'] = $schoolInfo ?: null;
        return $userInfo;
    }

    protected function getCurrentUserSchoolId()
    {
        if (!$this->auth->id || !$this->hasUserSchoolSchema()) {
            return 0;
        }
        return (int)Db::name('user')->where('id', $this->auth->id)->value('school_id');
    }

    protected function getCurrentUserSchoolLocked()
    {
        if (!$this->auth->id || !$this->hasUserSchoolSchema()) {
            return '0';
        }
        return (string)Db::name('user')->where('id', $this->auth->id)->value('school_locked');
    }

    protected function getScopeSchoolId($requestedSchoolId = 0, $requireBound = false)
    {
        $requestedSchoolId = max(0, (int)$requestedSchoolId);
        if ($requireBound && (!$this->auth->id || !$this->hasUserSchoolSchema())) {
            $this->error('请先登录并绑定学校');
        }
        if ($this->auth->id && $this->hasUserSchoolSchema()) {
            $currentSchoolId = $this->getCurrentUserSchoolId();
            if ($currentSchoolId > 0) {
                return $currentSchoolId;
            }
            if ($requireBound) {
                $this->error('请先绑定学校');
            }
        }
        return $requestedSchoolId;
    }

    protected function buildScopedDiscoverWhere($alias = 'discover', $requestedSchoolId = 0, $approvedOnly = true)
    {
        $where = [];
        $where[$alias . '.statusdata'] = '1';
        if ($approvedOnly && $this->hasDiscoverModerationSchema()) {
            $where[$alias . '.audit_status'] = 'approved';
        }
        $schoolId = $this->getScopeSchoolId($requestedSchoolId, false);
        if ($this->hasDiscoverSchoolSchema() && $schoolId > 0) {
            $where[$alias . '.school_id'] = $schoolId;
        }
        return $where;
    }

    protected function getScopedDiscoverInfo($discoverId, $requestedSchoolId = 0, $approvedOnly = true)
    {
        $where = $this->buildScopedDiscoverWhere('A', $requestedSchoolId, $approvedOnly);
        $where['A.id'] = (int)$discoverId;
        return Db::name('discover')->alias('A')->where($where)->find();
    }

    protected function assertDiscoverAccessible($discoverId, $requestedSchoolId = 0, $approvedOnly = true, $message = '该条动态不存在或无权访问')
    {
        $row = $this->getScopedDiscoverInfo($discoverId, $requestedSchoolId, $approvedOnly);
        if (!$row) {
            $this->error($message);
        }
        return $row;
    }

    protected function assertCommentAccessible($commentId, $requestedSchoolId = 0, $message = '该条评论不存在或无权访问')
    {
        $comment = Db::name('discover_comment')->where('id', (int)$commentId)->find();
        if (!$comment) {
            $this->error($message);
        }
        $this->assertDiscoverAccessible((int)$comment['discover_id'], $requestedSchoolId, true, $message);
        return $comment;
    }

    protected function assertAttentionTargetAccessible($attentionId, $requestedSchoolId = 0)
    {
        if (!$this->auth->id || !$this->hasUserSchoolSchema()) {
            return;
        }
        $schoolId = $this->getScopeSchoolId($requestedSchoolId, false);
        if ($schoolId <= 0) {
            return;
        }
        $target = Db::name('user')->where('id', (int)$attentionId)->field('id,school_id')->find();
        if (!$target) {
            $this->error('该用户不存在');
        }
        if ((int)$target['school_id'] > 0 && (int)$target['school_id'] !== $schoolId) {
            $this->error('无权操作其他学校用户');
        }
    }
    public function indexData($keywords=null,$type=null,$location=null,$user_id=0,$page=0,$limit=4,$school_id=0)
    {
          $where = [];
          $where['statusdata']=1;
          if ($this->hasDiscoverModerationSchema()) {
            $where['discover.audit_status'] = 'approved';
          }
          $school_id = max(0, (int)$school_id);
          if ($this->hasDiscoverSchoolSchema()) {
            $school_id = $this->getScopeSchoolId($school_id, true);
          }
          if ($this->auth->id) {
            $user_id = $this->auth->id;
          }
          if ($this->hasDiscoverSchoolSchema() && $school_id > 0) {
            $where['discover.school_id'] = $school_id;
          }
          if(!empty($type)){
            $wheres="find_in_set(".intval($type).",discover.tag_ids)";
          }
          if(!empty($keywords)){
            $where['discover.title']=array('like','%'.$keywords.'%');
          }
          if(!empty($location) && $school_id <= 0){
            $where['discover.city']=array('like','%'.$location.'%');
          }

          $query = $this->discoverModel->with(['user'])->where($where);
          if(!empty($type)){
            $query->where($wheres);
          }
          if ($this->hasDiscoverModerationSchema()) {
            $query->order('discover.is_top','desc')->order('discover.top_sort','desc');
          }
          $discoverList=$query
                       ->order('discover.createtime','desc')
                       ->field('discover.id,discover.content as content,discover.coverimage as image_url,discover.coverimages as image_urlLists,discover.tag_ids as type,discover.title,discover.description as text,discover.user_id,discover.school_id,discover.favorNum,discover.commentNum,discover.createtime,user.nickname,user.avatar')
                       ->paginate($limit)
			           ->each(function($lists, $key){
			               $lists['image_url'] = !empty($lists->image_url) ? cdnUrl($lists->image_url, true) : '';
                           $imageList = !empty($lists->image_urlLists)
                               ? explode(',', $lists->image_urlLists)
                               : (!empty($lists->image_url) ? explode(',', $lists->image_url) : []);
			               $lists['type']='image';
                           foreach ($imageList as $k => $v) {
                             if ($v === null || $v === '') {
                                 unset($imageList[$k]);
                                 continue;
                             }
                             $imageList[$k]=cdnUrl($v,true);
                             $picNames = strrchr($v,'.');
                             if(in_array($picNames, array('.mp4','.avi'))){
                                 $lists['type']='video';
                              }
                          }
                          $imageList = array_values($imageList);
                          $lists['image_urlLists']=implode(',',$imageList);
                          $lists['createtime'] = isset($lists['createtime']) ? (int)$lists['createtime'] : 0;
                          if($this->is_url($lists->avatar)==0){
                             $lists['avatar']=letter_avatar($lists->nickname);
                          }
                          if($this->is_url($lists->avatar)==2){
                             $lists['avatar']=cdnUrl($lists->avatar,true);
                          }
                          $endt='';
                          if(mb_strlen($lists->title)>50){
                              $endt='...';
                          }
                          $endc='';
                          if(mb_strlen($lists->title)>50){
                              $endc='...';
                          }
                          $lists['title']=mb_substr($lists->title, 0, 50, 'utf-8').$endt;
                          $lists['text']=mb_substr($lists->text, 0, 50, 'utf-8').$endc;
                          $lists['index']=$key;
			              return $lists;
			           });
          $data['data']=$discoverList;
          if($user_id>0){
              $data['msgCount']=$this->logModel
                          ->alias('A')
                          ->join('discover B','B.id=A.discover_id')
                          ->join('user C','C.id=A.create_id')
                          ->where(['A.user_id'=>$user_id,'A.readdata'=>'0'])
                          ->count();
            }else{
            $data['msgCount']=0;
        }
          return $data;
    }

    public function attentionData()
    {
          $page=$this->request->request('page');
          $limit=$this->request->request('limit')?$this->request->request('limit'):10;
          $user_id=$this->auth->id;
          $scopeSchoolId = $this->getScopeSchoolId(0, true);
          $where=[];
          $attention_lists=Db::name('discover_attentions')->where('user_id',$user_id)->select();
          $attention_ids=array_column($attention_lists, 'attention_id');
          if(!empty($attention_ids)){
            $where['user_id']=array('in',$attention_ids);
          }else{
            $data_s['list']=[];
            $data_s['total_page']=0;
            $data_s['count']=0;
            $this->error('暂无数据',$data_s);
          }
          $where['statusdata']=1;
          if ($this->hasDiscoverModerationSchema()) {
            $where['audit_status']='approved';
          }
          if ($this->hasDiscoverSchoolSchema() && $scopeSchoolId > 0) {
            $where['school_id']=$scopeSchoolId;
          }
          $discoverCount=Db::name('discover')->alias('A')->join('user B','A.user_id=B.id')->where($where)->count();
          $discoverList=Db::name('discover')->alias('A')->join('user B','A.user_id=B.id')->where($where)->page($page,$limit)->order('A.createtime','desc')->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.nickname,B.avatar')->select();
          $data['list']=$discoverList;
          $data['total_page']=ceil($discoverCount/$limit);
          $data['count']=$discoverCount;
          if($data['count']>0){
            foreach($data['list'] as $key => $value) {
              if($this->is_url($value['avatar'])==0){
                $data['list'][$key]['avatar']=letter_avatar($value['nickname']);
              }
              if($this->is_url($value['avatar'])==2){
                $data['list'][$key]['avatar']=cdnUrl($value['avatar'],true);
              }
              $data['list'][$key]['coverimages']=explode(',', $value['coverimages']);
              foreach ($data['list'][$key]['coverimages']as $kk => $vv) {
                $data['list'][$key]['coverimages'][$kk]=cdnUrl($vv,true);
              }
              $commentData=$this->showCommentListsMethod($value['id'],$user_id,1,3);
              $data['list'][$key]['comment_lists']=$commentData['list'];
              $data['list'][$key]['isfavor']=$this->isFavorMethod($value['id']);
              $data['list'][$key]['isattention']=$this->isAttentionMethod($value['user_id']);
            }
          }
          $this->success('获取成功',$data);
    }

    public function typeData($type)
    {
         $where['auditdata']=0;
         $where['typedata']=0;
         $typeList=Db::name('discover_tag')->where($where)->select();
         $typeData=[];
         if($type!=-1){
            foreach ($typeList as $key => $value) {
             $typeData[$key]['key']=$value['id'];
             $typeData[$key]['list_key']='typeList-'.$value['id'];
             $typeData[$key]['id']=$value['id'];
             $typeData[$key]['label']=$value['name'];
           }
          $selectedData=['key'=>0,'list-key'=>'typeList-0','id'=>'0','label'=>'最新'];
           array_unshift($typeData,$selectedData);
         }
         if($type==-1){
          $typedatas=[];
          foreach ($typeList as $key => $value) {
            $typedatas[$key]['value']=$key;
            $typedatas[$key]['name']=$value['name'];
            $typedatas[$key]['checked']=false;
            $typedatas[$key]['id']=$value['id'];
            $typedatas[$key]['hot']=false;
          }
          $typeData=$typedatas;
         }
         return $typeData;
    }
    /**
     * 获取发现详情
     *
     * @param string $id 
     */
    public function detailData($id,$user_id=0)
    {
         $user_id = $this->auth->id ? $this->auth->id : (int)$user_id;
         $scopeSchoolId = $this->auth->id ? $this->getScopeSchoolId(0, true) : 0;
         $where = $this->buildScopedDiscoverWhere('A', $scopeSchoolId, true);
         $where['A.id']=intval($id);
         $typeList=Db::name('discover')->alias('A')->join('user B','B.id=A.user_id')->where($where)->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.avatar,B.nickname')->find();
         if(empty($typeList)){
          $this->error('该条动态不存在或无权访问');
         }
         $favorCount=$this->favorModel->where(['discover_id'=>$id,'typedata'=>1])->count();
         $commentCount=$this->commentModel->where(['discover_id'=>$id,'statusdata'=>1])->count();
         $this->discoverModel->where('id', $id)->setInc('browse');
         $typeList['coverimage']=cdnUrl($typeList['coverimage'],true);
         $coverPicVideos=[];
         $typeList['coverimages_']=explode(',',$typeList['coverimages']);
         foreach ($typeList['coverimages_'] as $key => $value) {
              $typeList['coverimages_'][$key]=cdnUrl($value,true);
              $picNames = strrchr($value,'.');
              if(in_array($picNames, array('.jpg','.jpeg','.png','.gif','.JPEG','.JPG'))){
                 $coverPicVideos[$key]['type']='image';
              }else if(in_array($picNames, array('.mp4','.avi'))){
                 $coverPicVideos[$key]['type']='video';
              }else{
                 $coverPicVideos[$key]['type']='';
              }
              $coverPicVideos[$key]['url']=cdnUrl($value,true);
         }
         $typeList['coverimages']=implode(',',$typeList['coverimages_']);
         $typeList['coverPicVideos']=$coverPicVideos;
         $typeList['commentNum']=$commentCount;
         $typeList['favorNum']=$favorCount;
         $typeList['isFavor']=0;
         $typeList['isAttention']=0;
         if($this->is_url($typeList['avatar'])==0){
               $typeList['avatar']=letter_avatar($typeList['nickname']);
         }
         if($this->is_url($typeList['avatar'])==2){
               $typeList['avatar']=cdnUrl($typeList['avatar'],true);
         }
         if($user_id!=0){
            $data['typedata']=1;
            $data['user_id']=$user_id;
            $data['discover_id']=$id;
            $isFavorStatus=$this->favorModel->where($data)->count();
            if($isFavorStatus){
               $typeList['isFavor']=1;
            }
            $isAttentionStatus=$this->attentionsModel->where(['attention_id'=>$typeList['user_id'],'user_id'=>$user_id])->count();
            if($isAttentionStatus){
               $typeList['isAttention']=1;
            }
         }
         return $typeList;
    }

    public function isFavorMethod($discover_id)
    {
         $data['user_id']=$this->auth->id;
         $data['discover_id']=$discover_id;
         if(empty($data['discover_id'])){
          $this->error('参数错误');
         }
         $scopeSchoolId = $this->auth->id ? $this->getScopeSchoolId(0, true) : 0;
         $this->assertDiscoverAccessible($discover_id, $scopeSchoolId, true, '该条动态不存在或无权访问');
         $data['typedata']=1;
         $isFavorStatus=$this->favorModel->where($data)->count();
         return $isFavorStatus ? true : false;
    }

    public function isAttentionMethod($attention_id)
    {
         $data['user_id']=$this->auth->id;
         $data['attention_id']=$attention_id;
         $isAttentionStatus=$this->attentionsModel->where($data)->count();
         return $isAttentionStatus ? true : false;
    }

    public function publishData($data)
    {
         $data['user_id']=$this->auth->id;
         if ($this->hasDiscoverSchoolSchema()) {
            $data['school_id'] = $this->getScopeSchoolId(0, true);
         }
         if ($this->hasDiscoverModerationSchema()) {
            $data['audit_status'] = 'approved';
            $data['audit_admin_id'] = 0;
            $data['audit_time'] = time();
         }
         $newDiscover=$this->discoverModel->allowField(true)->save($data);
         return $newDiscover;
    }

    public function delData()
    {
         $data['user_id']=$this->auth->id;
         $data['id']=$this->request->request('discover_id')?$this->request->request('discover_id'):0;
         $res=$this->discoverModel->where($data)->select();
         if(count($res)==0){
          $this->error('未查询到该条动态数据，删除失败');
         }
         $data['statusdata']=2;
         $result=$this->discoverModel->update($data,true);
         if($result){
              $this->success('删除成功');
         }else{
            $this->error('删除失败，请重试');
         }
    }
   /**
     * 点赞
     */ 
  public function doLike(){
    $user_id=$this->auth->id;
    $type=$this->request->request('type')?$this->request->request('type'):0;
    $discover_id=$this->request->request('discover_id')?$this->request->request('discover_id'):0;
    $comment_id=$this->request->request('comment_id')?$this->request->request('comment_id'):0;
    if($type==0){
      $this->error('参数错误');
    }
    $where['user_id']=$user_id;
    $scopeSchoolId = $this->getScopeSchoolId(0, true);
    if($type==1){
      $discover = $this->assertDiscoverAccessible($discover_id, $scopeSchoolId, true, '该条作品不存在或无权访问');
      $discover_id = (int)$discover['id'];
      $where['discover_id']=$discover_id;
      $where['typedata']=1;
      $zan_user_id=(int)$discover['user_id'];
    }else if($type==2){
      $where['comment_id']=$comment_id;
      $where['typedata']=2;
      $commnet_user=$this->assertCommentAccessible($comment_id, $scopeSchoolId, '该条评论不存在或无权访问');
      $discover_id=(int)$commnet_user['discover_id'];
      $where['discover_id']=$discover_id;
    }
    $data=$this->favorModel->where($where)->find();
    if($data){
       $this->favorModel->where($where)->delete();
       if($this->discoverModel->where('id',$discover_id)->value('favorNum')>0){
          $this->discoverModel->where('id',$discover_id)->setDec('favorNum');
       }
       $favorCount = $this->favorModel->where($where)->count();
       $this->success('取消点赞', ['favorNum' => $favorCount]);
    }else{
       $res=$this->favorModel->save($where);
       if($res){
         if($type==1){
           $this->discoverModel->where('id',$discover_id)->setInc('favorNum');
           $logData['typedata']=1;
           $logData['user_id']=$zan_user_id;
           $logData['create_id']=$user_id;
           $logData['discover_id']=$discover_id;
           $logData['content']='赞了你的作品';
           $logData['remind']='{"discover_id":'.$discover_id.',"favor_id":'.$this->favorModel->id.',"attention_id":"","comment_id":""}';
         }
         if($type==2){
           $logData['typedata']=2;
           $logData['user_id']=$commnet_user['user_id'];
           $logData['create_id']=$user_id;
           $logData['discover_id']=$discover_id;
           $logData['content']='赞了你的评论';
           $logData['remind']='{"discover_id":'.$commnet_user['discover_id'].',"favor_id":'.$this->favorModel->id.',"attention_id":"","comment_id":'.$comment_id.'}';
         }
         $logGet=$this->logModel->where(['user_id'=>$logData['user_id'],'remind'=>$logData['remind'],'typedata'=>$logData['typedata']])->find();
         if($logGet){
           $logData['updatetime']=time();
           $this->logModel->where('id',$logGet->id)->update($logData,true);
         }else{
           $this->logModel->allowField(true)->save($logData);
         }
         $favorCount = $this->favorModel->where($where)->count();
         $this->success('赞好啦！', ['favorNum' => $favorCount]);
       }else{
         $this->error('点赞失败！');
       }
    }
  }

  public function doComment(){
    $user_id=$this->auth->id;
    $type=$this->request->request('type')?$this->request->request('type'):0;
    $discover_id=$this->request->request('discover_id')?$this->request->request('discover_id'):0;
    $content=$this->request->request('content')?$this->request->request('content'):'';
    $content=trim((string)$content);
    $comment_id=$this->request->request('comment_id')?$this->request->request('comment_id'):0;
    $parent_id=$this->request->request('parent_id')?$this->request->request('parent_id'):0;
    if($type==0 || $content===''){
      $this->error('参数错误');
    }
    $this->assertDiscoverPublishAllowed(['content' => $content]);
    $scopeSchoolId = $this->getScopeSchoolId(0, true);
    $discover = $this->assertDiscoverAccessible($discover_id, $scopeSchoolId, true, '该条动态不存在或无权访问');
    $discover_id = (int)$discover['id'];
    $where['user_id']=$user_id;
    $where['content']=$content;
    $where['discover_id']=$discover_id;
    $where['parent_id']=$parent_id;
    if($type==1){
      $comment_user_id=(int)$discover['user_id'];
    }else if($type==2){
      $where['comment_id']=$comment_id;
      $commnet_user=$this->assertCommentAccessible($comment_id, $scopeSchoolId, '该条评论不存在或无权访问');
      $discover_id=(int)$commnet_user['discover_id'];
      $where['discover_id']=$discover_id;
      $where['reply_id']=$commnet_user['user_id'];
      if (empty($where['parent_id'])) {
        $where['parent_id'] = (int)$commnet_user['parent_id'];
      }
    }
    $res=$this->commentModel->save($where);
    if($type==1){
      $wherem['parent_id']=$this->commentModel->id;
      $wherem['id']=$this->commentModel->id;
      $this->commentModel->update($wherem,true);
    }
    if($res){
      if($type==1){
        $this->discoverModel->where('id',$discover_id)->setInc('commentNum');
        $typedata = 3;
        $logUserId = $comment_user_id;
        $logCommentId = $this->commentModel->id;
        $logContent = '评论了你的作品';
      }else{
        $typedata = 5;
        $logUserId = $commnet_user['user_id'];
        $logCommentId = $comment_id;
        $logContent = '回复了你的评论';
      }
      $this->logWrite($typedata,$logUserId,$user_id,$discover_id,0,0,$logCommentId,$logContent);
      $wheres['discover_id']=$discover_id;
      if($type==2){
        $wheres['parent_id']=$where['parent_id'];
      }
      $commentCount = $this->commentModel->where($wheres)->count();
      $this->success('评论成功', ['commentNum' => $commentCount]);
    }else{
      $this->error('评论失败！');
    }
  }

  public function doAttention(){
    $user_id=$this->auth->id;
    $attention_id=$this->request->request('attention_id')?$this->request->request('attention_id'):0;
    $discover_id=$this->request->request('discover_id')?$this->request->request('discover_id'):0;
    if(!isset($attention_id) || !$attention_id){
      $this->error('参数错误');
    }
    if($user_id==$attention_id){
      $this->error('不能关注自己');
    }
    $scopeSchoolId = $this->getScopeSchoolId(0, true);
    if($discover_id){
      $discover = $this->assertDiscoverAccessible($discover_id, $scopeSchoolId, true, '该条动态不存在或无权访问');
      $discover_id = (int)$discover['id'];
    }
    $this->assertAttentionTargetAccessible($attention_id, $scopeSchoolId);
    $data=$this->attentionsModel->where(['user_id'=>$user_id,'attention_id'=>$attention_id])->find();
    if($data){
      $res=$this->attentionsModel->where('id',$data['id'])->delete();
      if($res){
        $this->success('成功取消关注');
      }else{
        $this->error('出错了，请重试');
      }
    }else{
      $map['user_id']=$user_id;
      $map['attention_id']=$attention_id;
      $map['discover_id']=$discover_id;
      $res=$this->attentionsModel->save($map);
      if($res){
        $this->logWrite(4,$attention_id,$user_id,$discover_id,$this->attentionsModel->id,0,0,'关注了你');
        $this->success('关注成功');
      }else{
        $this->error('出错了，请重试');
      }
    }
  }

  public function showCommentListsMethod($discover_id,$user_id,$page,$limit=10){
    if(!isset($page) || !isset($discover_id)){
      $this->error('参数错误');
    }
    $user_id = $this->auth->id ? $this->auth->id : (int)$user_id;
    $scopeSchoolId = $this->auth->id ? $this->getScopeSchoolId(0, true) : 0;
    $discover = $this->assertDiscoverAccessible($discover_id, $scopeSchoolId, true, '该条动态不存在或无权访问');
    $discover_id = (int)$discover['id'];
    $where['A.discover_id']=$discover_id;
    $where['A.comment_id']=0;
    $where['A.statusdata']=1;
    $dataTotal=$this->commentModel->alias('A')->join('discover B','B.id=A.discover_id')->join('user C','C.id=A.user_id')->where($where)->count();
    $data=Db::name('discover_comment')->alias('A')->join('discover B','B.id=A.discover_id')->join('user C','C.id=A.user_id')->where($where)->page($page,$limit)->order('B.commentNum desc , A.createtime desc')->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.title,B.description,B.coverimage,C.nickname,C.avatar')->select();
    foreach ($data as $key => $value) {
      $data[$key]['favorCount']=Db::name('discover_favor')->where(['comment_id'=>$value['id']])->count();
      $data[$key]['commentCount']=Db::name('discover_comment')->where(['parent_id'=>$value['id'],'id'=>array('neq',$value['id']),'user_id'=>$value['user_id']])->count();
      $data[$key]['isfavor']=0;
      if($this->is_url($value['avatar'])==0){
            $data[$key]['avatar']=letter_avatar($value['nickname']);
      }
      if($this->is_url($value['avatar'])==2){
           $data[$key]['avatar']=cdnUrl($value['avatar'],true);
      }
      if($user_id){
        $data[$key]['isfavor']=Db::name('discover_favor')->where(['discover_id'=>$value['discover_id'],'typedata'=>2,'comment_id'=>$value['id'],'user_id'=>$user_id])->count()>0?1:0;
      }
      $data[$key]['replyLists']=Db::name('discover_comment')->alias('A')->join('user B','B.id=A.reply_id')->join('user C','C.id=A.user_id')->where('A.comment_id','gt',0)->where('A.parent_id',$value['parent_id'])->order('A.createtime','desc')->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.nickname as rnickname,B.avatar as ravatar,C.nickname, C.avatar')->select();
      foreach ($data[$key]['replyLists'] as $kk => $vv) {
        if(strlen($vv['nickname'])>8){
             $data[$key]['replyLists'][$kk]['nickname'] =mb_substr($vv['nickname'], 0, 5, 'utf-8').'...';
        }
        if(strlen($vv['rnickname'])>8){
             $data[$key]['replyLists'][$kk]['rnickname'] =mb_substr($vv['rnickname'], 0, 5, 'utf-8').'...';
        }
        if($this->is_url($vv['avatar'])==0){
             $data[$key]['replyLists'][$kk]['avatar']=letter_avatar($vv['nickname']);
        }
        if($this->is_url($vv['avatar'])==2){
             $data[$key]['replyLists'][$kk]['avatar']=cdnUrl($vv['avatar'],true);
        }
      }
    }
    $resultData=['list'=>$data,'total'=>ceil($dataTotal/$limit),'count'=>$dataTotal];
    return $resultData;
  }

  public function logWrite($typedata=null,$user_id=0,$create_id=0,$discover_id=0,$attention_id=0,$favor_id=0,$comment_id=0,$content=null){
         $logData['typedata']=$typedata;
         $logData['user_id']=$user_id;
         $logData['create_id']=$create_id;
         $logData['discover_id']=$discover_id;
         $logData['content']=$content;
         $logData['remind']='{"discover_id":'.$discover_id.',"favor_id":'.$favor_id.',"attention_id":'.$attention_id.',"comment_id":'.$comment_id.'}';
         $logGet=$this->logModel->where(['user_id'=>$logData['user_id'],'remind'=>$logData['remind'],'typedata'=>$logData['typedata']])->find();
         if($logGet){
           $logData['updatetime']=time();
           $this->logModel->where('id',$logGet->id)->update($logData,true);
         }else{
           $this->logModel->allowField(true)->save($logData);
         }
         return true;
  }
/**
     * 消息列表
     * @param string $type 消息类型 0 全部消息  消息类型:1=作品被点赞,2=评论被点赞,3=作品被评论,4=被关注,5=评论被回复,6=备用
     * @param string $page 第几页，1开始 
     * @param string $limit 每一页显示多少数量
     * 
     */ 
  public function showMessageLists(){
    $user_id=$this->auth->id;
    $typedata=$this->request->request('type')?$this->request->request('type'):0;
    $typedata=intval($typedata);
    $page=$this->request->request('page');
    $limit=$this->request->request('limit')?$this->request->request('limit'):10;
    if(!isset($page)){
      $this->error('参数错误');
    }
    $where['A.user_id']=$user_id;
    if($typedata!=0){
      $where['A.typedata']=intval($typedata);
      //$where['A.create_id']=array('neq',$user_id);
    }
    //var_dump($where);exit;
    $dataTotal=$this->logModel
          ->alias('A')
          ->join('discover B','B.id=A.discover_id')
          ->join('user C','C.id=A.create_id')
          ->where($where)
          ->count();
    $data=$this->logModel
          ->alias('A')
          ->join('discover B','B.id=A.discover_id')
          ->join('user C','C.id=A.create_id')
          ->where($where)
          ->page($page,$limit)
          ->order('A.readdata ASC,A.createtime desc')
          ->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d  %H:%i") as createtime,B.title,B.description,B.coverimage,C.nickname,C.avatar')
          ->select();//,from_unixtime(A.createtime,'%Y-%m-%d %H:%i') as createtime,
          foreach ($data as $key => $value) {
              if($this->is_url($value['avatar'])==0){
                $data[$key]['avatar']=letter_avatar($value['nickname']);
              }
              if($this->is_url($value['avatar'])==2){
                   $data[$key]['avatar']=cdnUrl($value['avatar'],true);
                  }
              $data[$key]['coverimage']=$this->is_url($value['coverimage'])?$value['coverimage']:cdnUrl($value['coverimage'],true);
            }
    $this->success('获取成功',['list'=>$data,'count'=>ceil($dataTotal/$limit)]);
  }
  /**
     * 消息已读操作
     *
     * @param string $ids 消息ids，多条 1,2,3,4,5,6,7
     */ 
  public function doMessageRead(){
    $user_id=$this->auth->id;
    $ids=$this->request->request('ids');
    if(!isset($ids)){
      $this->error('参数错误');
    }
    $idArray=explode(',', $ids);
    foreach ($idArray as $key => $value) {
      $data[$key]['id']=$value;
      $data[$key]['readdata']='1';
      $data[$key]['updatetime']=time();
    }
    $logDataUpdate=$this->logModel->saveAll($data,true);
    $count=$this->logModel
              ->alias('A')
              ->join('discover B','B.id=A.discover_id')
              ->join('user C','C.id=A.create_id')
              ->where(['A.user_id'=>$user_id,'A.readdata'=>'0'])
            ->count();
     if (!empty($logDataUpdate)){
        $this->success("更新成功",$count);
        }else{
         $this->error('消息状态更新失败',$count);
        }
    
  }



  /**
     * 作品被点赞列表
     *
     * @param string $id 传作品id 则查询该作品对应的点赞，否则就是该用户的全部点赞数据
     * @param string $page 页数
     * @param string $limit 数量
     */ 
  public function doLikeLists(){
    $user_id=$this->auth->id;
    $discover_id=$this->request->request('id')?$this->request->request('id'):0;
    $page=$this->request->request('page');
    $limit=$this->request->request('limit')?$this->request->request('limit'):10;
    if(!isset($discover_id)){
      $this->error('参数错误');
    }
    //如果discover_id=0;则代表查我所有的作品的点赞情况。
    if($discover_id==0){
       $discover_ids=$this->discoverModel->where('user_id',$user_id)->select();
       $discover_ids=array_column($discover_ids, 'id');
    }else{
     $discover_ids=array($discover_id);
     $where['A.discover_id']=array('in',$discover_ids);
    }
    //var_dump($discover_ids);exit;
    
    $where['A.user_id']=$user_id;
    $favorListsCount=$this->favorModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->count();
    $favorLists=$this->favorModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->order('A.createtime','desc')
                ->page($page,$limit)
                ->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.avatar,B.nickname')
                ->select();

    if(count($favorLists)){
      foreach ($favorLists as $key => $value) {
         $favorLists[$key]['date']=date('Y-m-d',strtotime($value['createtime']));
         $favorLists[$key]['time']=date('H:i',strtotime($value['createtime']));
         if($this->is_url($value['avatar'])==0){
                $favorLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
          if($this->is_url($value['avatar'])==2){
               $favorLists[$key]['avatar']=cdnUrl($value['avatar'],true);
              }
      }
    }
    $this->success('获取成功',['list'=>$favorLists,'total'=>ceil($favorListsCount/$limit),'count'=>$favorListsCount]);

  }

/**
     * 人被关注列表
     * @param string $page 页数
     * @param string $limit 数量
     * @param string $type 1关注我的，2我关注的
     */ 
  public function doAttentionLists(){
    $user_id=$this->auth->id;
    $page=$this->request->request('page');
    $type=$this->request->request('type');
    $limit=$this->request->request('limit')?$this->request->request('limit'):10;
    if(!isset($type) || $type==''){
       $this->error('参数错误');
    }
    if($type==1){
      $where['A.attention_id']=$user_id;
      $cid='user_id';
       $attentionsListsCount=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->group($cid)
                ->where($where)
                ->count();
      $attentionsLists=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->group($cid)
                ->where($where)
                //->order('A.createtime','desc')
                ->page($page,$limit)
                ->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.avatar,B.nickname')
                ->select();
    }
    if($type==2){
      $where['A.user_id']=$user_id;
      $cid='attention_id';
       $attentionsListsCount=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.attention_id=B.id')
                ->group($cid)
                ->where($where)
                ->count();
       $attentionsLists=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.attention_id=B.id')
                ->group($cid)
                ->where($where)
                //->order('A.createtime','desc')
                ->page($page,$limit)
                ->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.avatar,B.nickname')
                ->select();
    }
    
    foreach ($attentionsLists as $key => $value) {
         if($this->is_url($value['avatar'])==0){
                  $attentionsLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
         if($this->is_url($value['avatar'])==2){
             $attentionsLists[$key]['avatar']=cdnUrl($value['avatar'],true);
            }
    }
    //var_dump($discover_ids);exit;
    //
   
    if(count($attentionsLists)){
      foreach ($attentionsLists as $key => $value) {
         $attentionsLists[$key]['date']=date('Y-m-d',strtotime($value['createtime']));
         $attentionsLists[$key]['time']=date('H:i',strtotime($value['createtime']));
      }
    }
    
    $this->success('获取成功',['list'=>$attentionsLists,'total'=>ceil($attentionsListsCount/$limit),'count'=>ceil($attentionsListsCount/$limit)]);

  }
  
/**
     * 用户的作品被评论的列表
     * @param string $id 传动态id 则查询该作品对应的评论列表，否则就是该用户的全部评论数据
     * @param string $page 页数
     * @param string $limit 数量
     */ 
   public function doCommentLists(){
    $user_id=$this->auth->id;
    $discover_id=$this->request->request('id')?$this->request->request('id'):0;
    $page=$this->request->request('page');
    $limit=$this->request->request('limit')?$this->request->request('limit'):20;
    if(!isset($discover_id)){
      $this->error('参数错误');
    }
    //如果discover_id=0;则代表查我所有的作品的点赞情况。
    if($discover_id==0){
       $discover_ids=$this->discoverModel->where('user_id',$user_id)->select();
       $discover_ids=array_column($discover_ids, 'id');
    }else{
     $discover_ids=array($discover_id);
    }
    $where['discover_id']=array('in',$discover_ids);
                          
    $commentListsCount=Db::name('discover_comment')
                ->where($where)
                ->count();
    $commentLists=Db::name('discover_comment')
                ->where($where)
                ->order('createtime','desc')
                ->page($page,$limit)
                ->select();
    $user_ids=array_column($commentLists, 'user_id');
    $reply_ids=array_column($commentLists, 'reply_id');
    //合并
    $uids=array_merge($user_ids,$reply_ids);
    //去重
    $uids=array_unique($uids);
    //var_dump($uids);exit;
    $user_infos=Db::name('user')->where('id','in',$uids)->field('id,avatar,nickname')->select();
    foreach ($commentLists as $key => $value) {
      //把所有id加上评论人的信息
      foreach ($user_infos as $kk => $vv) {
        if($value['reply_id']==$vv['id']){
          $commentLists[$key]['avatar']=$vv['avatar'];
          $commentLists[$key]['nickname']=$vv['nickname'];
          break;
        }elseif ($value['user_id']==$vv['id']) {
          $commentLists[$key]['avatar']=$vv['avatar'];
          $commentLists[$key]['nickname']=$vv['nickname'];
          break;
        }
        # code...
      }
    }
    $this->success('获取成功',['list'=>$commentLists,'total'=>ceil($commentListsCount/$limit),'count'=>$commentListsCount]);

  }
  
/**
     * 关注数量统计
     */ 
  public function doMyDataLists(){
    $user_id=$this->auth->id;
    $page=$this->request->request('page');
    $limit=$this->request->request('limit')?$this->request->request('limit'):10;
    //var_dump($discover_ids);exit;
    $where['A.user_id']=$user_id;
    $attentionListsCount=$this->attentionModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->group('user_id')
                ->where($where)
                ->count();
    $attentionLists=$this->attentionModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->group('user_id')
                ->where($where)
                ->order('A.createtime','desc')
                ->page($page,$limit)
                ->field('A.*,from_unixtime(A.createtime,"%Y-%m-%d %H:%i") as createtime,B.avatar,B.nickname')
                ->select();
    
    $this->success('获取成功',['list'=>$attentionLists,'total'=>ceil($attentionListsCount/$limit),'count'=>$attentionListsCount]);

  }   
/**
     * 我的动态列表
     */ 
  public function doMyDiscoverLists(){
    $user_id=$this->auth->id;
    $page=$this->request->request('page');
    $limit=$this->request->request('limit')?$this->request->request('limit'):10;
    //var_dump($discover_ids);exit;
    $where['A.user_id']=$user_id;
    $where['statusdata']=1;
    $discoverListsCount=$this->discoverModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->count();
    $discoverLists=$this->discoverModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->order('A.createtime','desc')
                ->page($page,$limit)
                ->field('A.*,B.avatar,B.nickname')
                ->select();
       // var_dump($discoverListsCount);exit;
       foreach ($discoverLists as $key => $value) {
          if($this->is_url($value['avatar'])==0){
                  $discoverLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
          if($this->is_url($value['avatar'])==2){
             $discoverLists[$key]['avatar']=cdnUrl($value['avatar'],true);
            }
          $discoverLists[$key]['coverimage']=cdnUrl($value['coverimage'],true);
       }
      if($discoverListsCount>1){
        $numSet=count($discoverLists);
        //时间倒序排列的
        if($numSet>0){
           $beginDate=date('Y-m-d',$discoverLists[$numSet-1]['createtime']); 
           $endDate=date('Y-m-d',$discoverLists[0]['createtime']);
           //计算相差天数
            $timediff =$this->timediff(strtotime($beginDate), strtotime($endDate));
            $timediff=$timediff['day']+1;//上面计算的时候把时间去掉了，实际要加一天
        }else{
            //计算相差天数
            $timediff =0;
        }
        //找出开始和结束时间，计算有多少天，开始循环
        $outData=[];
        for($i=0;$i<$timediff;$i++){
            $outData[$i]['monthDate']=date('Y-m-d',$discoverLists[0]['createtime']-$i*24*60*60);//5.31
            $outData[$i]['istoday']=0;
            if(strtotime($outData[$i]['monthDate'])==strtotime(date('Y-m-d',time()))){
                $outData[$i]['istoday']=1;
            }
            $beginTimeSet=date('Y-m-d 00:00:00',$discoverLists[0]['createtime']-$i*24*60*60);
            $endTimeSet=date('Y-m-d 23:59:59',$discoverLists[0]['createtime']-$i*24*60*60);
            $outData[$i]['list']=[];
             foreach ($discoverLists as $key => $value) { 
                if($value['createtime']<strtotime($endTimeSet) && $value['createtime']>strtotime($beginTimeSet)){
                  $discoverLists[$key]['time']=date('H:i',$value['createtime']);
                  $outData[$i]['list'][]=$discoverLists[$key];
                }
            }
            if(count($outData[$i]['list'])==0){
                unset($outData[$i]);
            }
        }
       $outData=array_values($outData);
      }else if($discoverListsCount==1){
          $outData[0]['monthDate']=date('Y-m-d',$discoverLists[0]['createtime']);
           $outData[0]['istoday']=0;
          if(strtotime($outData[0]['monthDate'])==strtotime(date('Y-m-d',time()))){
                $outData[0]['istoday']=1;
            }
          $outData[0]['list']=$discoverLists;
      }else{
        $outData=[];
      }
      
    
    $this->success('获取成功',['list'=>$outData,'total'=>ceil($discoverListsCount/$limit),'count'=>$discoverListsCount]);

  }  

function timediff( $begin_time, $end_time )
{
  if ( $begin_time < $end_time ) {
    $starttime = $begin_time;
    $endtime = $end_time;
  } else {
    $starttime = $end_time;
    $endtime = $begin_time;
  }
  $timediff = $endtime - $starttime;
  $days = intval( $timediff / 86400 );
  $remain = $timediff % 86400;
  $hours = intval( $remain / 3600 );
  $remain = $remain % 3600;
  $mins = intval( $remain / 60 );
  $secs = $remain % 60;
  $res = array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
  return $res;
}
  /**
     * 个人中心展示数量统计
     */ 
  public function userDataLists(){
    $user_id=$this->auth->id;
    //var_dump($discover_ids);exit;
    $where['A.user_id']=$user_id;
    //喜欢点赞数量统计
    $favorListsCount=$this->favorModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->count();
    $favorLists=$this->favorModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($where)
                ->group('user_id')
                //->order('A.id','desc')
                ->page(1,6)
                ->field('B.avatar,B.nickname')
                ->select();

     //我关注的数量统计
    $attentionListsCount=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.attention_id=B.id')
                ->where($where)
                ->group('attention_id')
                ->count();
    $attentionLists=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.attention_id=B.id')
                ->where($where)
                ->group('attention_id')
                //->order('A.id','desc')
                ->page(1,6)
                ->field('B.avatar,B.nickname')
                ->select();
    //被关注的数量统计
    $wheres['A.attention_id']=$user_id;
    $beAttentionListsCount=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($wheres)
                ->group('user_id')
                ->count();
    $beAttentionLists=$this->attentionsModel
                ->alias('A')
                ->join('user B','A.user_id=B.id')
                ->where($wheres)
                ->group('user_id')
                //->order('A.id','desc')
                ->page(1,6)
                ->field('B.avatar,B.nickname')
                ->select();
     foreach ($beAttentionLists as $key => $value) {
         if($this->is_url($value['avatar'])==0){
             $beAttentionLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
          if($this->is_url($value['avatar'])==2){
             $beAttentionLists[$key]['avatar']=cdnUrl($value['avatar'],true);
            }
     }
     foreach ($attentionLists as $key => $value) {
         if($this->is_url($value['avatar'])==0){
             $attentionLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
          if($this->is_url($value['avatar'])==2){
             $attentionLists[$key]['avatar']=cdnUrl($value['avatar'],true);
            }
     }
      foreach ($favorLists as $key => $value) {
          if($this->is_url($value['avatar'])==0){
             $favorLists[$key]['avatar']=letter_avatar($value['nickname']);
              }
          if($this->is_url($value['avatar'])==2){
             $favorLists[$key]['avatar']=cdnUrl($value['avatar'],true);
            }
     }
    //我点赞的
    $favorTotalCount=$this->favorModel
                ->where('user_id',$user_id)
                ->count();
    
    $this->success('获取成功',['favorLists'=>$favorLists,'attentionLists'=>$attentionLists,'beAttentionLists'=>$beAttentionLists,'favorListsCount'=>$favorListsCount,'attentionListsCount'=>$attentionListsCount,'beAttentionListsCount'=>$beAttentionListsCount,'favorTotalCount'=>$favorTotalCount]);

  } 
/**
把用户输入的文本转义（主要针对特殊符号和emoji表情）
*/
public function userTextEncode($str){
  if(!is_string($str)) return $str;
  if(!$str || $str='undefined') return '';
  $text=json_encode($str);//暴露出unicode
  $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
    var_dump($str);exit;
    return addslashes($str[0]);
  },$text);

  //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
  return json_decode($text);
}
/**
解码上面的转义
*/
public function userTextDecode($str){
  $text=json_decode($str);
  $text=preg_replace_callback('/\\\\\\\\/i',function($str){
    return '\\';
  },$text);
  return json_decode($text);
}
 

    /**
     * 腾讯地图crul
     * 
     * 
     */
    public function http_curl($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        curl_close($ch);
        return $output;
    }
    
    
//主动判断是否HTTPS
  function isHTTPS()
  {
      if (defined('HTTPS') && HTTPS) return true;
      if (!isset($_SERVER)) return FALSE;
      if (!isset($_SERVER['HTTPS'])) return FALSE;
      if ($_SERVER['HTTPS'] === 1) {  //Apache
          return TRUE;
      } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
          return TRUE;
      } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
          return TRUE;
      }
      return FALSE;
    }
/**
    *判断是不是网址
     */
 public function is_url($v){
    $pattern="#(http|https)://(.*\.)?.*\..*#i";
    //排除base64头像
    if (strpos($v, 'data:image') === 0) {
      return 1;//无需替换
     }
     if ($v=='') {
      return 0;//空头像，需要替换字母
     }
    if(preg_match($pattern,$v)){ 
      return 1;//网址形式 
    }else{ 
      return 2;//添加域名前缀  0替换字母，1无需变动，2加域名前缀 
    } 
}
/**
 *输出省市区按照ABC排序json
 * 
 *
 */
public function areaOrderABCJson(){
  $province=Db::name('area')->where('pid',0)->select();
  $area=[];
  foreach ($province as $key => $value) {
    $area[$key]['code']=$value['zip'];
    $area[$key]['name']=$value['name'];
    //循环市
    $city=Db::name('area')->where('pid',$value['id'])->select();
    foreach ($city as $k => $v) {
      $area[$key]['code']=$city[0]['zip'];
      $area[$key]['first']=$city[0]['first'];
      $area[$key]['cityList'][$k]['code']=$v['zip'];
      $area[$key]['cityList'][$k]['name']=$v['name'];
      $arealist=Db::name('area')->where('pid',$v['id'])->select();
      foreach ($arealist as $kk => $vv) {
        $area[$key]['cityList'][$k]['areaList'][$kk]['code']=$vv['zip'];
        $area[$key]['cityList'][$k]['areaList'][$kk]['name']=$vv['name'];
      }
    }
  }
}

/**
 *输出省市区json
 * 
 *
 */
public function areaJson(){
  $province=Db::name('area')->where('pid',0)->select();
  $area=[];
  foreach ($province as $key => $value) {
    $area[$key]['code']=$value['zip'];
    $area[$key]['name']=$value['name'];
    //循环市
    $city=Db::name('area')->where('pid',$value['id'])->select();
    foreach ($city as $k => $v) {
      $area[$key]['code']=$city[0]['zip'];
      $area[$key]['first']=$city[0]['first'];
      $area[$key]['cityList'][$k]['code']=$v['zip'];
      $area[$key]['cityList'][$k]['name']=$v['name'];
      $arealist=Db::name('area')->where('pid',$v['id'])->select();
      foreach ($arealist as $kk => $vv) {
        $area[$key]['cityList'][$k]['areaList'][$kk]['code']=$vv['zip'];
        $area[$key]['cityList'][$k]['areaList'][$kk]['name']=$vv['name'];
      }
    }
  }
  $this->success('返回成功', $area);
}
  
   /*
     *  @param $saveWhere ：想要更新主键ID数组
     *  @param $saveData    ：想要更新的ID数组所对应的数据
     *  @param $tableName  : 想要更新的表明
     *  @param $saveWhere  : 返回更新成功后的主键ID数组
     * */
    public function saveAll($saveWhere,$saveData,$tableName){
        if($saveWhere==null||$tableName==null)
            return false;
        //获取更新的主键id名称
        $key = array_keys($saveWhere)[0];
        //获取更新列表的长度
        $len = count($saveWhere[$key]);
        $flag=true;
        // isset($model)?$model:
        $model =Db::name($tableName);
        //开启事务处理机制
        $model->startTrans();
        //记录更新失败ID
        $error=[];
        for($i=0;$i<$len;$i++){
            //预处理sql语句
            $isRight=$model->where($key.'='.$saveWhere[$key][$i])->update($saveData[$i]);
            if($isRight==0){
                //将更新失败的记录下来
                $error[]=$i;
                $flag=false;
            }
            //$flag=$flag&&$isRight;
        }
        if($flag ){
            //如果都成立就提交
            $model->commit();
            return $saveWhere;
        }elseif(count($error)>0&count($error)<$len){
            //先将原先的预处理进行回滚
            $model->rollback();
            for($i=0;$i<count($error);$i++){
                //删除更新失败的ID和Data
                unset($saveWhere[$key][$error[$i]]);
                unset($saveData[$error[$i]]);
            }
            //重新将数组下标进行排序
            $saveWhere[$key]=array_merge($saveWhere[$key]);
            $saveData=array_merge($saveData);
            //进行第二次递归更新
            $this->saveAll($saveWhere,$saveData,$tableName);
            return $saveWhere;
        }
        else{
            //如果都更新就回滚
            $model->rollback();
            return false;
        }
    }
}
