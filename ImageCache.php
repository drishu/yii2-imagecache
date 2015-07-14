<?php

namespace drishu\yii2imagecache;

use yii\base\Component;
use yii\helpers\BaseFileHelper;

/**
 * @author Szilagyi Andras <mr.drishu@gmail.com>
 */
class ImageCache extends Component
{
    /**
     * The folder the images are coming from
     * @TODO: support external images
     * @var type 
     */
    public $sourcePath;
    
    /**
     * Path to cache folder
     * @var type 
     */
    public $cachePath;
    
    /**
     * Tries to create a cached resized image.
     * @TODO: try imagemagick and fallback to GD if not found
     * @param type $path
     * @param type $preset
     * @param type $expires
     * @return string
     */
    public function get($path, $preset, $expires = 31536000)
    {
        if (strpos($path, 'http') !== false)
        {
            // read the external image
            $ch = curl_init();

            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $content = curl_exec($ch);
            curl_close($ch);
            
            if (empty($content))
            {
                return $path;
            }
            
            $sourceImagePath = $path;
        }
        else
        {
            $sourceImagePath = $this->sourcePath.$path;
        }
        
        if (!is_file($sourceImagePath))
        {
            return null;
        }
        
        $fileExtension =  pathinfo($sourceImagePath, PATHINFO_EXTENSION);
        $fileName =  pathinfo($sourceImagePath, PATHINFO_FILENAME);
        
        list($width, $height) = getimagesize($$sourceImagePath);
        
        $pathToSave = $this->cachePath.'/imagecache/'.$preset.'/'.$fileName.'.'.$fileExtension;
        
        BaseFileHelper::createDirectory(dirname($this->sourcePath.$pathToSave), 0777, true);
        
        if (is_file($this->sourcePath.$pathToSave) && $lastModified = filemtime($this->sourcePath.$pathToSave))
        {
            $expiresIn = $lastModified + $expires;
            
            if (time() > $expiresIn)
            {
                unlink($this->sourcePath.$pathToSave);
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
                $image = imagecreatefromjpeg($sourceImagePath);
                break;
            case 'gif':
                $image = imagecreatefromgif($sourceImagePath);
                break;
            case 'png':
                $image = imagecreatefrompng($sourceImagePath);
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
                imagejpeg($tmp, $this->sourcePath.$pathToSave, 100);
                break;
            case 'gif':
                imagegif($tmp, $this->sourcePath.$pathToSave, 100);
                break;
            case 'png':
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                imagepng($tmp, $this->sourcePath.$pathToSave, 9);
                break;
        }
        
        return $pathToSave;
    }
    
    /**
     * Remove all cached images
     */
    public function clearAll()
    {
        @BaseFileHelper::removeDirectory($this->sourcePath.$this->cachePath.'/imagecache/');
    }
}
