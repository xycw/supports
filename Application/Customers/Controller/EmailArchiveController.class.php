<?php
namespace Customers\Controller;

use Think\Controller;
use Customers\Model\EmailArchiveModel;

class EmailArchiveController extends Controller {

    private $num_package = 10; //每个包的记录数

    public function downAction($site_id) {
        $page = I('page', 1);
        $where = array();
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=EmailArchive');
        $result = $client->down($site_id, $where, $page, $this->num_package);
        if (is_object($result) && get_class($result) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error'=>$result->toString()), 'JSON');
        }
        $data = uncompress_decode($result);
        
        if (is_array($data)) {
            $email_archive = new EmailArchiveModel();
            foreach ($data as $_data) {
                $_data['site_id'] = $site_id;
                $email_archive->add($_data, array(), true);
            }
            $this->ajaxReturn(array('status' => 1), 'JSON');
        } else {
            $this->ajaxReturn(array('status' => 0, 'error'=>'无法识别下载的数据!'), 'JSON');
        }
    }

    public function getPackageAction($site_id) {
        Vendor('phpRPC.phprpc_client');
        $interface_url = $this->getInterfaceUrl($site_id);
        $client = new \PHPRPC_Client($interface_url . '?m=Server&c=EmailArchive');
        $total = $client->count();
        if (is_object($total) && get_class($total) == 'PHPRPC_Error') {
            $this->ajaxReturn(array('status' => 0, 'error' => $total->toString()), 'JSON');
        }
        $num_page = ceil($total / $this->num_package);

        $this->ajaxReturn(array('status' => 1, 'num_page' => $num_page, 'total' => $total), 'JSON');
    }

    private function getInterfaceUrl($site_id) {
        $site_row = D('site')->field('site_interface')->find($site_id);

        return $site_row['site_interface'];
    }

}
