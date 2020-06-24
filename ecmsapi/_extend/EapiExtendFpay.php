<?php 
class EapiExtendFpay
{
    protected $api = null;
    protected $error = null;
    protected $classCache = [];

    public function __construct($conf = [] , $api = null)
    {
        $this->api = $api;
    }
    
    // 加载支付平台模块
    
    public function load($name = '' , $conf = [] , $cache = true)
    {
        $className = 'Fpay'.ucfirst($name);
        if(!class_exists($className)){
            require(ECMS_PATH . '/ecmsapi/_extend/fpay/'.$className.'.php');
        }
        if(false === $cache){
            return new $className($conf);
        }else{
            if(!isset($this->classCache[$name])){
                $this->classCache[$name] = new $className($conf);
            }
            return $this->classCache[$name];
        }
    }
    
    // 生成订单号
    public function buildOrderid()
    {
        list($i , $t) = explode(' ' ,microtime());
        return $t.substr($i , 2 , 6);
    }
    
    // 创建订单
    public function createOrder($conf = [])
    {
        $data = [
            'orderid' => isset($conf['orderid']) ? $conf['orderid'] : $this->buildOrderid(),
            'price' => (int)$conf['price'],
            'status' => 0,
            'payid' => $conf['payid'],
            'ctime' => time(),
            'description' => isset($conf['description']) ? $conf['description'] : '',
            'uid' => $conf['uid'],
            'ip' => egetip(),
            'tid' => isset($conf['tid']) ? (int)$conf['tid'] : 0,
            'type' => isset($conf['type']) ? (int)$conf['type'] : 0
        ];

        $id = $this->api->load('db')->insert('[!db.pre!]fpay_order' , $data);
        if(false !== $id){
            return $data;
        }else{
            $this->error = '订单创建失败';
            return false;
        }
    }
    
    // 获取一个订单
    public function getOrder($orderid = '')
    {
        $orderid = (int)$orderid;
        return $this->api->load('db')->one('[!db.pre!]fpay_order' , '*' , 'orderid = ' . $orderid);
    }
    
    // 完成一个订单
    public function completeOrder($orderid = '')
    {
        $order = $this->getOrder($orderid);
        if(empty($order)){
            $this->error = '无效订单';
            return false;
        }
        if((int)$order['status'] === 1){
            return true;
        }
        $num = $this->api->load('db')->total('[!db.pre!]enewsmember' , 'userid='.$order['uid']);
        if($num === 0){
            $this->error = '此订单用户已被删除';
            return false;
        }
        $type = (int)$order['type'];
        if($type === 0){
            return $this->complete_fen_order($order);
        }else if($type === 1){
            return $this->complete_money_order($order);
        }else if($type === 2){
            return $this->complete_buygroup_order($order);
        }else if($type === 3){
            return $this->complete_shop_order($order);
        }else if($type === 4){
            return $this->complete_other_order($order);
        }else{
            $this->error = '订单类型错误';
            return false;
        }
    }
    
    // 完成一个积分订单
    protected function complete_fen_order($order)
    {
        $db = $this->api->load('db');
        $v = $this->api->load('db')->one('[!db.pre!]enewspublic' , 'paymoneytofen,payminmoney' , '1=1');
        $fen = intval($order['price']*$v['paymoneytofen']/$v['payminmoney']);

        $result = $db->update('[!db.pre!]enewsmember' , [
            'userfen' => ['userfen+'.$fen]
        ] , 'userid='.$order['uid']);
        if(false === $result){
            $this->error = '订单处理失败';
            return false;
        }
        $this->set_order_status($order['orderid'] , 1);
        return true;
    }
    
    // 完成一个现金订单
    protected function complete_money_order($order)
    {
        $result = $this->api->load('db')->update('[!db.pre!]enewsmember' , [
            'money' => ['money+'.$order['price']]
        ] , 'userid='.$order['uid']);
        if(false === $result){
            $this->error = '订单处理失败';
            return false;
        }
        $this->set_order_status($order['orderid'] , 1);
        return true;
    }
    
    // 完成一个充值类型订单
    protected function complete_buygroup_order($order)
    {
        global $public_r;
        $id = $order['tid'];
        $db = $this->api->load('db');
        $ka = $db->one('[!db.pre!]enewsbuygroup' , '*' , 'id='.$id);
        if(empty($ka)){
            $this->error = '充值类型已下架';
            return false;
        }
        $user = $db->one('[!db.pre!]enewsmember' , 'userdate,userid,username,groupid' , 'userid='.$order['uid']);
        if(!$user){
            $this->error = '该充值用户未找到';
            return false;
        }
        $up = [];
        if($level_r[$ka['buygroupid']]['level'] > $level_r[$user['groupid']]['level'] ){
            $this->error = '当前用户所有组不允许购买此充值类型';
            return false;
        }
        if($ka['gfen'] > 0){
            $up['userfen'] = ['userfen+'.$ka['gfen']];
        }
        
        // 存在时间购买
        if($ka['gdate'] > 0){
            
            $date = $user['userdate'];
            // 当前会组
            if((int)$user['groupid'] !== (int)$ka['ggroupid']){
                // 当存在会员组更变时,且时间未到期时
                if($date && $date > time()){
                    $dateType = (int)$public_r['mhavedatedo']; //时间处理方式 1覆盖，2叠加，其它不允许
                    if($dateType === 1){
                        // 覆盖时间,将原时间清0
                        $date = 0;
                    }else if($dateType === 2){
                        // 叠加时间，不需处理
                        
                    }else{
                        $this->error = '已有会员组';
                        return false;
                    }
                }
            }
            $up['userdate'] = $date < time() ? time() + $ka['gdate']*24*3600 : $date + $ka['gdate']*24*3600;
            
            if($ka['ggroupid'] > 0){
                $up['groupid'] = $ka['ggroupid'];
            }
            if($ka['zgroupid'] > 0){
                $up['zgroupid'] = $ka['zgroupid'];
            }
        }
        
        $result = $this->api->load('db')->update('[!db.pre!]enewsmember' , $up , 'userid='.$order['uid']);
        if(false === $result){
            $this->error = '订单处理失败';
            return false;
        }
        $this->set_order_status($order['orderid'] , 1);
        return true;
    }
    
    // 完成一个商城订单
    protected function complete_shop_order($order)
    {
        $id = $order['tid']; //商城订单id
        $db = $this->api->load('db');
        
        // 获取商城订单
        $dd = $db->one('[!db.pre!]enewsshopdd' , 'ddid,ddno,userid,username,truename,pstotal,alltotal,fptotal,pretotal,fp,payby,havecutnum' , 'ddid='.$id);
        
        if(!$dd){
            $this->error = '订单已失效或被删除';
            return false;
        }
        
        if((int)$dd['payby'] !== 0){
            $this->error = '此订单为非现金支付';
            return false;
        }
        
        $dd['tmoney'] = $dd['alltotal']+$dd['pstotal']+$dd['fptotal']-$dd['pretotal'];
        
        // 更新商城订单状态
        $result = $db->update('[!db.pre!]enewsshopdd' , ['haveprice' => 1] , 'ddid='.$id);
        
        if($result === false){
            $this->error = '订单处理失败';
            return false;
        }
        
        // 获取商城配置
        $conf = $db->one('[!db.pre!]enewsshop_set' , '*' , '1=1');
        
        // 更新库存
        if( (int)$conf['cutnumtype'] === 1 ){
            $dd_add = $db->one('[!db.pre!]enewsshopdd_add' , '*' , 'ddid='.$id);
            $this->ShopsysCutMaxnum($id , $dd_add['buycar'] , $dd['havecutnum'] , $conf , 0);
        }
        
        $this->set_order_status($order['orderid'] , 1);
        
        return true;
    }
    
    // 完成一个其它订单,tid表示要充值的积分
    protected function complete_other_order($order)
    {
        $result = $this->api->load('db')->update('[!db.pre!]enewsmember' , [
            'userfen' => ['userfen+'.$order['tid']]
        ] , 'userid='.$order['uid']);
        if(false === $result){
            $this->error = '订单处理失败';
            return false;
        }
        $this->set_order_status($order['orderid'] , 1);
        return true;
    }
    
    // 设置订单状态
    protected function set_order_status($orderid = 0 , $status = 1)
    {
        return $this->api->load('db')->update('[!db.pre!]fpay_order' , ['status' => $status , 'ptime' => time()] , 'orderid = '.$orderid);
    }
    
    // 将帝国默认的订单类型转换成数字
    public function getOrderType($name = '')
    {
        $name = $name === '' ? getcvar('payphome') : $name;
        $name = strtolower($name);
        
        $types = [
            'paytofen' => 0,
            'paytomoney' => 1,
            'buygrouppay' => 2,
            'shoppay' => 3,
        ];
        
        return isset($types[$name]) ? $types[$name] : null;
        
    }
    
    public function getOrderList($data = [] , $pagination = '20,1' , $orderby = 'orderid desc')
    {
        $map = '';
        $uid = isset($data['uid']) ? (int)$data['uid'] : 0;
        if($uid > 0){
            $map .= ' and uid = '.$uid;
        }
        $status = isset($data['status']) ? trim($data['status']) : '';
        if($status !== ''){
            $map .= ' and status = '.($status ? 1 : 0);
        }
        $orderid = isset($data['orderid']) ? (int)$data['orderid'] : 0;
        if($orderid > 0){
            $map .= ' and orderid = '.$orderid;
        }
        $startTime = isset($data['starttime']) ? trim($data['starttime']) : '';
        if($startTime !== '' && strtotime($startTime) !== false){
            $map .= ' and ctime > '.strtotime($startTime);
        }
        $endTime = isset($data['endtime']) ? trim($data['endtime']) : '';
        if($endTime !== '' && strtotime($endTime) !== false){
            $map .= ' and ctime < '.strtotime($endTime);
        }
        $payid = isset($data['payid']) ? (int)$data['payid'] : 0;
        if($payid > 0){
            $map .= ' and payid = '.$payid;
        }
        list($limit , $page) = explode(',' , $pagination.',1,1');
        $page = (int)$page;
        $limit = (int)$limit;
        $limit = $limit > 0 ? $limit : 20;
        $limit = $limit <= 1000 ? $limit : 1000;
        
        
        
        $map = $map !== '' ? substr($map , 4) : '1=1';
        
        list($sortfield , $sorttype) = explode(' ' , $orderby.' orderid desc');

        $sortfield = in_array(strtolower($sortfield) , ['orderid' , 'ctime' , 'ptime' , 'uid' , 'price' , 'status']) ? strtolower($sortfield) : 'orderid';
        $sorttype = strtolower($sorttype) === 'asc' ? 'asc' : 'desc';
        
        
        
        
        $total = $this->api->load('db')->total('[!db.pre!]fpay_order' , $map);
        
        if($total > 0){
            $page_total = ceil($total / $limit);
            $list = $this->api->load('db')->select('[!db.pre!]fpay_order' , '*' , $map , $limit.','.$page , $sortfield.' '.$sorttype);
            $result = [
                'total' => $total,
                'page' => $page,
                'page_total' => (int)$page_total,
                'limit' => $limit,
                'list' => $list
            ];
        }else{
            $result = [
                'total' => 0,
                'page' => 1,
                'page_total' => 1,
                'limit' => $limit,
                'list' => []
            ];
        }
        return $result;

    }
    
    
    public function getError()
    {
        return $this->error;
    }
    
    /* 偷个懒 照抄商城订单库存处理函数 */
    protected function ShopsysCutMaxnum($ddid,$buycar,$havecut,$shoppr,$ecms=0){
        global $class_r,$empire,$dbtbpre,$public_r;
        $ddid=(int)$ddid;
        if(empty($buycar))
        {
            return '';
        }
        if($ecms==0&&$havecut)
        {
            return '';
        }
        if($ecms==1&&!$havecut)
        {
            return '';
        }
        if($ecms==0)
        {
            $fh='-';
            $salefh='+';
        }
        else
        {
            $fh='+';
            $salefh='-';
        }
        $record="!";
        $field="|";
        $buycarr=explode($record,$buycar);
        $bcount=count($buycarr);
        for($i=0;$i<$bcount-1;$i++)
        {
            $pr=explode($field,$buycarr[$i]);
            $productid=$pr[1];
            $fr=explode(",",$pr[1]);
            //ID
            $classid=(int)$fr[0];
            $id=(int)$fr[1];
            //数量
            $pnum=(int)$pr[3];
            if($pnum<1)
            {
                $pnum=1;
            }
            if(empty($class_r[$classid][tbname]))
            {
                continue;
            }
            $empire->query("update {$dbtbpre}ecms_".$class_r[$classid][tbname]." set pmaxnum=pmaxnum".$fh.$pnum.",psalenum=psalenum".$salefh.$pnum." where id='$id'");
        }
        $newhavecut=$ecms==0?1:0;
        $empire->query("update {$dbtbpre}enewsshopdd set havecutnum='$newhavecut' where ddid='$ddid'");
    }
    
}