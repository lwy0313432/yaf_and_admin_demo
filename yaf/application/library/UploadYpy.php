<?php
define('UPYUN_BUCKETNAME', 'miyouimg');        // TODO: create new bucket for test
define('UPYUN_USERNAME', 'miyouimg');
define('UPYUN_PASSWORD', 'iKiM987l287');
class UploadYpy
{
    public function upload_image($photo_type, $relation_id, $title_array, $tmp_photo_name_array)
    {
        $photo_type        = (preg_match('/^\w+$/', $photo_type)) ? strtolower($photo_type) : false;
        $relation_id    = intval($relation_id);
        if (is_array($tmp_photo_name_array)) {
            $filename    = $this->make_photo_name($tmp_photo_name_array['tmp_name']);
            $pathname    = "/photo_uploader/".$photo_type;                        // 用photo类型作为path
            if ($filename) {
                if ($this->upload_to_upyun($tmp_photo_name_array['tmp_name'], $pathname, $filename)) {        // 上传到又拍云
                    $dao_photo=new Dao_Default_PhotoModel();
                    $data = array();
                    $data['type'] = $photo_type;
                    $data['relation_id'] = $relation_id;
                    $data['title'] = $title_array;
                    $data['filename'] = $filename;
                    $data['dt'] = date('Y-m-d H:i:s');
                    $in_ret=$dao_photo->Insert($data);
                    if ($in_ret) {
                        $result['filename']=Tools::get_photo_url($photo_type, $filename);
                        $result['image_path']=$filename;
                        return $result;
                    }
                } else {
                    throw new CException(Errno::USER_UPLOAD_IMG_IS_ERROR);
                }
            } else {
                throw new CException(Errno::USER_UPLOAD_IMG_IS_ERROR);
            }
        } else {
            throw new CException(Errno::USER_UPLOAD_FILE_IS_ERROR);
        }
    }
    public function make_photo_name($tmp_name)
    {
        if (file_exists($tmp_name) and filesize($tmp_name)>0) {
            // 检查图片类型，只允许jpg、png图片
            $img = getimagesize($tmp_name);        // 1 = GIF，2 = JPG，3 = PNG
            if ($img[2] == 2 or $img[2] == 3) {
                return md5($tmp_name . microtime()) . (($img[2] == 2) ? '.jpg' : '.png');
            }
        }
        return false;
    }
    private function upload_to_upyun($tmp_name, $pathname, $filename)
    {
        if (defined('UPYUN_BUCKETNAME') and defined('UPYUN_USERNAME') and defined('UPYUN_PASSWORD') and $tmp_name and $pathname and $filename) {
            $content = file_get_contents($tmp_name);
                    //$upyun = new UpYun(UPYUN_BUCKETNAME, UPYUN_USERNAME, UPYUN_PASSWORD);
            $upyun=new Common_UpYun(UPYUN_BUCKETNAME, UPYUN_USERNAME, UPYUN_PASSWORD);
            $opts = array(
                Common_UpYun::CONTENT_MD5 => md5($content)
            );
            $save_name    = "$pathname/$filename";
            $rsp = $upyun->writeFile($save_name, $content, true, $opts);        // 上传图片，自动创建目录
            if (isset($rsp['x-upyun-width']) and isset($rsp['x-upyun-height'])) {        // 上传成功
                return true;
            }
        }
        return false;
    }
}
