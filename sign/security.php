<?php  
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    /**
    * 
    */
    class security extends IController
    {
       
        public function login_o()
        {
            $this->redirect('login');
        }
        //获取ip
        public function staffs()
        {
            $this->redirect('sign');
        }
        public function userinfo()
        {
            $uname=IReq::get('uname');
            $pwd=IReq::get('pwd');
            $user=new IModel('staff');
            $where="nickname='$uname' and staffcode='$pwd'";
            $userinfo=$user->getObj($where);
            if (isset($userinfo)&&$uname=='han')
             {
                $session=new ISession;
                $session->set('adminname',$uname);
                $this->info();
            }
            else
            {
                // $this->userinfo=$userinfo;
                 $this->setRenderData(['data'=>$userinfo]);
                $this->redirect('userinfo');
            }
        }
        //管理员展示数据
        public function info()
        {
            $search_da=IReq::get('search_da');
            $redis=new Redis;
            $redis->connect('127.0.0.1',6379);
            if ($search_da!='')
             {

                if ($redis->exists($search_da)) 
                {
                    $data=$redis->get($search_da);
                    $data=json_decode($data,true);
                }
                else
                {
                    $user=new IModel('staff');
                    $where="nickname='$search_da'";
                    $data=$user->getObj($where);
                    $redis->set($search_da,json_encode($data));
                }
                 $this->num=1;
            }
            else
            {
                $admin=new IQuery('staff');
                $data=$admin->find();
                $this->num=2;
            };
            $this->setRenderData(['data'=>$data]);
            $this->redirect('info');
        }
        //注册
        public function reg_ok()
        {
            $nickname=IReq::get('nickname');
            $staffcode=IReq::get('staffcode');
            $staffname=IReq::get('staffname');
            $salary=IReq::get('salary');
            $data=array(
                    'nickname'=>$nickname,
                    'staffcode'=>$staffcode,
                    'staffname'=>$staffname,
                    'salary'=>$salary,
                    );
            $staff=new IModel('staff');
            $staff->setData($data);
            $staff->add();
            $this->redirect('login');
        }
        //搜索
        public function search_da()
        {
            
        }
        //导出
        public function export()
        {
            $userinfo=array(
                'ip'=>$_SERVER['REMOTE_ADDR'],
                'adminname'=>ISession::get('adminname'),
                'addtime'=>time(),
                );
            $log=new IModel('log_admin');
            $log->setData($userinfo);
            $log->add();
            $admin=new IQuery('staff');
            $data=$admin->find();
            $reportObj = new report('user');
            $reportObj->setTitle(array("id","用户名","密码","真实姓名","工资"));
            foreach ($data as $key => $val) 
            {
                $insertData = array($val['id'],$val['nickname'],$val['staffcode'],$val['staffname'],$val['salary']);
            $reportObj->setData($insertData);
            }
            $reportObj->toDownload();
        }
        //导入
        public function import()
        {
            require 'plugins/vendor/autoload.php';          // 包含自动加载文件
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(TRUE);
            $spreadsheet = $reader->load("public/test.xlsx");
            $worksheet = $spreadsheet->getActiveSheet();

            $row=$worksheet->getHighestRow();
            $column=$worksheet->getHighestcolumn();
            
            $dataArray = $spreadsheet->getActiveSheet()
                ->rangeToArray(
                    'A1:'.$column.$row,     // The worksheet range that we want to retrieve
                    NULL,        // Value that should be returned for empty cells
                    TRUE,        // Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
                    TRUE,        // Should values be formatted (the equivalent of getFormattedValue() for each cell)
                    TRUE         // Should the array be indexed by cell row and cell column
                );
                var_dump($dataArray);
        }
        
    }