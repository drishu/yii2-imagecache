<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\BaseFileHelper;

/**
 * @author Szilagyi Andras <mr.drishu@gmail.com>
 */
class ImageCache extends Component
{
    public static function get($path, $preset, $expires = 31536000)
    {
        if (strpos($path, 'http') !== false)
        {
            return $path;
        }
        
        if (!is_file(Yii::getAlias('@base').$path))
        {
            return null;
        }
        
        $fileExtension =  pathinfo(Yii::getAlias('@base').$path, PATHINFO_EXTENSION);
        $fileName =  pathinfo(Yii::getAlias('@base').$path, PATHINFO_FILENAME);
        
        list($width, $height) = getimagesize(Yii::getAlias('@base').$path);
        
        $pathToSave = '/data/imagecache/'.$preset.'/'.$fileName.'.'.$fileExtension;
        
        BaseFileHelper::createDirectory(dirname(Yii::getAlias('@base').$pathToSave), 0777, true);
        
        if (is_file(Yii::getAlias('@base').$pathToSave) && $lastModified = filemtime(Yii::getAlias('@base').$pathToSave))
        {
            $expiresIn = $lastModified + $expires;
            
            if (time() > $expiresIn)
            {
                unlink(Yii::getAlias('@base').$pathToSave);
            }
        }        
        
        $preset = strtolower($preset);
        $parts = explode('x', $preset);
        $newWidth = intval($parts['0']);
        $newHeight = isset($parts['1']) ? intval($parts['1']) : 0;
        
        switch($fileExtension)
        {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg(Yii::getAlias('@base').$path);
                break;
            case 'gif':
                $image = imagecreatefromgif(Yii::getAlias('@base').$path);
                break;
            case 'png':
                $image = imagecreatefrompng(Yii::getAlias('@base').$path);
                break;
        }
        
        if (!empty($newWidth) && !empty($newHeight))
        {
            
        }
        elseif (!empty($newWidth))
        {
            $x = ($newWidth * 100) / $width;
            $newHeight = ceil(($height * $x) / 100);
        }
        elseif (!empty($newHeight))
        {
            $x = ($newHeight * 100) / $height;
            $newWidth = ceil(($width * $x) / 100);
        }
        
        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($tmp,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);

        switch($fileExtension)
        {
            case 'jpeg':
            case 'jpg':
                imagejpeg($tmp, Yii::getAlias('@base').$pathToSave, 100);
                break;
            case 'gif':
                imagegif($tmp, Yii::getAlias('@base').$pathToSave, 100);
                break;
            case 'png':
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                imagepng($tmp, Yii::getAlias('@base').$pathToSave, 9);
                break;
        }
        
        return $pathToSave;
    }
    
    public static function clearAll()
    {
        @BaseFileHelper::removeDirectory(Yii::getAlias('@base').'/data/imagecache/');
    }
}
