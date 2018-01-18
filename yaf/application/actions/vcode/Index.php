<?php
/**
 * @describe:
 * @author: liuwy(liuwy@yindou.com)
 * */

/* vim:set ts=4 sw=4 et fdm=marker: */

class IndexAction extends Yaf_Action_Abstract{
    public function execute(){
        $img = new Vcode_Securimage();
        $img->ttf_file        = APPLICATION_PATH . '/application/library/Font/Msyhbd.ttf';
        if (mt_rand(0, 5) < 2) {
            $img->captcha_type = Vcode_Securimage::SI_CAPTCHA_MATHEMATIC; // show a simple math problem instead of text
        }  
        $img->case_sensitive  = false;                              // true to use case sensitve codes - not recommended
        $img->image_height    = 90;                                // height in pixels of the image
        $img->image_width     = $img->image_height * M_E;          // a good formula for image size based on the height

        if (!empty($_GET['namespace'])) {
            $img->setNamespace($_GET['namespace']);
        }   

        header('Content-Type: image/png');
        $img->show();  // outputs the image and content headers to the browser
        $session = Yaf_Session::getInstance();
        $session->set('vcode',$img->code);
    }
}
