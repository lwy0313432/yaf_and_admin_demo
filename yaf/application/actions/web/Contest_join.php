<?php                                                                                                                      
/**
 *  * @describe:
 *   * @author: liuwy(liuwy@yindou.com)
 *    * */
class Contest_joinAction extends WebBaseAction{
    private $file_name = '';
    public function beforeExecute(){
        $this->file_name        = isset($_FILES['img_name'])?$_FILES['img_name']:array();
        if(!$this->file_name){
            throw new CException(Errno::USER_UPLOAD_FILE_IS_ERROR);
        }
    }
    public function run($args=null){
        $upload=new UploadYpy();
        $contest_user_id=1;
        $res=$upload->upload_image( Config::PHOTO_TYPE_1,$contest_user_id,Config::UPLOAD_DEFAULT_TITLE, $this->file_name);
        $this->data['list']=$res; 
        var_Export($res);
    }
}
/* vim:set ts=4 sw=4 et fdm=marker: */

