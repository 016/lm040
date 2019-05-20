<?php
namespace eeTools\common;

/**
 * All eeTools' class are Lee's self-dev and self-use class, it's ONLY can be used in Lee's projects, include personal or bussiness.
 * NO ONE ALLOW TO COPY//USE//PUBLIC IT OUT LEE'S PROJECT! IT'S UNDER PROTECT OF LOCAL LAW!!
 * IF YOU FOUND THIS CLASS SOMEWHERE ONLINE OR SOMEWHERE, PLZ SEND ME AN EMAIL THX.
 * IT'S IMPORTANT TO PROTECT INTELLECTUAL PROPERTY RIGHTS OURSELF!!
 *
 * AUTHOR: Boy.Lee <Boy.Lee.Here@gmail.com> http://yiilib.com
 *
 */

class eeImage{
    /**
     * render image with input code
     * @return image 
     */
    public static function renderImage($code, $w = 200, $h = 40, $cntPoint = 800, $cntLine = 6){
        
        //新建一个真彩色图像
        $image = imagecreatetruecolor($w, $h);
        //设置验证码颜色
        $bgcolor = imagecolorallocate($image,255,255,255);
        //填充背景色
        imagefill($image, 0, 0, $bgcolor);

        $tmpArr = eeString::mb_str_split($code);

        //add letter to image one by one with diff color&size
        foreach ($tmpArr  as $i => $value) {
            //设置字体大小
            $fontsize = mt_rand(15, 20);
            //设置字体颜色，随机颜色
            $fontcolor = imagecolorallocate($image, rand(0,120),rand(0,120), rand(0,120));
            //随机码宽度
            $maxOffW = $w/count($tmpArr);
            if ($maxOffW > 10) {
                $maxOffW = 10;
            }
            $x = (($i)*$w/count($tmpArr))+rand(0,$maxOffW);
            //随机码高度
            $y = rand($h/2,$h/2+10);

            //random angle
            $angle = mt_rand(-40, 40);

            imagettftext($image,$fontsize, $angle, $x,$y,$fontcolor, __DIR__.'/../fonts/kaiti2312.ttf' , $value);
        }



        //设置雪花点
        for($i=0;$i<$cntPoint;$i++){
            //设置点的颜色
            $pointcolor = imagecolorallocate($image,rand(50,200), rand(50,200), rand(50,200));    
            //imagesetpixel画一个单一像素
            imagesetpixel($image, rand(0,$w), rand(0,$h), $pointcolor);
        }

        //增加干扰元素
        for($i=0;$i<$cntLine;$i++){
            //设置线的颜色
            $linecolor = imagecolorallocate($image,rand(80,220), rand(80,220),rand(80,220));
            //设置线，两点一线
            imageline($image,rand(1,$w-1), rand(1,$h-1),rand(1,$w-1), rand(1,$h-1),$linecolor);
        }


        
        //设置图片头部
        header('Content-Type: image/png');
        //生成png图片
        imagepng($image);
        //销毁$image
        imagedestroy($image);

        
    }
    
}