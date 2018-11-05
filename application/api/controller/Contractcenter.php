<?php
/**
 * IndexController.class.php
 * 分销商首页
 * @author
 * @date 2016-01-14 16:52
 */

namespace app\api\controller;
use app\api\model\ContractTemplate;
use app\api\model\Contract;
use app\api\model\AppidRelation;
use app\api\controller\Contract as ContractController;

class Contractcenter extends ContractController{

	/**
	 * @author 
	 * @name   合同列表
	 * @
	 * @
	 * @
	 */
	function lists(){

		//$where['c_uid_id'] = session('agency_id');
		//$where['is_type'] = 0;
		
		if($_GET['keytype']||$_GET['contract_status']||$_GET['c_status']){
			$this->assign('active',0);
		}else{
			$this->assign('active',1);
		}
		
		if($_GET['keytype']==1){
			$where['line_name'] = array('like','%'.$_GET['word'].'%');
			$this->assign('keyword','线路名称');
			$this->assign('keytype',1);		
		}else if($_GET['keytype']==2){
			$where['onumber'] = array('like','%'.$_GET['word'].'%');
			$this->assign('keyword','订单编号');
			$this->assign('keytype',2);
		}else if($_GET['keytype']==3){
			$where['supply_name'] = array('like','%'.$_GET['word'].'%');
			$this->assign('keyword','供应商');
			$this->assign('keytype',3);
		}else if($_GET['keytype']==4){
			$where['title'] = array('like','%'.$_GET['word'].'%');
			$this->assign('keyword','合同名称');
			$this->assign('keytype',4);
		}else if($_GET['keytype']==5){
			$where['cp_number'] = array('like','%'.$_GET['word'].'%');
			$this->assign('keyword','合同编码');
			$this->assign('keytype',5);
		}else{
			
			$this->assign('keyword','线路名称');
			$this->assign('keytype',1);
		}
		
		$this->assign('word',$_GET['word']);
		if($_GET['contract_status']==1){   //1待创建 2待盖章 3待签约 4已撤销 5待填写
			$where['contract_status'] = 1;
			$where['c_status'] = 2;			
			$this->assign('key2','待创建');
			$_GET['c_status']=3;
		}else if($_GET['contract_status']==2){
			$where['contract_status'] = 3; 
			$this->assign('key2','待盖章');
			$_GET['c_status']=3;
		}else if($_GET['contract_status']==3){
			$where['contract_status'] = 4; 
			$this->assign('key2','待签约');
			$_GET['c_status']=3;
		}else if($_GET['contract_status']==4){
			$where['contract_status'] = 0; 
			$this->assign('key2','已作废');
			$_GET['c_status']=3;
		}else if($_GET['contract_status']==5){
			$where['contract_status'] = 2;
			$where['c_status'] = array('in','0,1,3');			
			$this->assign('key2','待填写');			
		}
		
		if($_GET['c_status']==1){   //1未提交 2待审核 3已通过 4未通过
			$where['c_status'] = 0; //0未提交 1待审核 2已通过 3未通过
			$this->assign('key1','未提交');			
		}else if($_GET['c_status']==2){
			$where['c_status'] = 1; 
			$this->assign('key1','待审核');	
		}else if($_GET['c_status']==3){
			$where['c_status'] = 2; 
			$this->assign('key1','已通过');	
		}else if($_GET['c_status']==4){
			$where['c_status'] = 3; 
			$this->assign('key1','未通过');				
		}
        $lists = Contract::where($where)->paginate(3);
        // 获取分页显示
        $page = $lists->render();

		//$this->assign('c_status',$_GET['c_status']);
		//$this->assign('contract_status',$_GET['contract_status']);
        $this->assign('page',$page);
		$this->assign('data',$lists);
		return $this->fetch();
	}

    //自由合同列表
    function freecont(){
        //$where['is_type'] = 1;

        if($_GET['keytype']||$_GET['contract_status']||$_GET['c_status']){
            $this->assign('active',0);
        }else{
            $this->assign('active',1);
        }

        if($_GET['keytype']==1){
            $where['title'] = array('like','%'.$_GET['word'].'%');
            $this->assign('keyword','合同名称');
            $this->assign('keytype',1);
        }else if($_GET['keytype']==2){
            $where['c_number'] = array('like','%'.$_GET['word'].'%');
            $this->assign('keyword','旅行社编号');
            $this->assign('keytype',2);
        }else if($_GET['keytype']==3){
            $where['cp_number'] = array('like','%'.$_GET['word'].'%');
            $this->assign('keyword','合同编码');
            $this->assign('keytype',2);
        }else{

            $this->assign('keyword','合同名称');
            $this->assign('keytype',1);
        }

        $this->assign('word',$_GET['word']);

        if($_GET['contract_status']==1){   //1待创建 2待盖章 3待签约 4已撤销
            $where['contract_status'] = 1;
            $where['c_status'] = 2;
            $this->assign('key2','待创建');
            $_GET['c_status']=1;
        }else if($_GET['contract_status']==2){
            $where['contract_status'] = 3;
            $this->assign('key2','待盖章');
            $_GET['c_status']=3;
        }else if($_GET['contract_status']==3){
            $where['contract_status'] = 4;
            $this->assign('key2','待签约');
            $_GET['c_status']=3;
        }else if($_GET['contract_status']==4){
            $where['contract_status'] = 0;
            $this->assign('key2','已作废');
            $_GET['c_status']=3;
        }
        if($_GET['c_status']==1){   //1未提交 2待审核 3已通过 4未通过
            $where['c_status'] = 0; //0未提交 1待审核 2已通过 3未通过
            $this->assign('key1','未提交');
        }else if($_GET['c_status']==2){
            $where['c_status'] = 1;
            $this->assign('key1','待审核');
        }else if($_GET['c_status']==3){
            $where['c_status'] = 2;
            $this->assign('key1','已通过');
        }else if($_GET['c_status']==4){
            $where['c_status'] = 3;
            $this->assign('key1','未通过');
        }
        $lists = Contract::where($where)->paginate(3);
        // 获取分页显示
        $page = $lists->render();
        //$this->assign('c_status',$_GET['c_status']);
        //$this->assign('contract_status',$_GET['contract_status']);
        $this->assign('page',$page);
        $this->assign('data',$lists);
        return $this->fetch();
    }
	/**
	 * @author
	 * 提交审核
	 * @$_GET['c_number'] 旅行社合同编号
	 */
	public function sendCheck(){
		
	}

	/**
	 * @author
	 * 作废合同
	 * @$_GET['c_number'] 旅行社合同编号
	 */
	public function del(){
		
	}	
	/**
	 * @author
	 * 添加备注
	 * @$_GET['c_number'] 旅行社合同编号
	 */
	public function addremark(){
		
	
	}	
	/**
	 * @author
	 * 创建合同
	 * @$_POST['c_number']
	 * @$_POST['type']
	 */
	public function sendCreat(){
		
	}
	
	//填写合同
	function save($appid){
        if(empty($appid)){
            $unit = AppidRelation::where('appid',$_GET['appid'])->find();
        }else{
            $unit = AppidRelation::where('appid',$appid)->find();
        }
        $templets = ContractTemplate::where('uit_id',$unit->unit_id)->select()->toArray();
		$isOrderNew = 0;//1表示订单合同创建草稿

		if($_GET['cmode']){//判定是否只是查看合同不进行修改
			$this->assign('cmode',$_GET['cmode']);
		}
        $this->assign('cnumber',0);
		$this->assign('type',1);
		$this->assign('isNew',0);			
		$this->assign('template',$templets);
		return $this->fetch();		
				
	}
    //编辑合同
    function edit(){

        $unit = AppidRelation::where('appid',$_GET['appid'])->find();
        $templets = ContractTemplate::where('uit_id',$unit->unit_id)->select()->toArray();
        $isOrderNew = 0;//1表示订单合同创建草稿
        $result = array();
        if($result['orderInfo']&&$_GET['onumber']){//订单合同
            $isOrderNew = 1;
            $this->assign('templateId',$result['templateId']);
            $this->assign('orderInfo',$result['orderInfo']);
        }
        if($_GET['c_number']){  //编辑读取合同信息
            $isOrderNew = 0;
            $this->assign('mode',1);//是否显示编辑按钮
            $this->assign('cdata',$result['cdata']);
        }else{
            $isOrderNew = 1;
        }
        if($_GET['cmode']){//判定是否只是查看合同不进行修改
            $this->assign('cmode',$_GET['cmode']);
        }
        $this->assign('type',1);
        $this->assign('isNew',0);
        $this->assign('template',$templets);
        $this->assign('cnumber',$result['cnumber']);

        return $this->fetch();

    }

	//添加草稿
	function add(){

        $Pram['contract_status']     = 1;
        $priceLoops			= $_POST['priceLoops'];
        $Pram['all_price']  = $_POST["$priceLoops"]; //需要移除
        $Pram['c_number']   = $_POST['cnumber']; 	//需要移除
        $Pram['c_number']   = '111111233333'; 	//需要移除
        $Pram['title']      = $_POST['title'];  	//需要移除
        $Pram['times']      = time();
        if($_POST['type']){//url 跳转  1自由合同
            $url = url('api/contractcenter/lists', 'uid=5');
        }else{
            $url = url('api/contractcenter/lists', 'uid=5');
        }
        unset($_POST['title']);unset($_POST['cnumber']);unset($_POST['templateId']);unset($_POST['type']);unset($_POST['priceLoops']);
        $Pram['info']     	= json_encode($_POST);
        //var_dump($Pram);die;

        $contract = new Contract;
        if($contract->save($Pram)){
            $data['type'] = 1 ;
            $data['msg'] = "更新成功";
            $data['url'] = $url;
        }else{
            $data['type'] = 0 ;
            $data['msg'] = "更新失败";
        }

        return $data;
    }

	//编辑更新
	function update(){


	}

	//显示合同模板
	function show($id, $cnumber, $isNew, $type){
/* 		$this->assign('cnumber',$_GET['cnumber']);
		$map['c_number'] = $_GET['cnumber'];

		$cdata = M('contract')->where($map)->find();
		$info = json_decode($cdata['info'],true); 
		$this->assign('info',$info);
		if($cdata){
			if($cdata['contract_status']==2){
				$this->assign('show_bool',0);	
			}else{
				$this->assign('show_bool',1);
			}
		}else{
			$this->assign('show_bool',0);
		}

		$this->display($_GET['id']); */
        if($id){
            return $this->fetch($id);
        }
        return $this->fetch($_GET['id']);
	
	}
	//手机查看二维码
	function ajaxReturnWapImg(){
		
		$data['pic'] ='http://qr.liantu.com/api.php?el=l&w=200&m=10&text=http://'.$_SERVER['HTTP_HOST'].'/distributor/Soap/wapshow/orderId/'.$_POST['c_number'];
		$data['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/distributor/Soap/wapshow/orderId/'.$_POST['c_number'];
		$this->ajaxReturn($data);

	}		
	//手机签约二维码
	function ajaxReturnImg(){
		
		$data['pic'] ='http://qr.liantu.com/api.php?el=l&w=200&m=10&text=http://'.$_SERVER['HTTP_HOST'].'/distributor/Soap/sigepageWap/orderId/'.$_POST['c_number'];
		$data['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/distributor/Soap/sigepageWap/orderId/'.$_POST['c_number'];
		$this->ajaxReturn($data);

	}	
}