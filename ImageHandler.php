<?php


namespace App\Libs;

use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class ImageHandler
{
    private $path;

    private $clientFieldName;

    private $clientOriginalName;

    private $request;

    private $imageName;

    private $savedPath;

    private $basePath;

    private $public_path;

    private $file_key;
    
    private $noImage = "no-image";

    private $noImageFile = "/images/no-image.png";
    /**
     * @return mixed
     */
    public function getAutoFileKey()
    {
        return $this->file_key;
    }

    public function setAutoFileKey()
    {        
       // $isset = isset($file) ? $file : null;
        $uploadedFile = $this->request->file();
// dd($uploadedFile);
        if(count($uploadedFile)>0)       
        {           
            $this->file_key = array_keys($uploadedFile)[0];
            $this->clientFieldName = $this->file_key;//for the time being        
        }
        else
        {
            $this->clientFieldName = $this->file_key = $this->noImage ;                
        }

        return $this;   
    }
    /**
     * @return mixed
     */
    public function getClientOriginalName()
    {
        if($this->clientFieldName != $this->noImage)        
            return $this->request->file($this->clientFieldName)->getClientOriginalName();

        return $this->noImageFile;
    }

    /**
     * @param mixed $clientOriginalName
     */
    public function setClientOriginalName($clientOriginalName)
    {
        $this->clientOriginalName = $clientOriginalName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublicPath()
    {
        return $this->public_path;
    }

    /**
     * @param mixed $public_path
     * @return $this
     */
    public function setPublicPath($public_path)
    {
        $this->public_path = $public_path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSavedPath()
    {
        return $this->savedPath;
    }

    /**
     * @param $clientFieldName
     * @return $this
     *
     */
    public function setClientFieldName($clientFieldName)
    {
        $this->clientFieldName = $clientFieldName;
        return $this;
    }

    /**
     * @param mixed $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param mixed $imageName
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        return $this;
    }

    /**
     * @param mixed $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function concatWithBasePath($path)
    {
        $this->basePath = base_path() . $path;
        return $this;
    }

    public function concatWithPublicPath()
    {
        $this->public_path = public_path() . $this->path;
        return $this;
    }

    public function SaveToPublicDirectory($request, $ImageName, $directoryPath)
    {
        $path = base_path() . '/public/images/' . $directoryPath;
        //    $path = base_path() . '/public/'.$directoryPath;
        $request->file('image_paths')->move($path, $ImageName);
        //     $filePath = $path.'/'.$ImageName;
        //  chmod($filePath, 0775);
        return $path;
    }

    public function moveTo($path)
    {


            $uploadedFile = $this->request->file();
             if(count($uploadedFile)>0)       
           {  
                $this->savedPath = $this->request->file($this->clientFieldName)->move($path, $this->imageName)->getPath();
            }
                return $this;
    }

    public function move(Request $request)
    {
        $this->savedPath = $request->file($this->clientFieldName)->move($this->path, $this->imageName);

        return $this;
    }

    public function deleteUserPhotoFromDirectory($id)
    {
        $user = new \App\Models\User();
        $userData = $user->with(['addressInfo', 'roles'])->find($id);
        //delete photos from directory
        $filename = public_path() . '/images/profileImages/' . $userData['photo_path'];
        // $filename = public_path().'/images/'.$userData['photo_path'];
        $filename = base_path() . '/public/' . $userData['photo_path'];
        if (File::exists($filename)) {
            File::delete($filename);
        }
    }


    public function deletePrizePhotoFromDirectory($id)
    {
        $obj = new \App\Models\Prize();
        $data = $obj->find($id);
        //delete photos from directory
        $filename = public_path() . '/images/prizeImage/' . $data['photo_path'];
        $filename = base_path() . '/public/' . $data['photo_path'];
        if (File::exists($filename)) {
            File::delete($filename);
        }
    }

    public function getBlob($path)
    {
        $base64_encode = null;
        if (!empty($path))
            $base64_encode = base64_encode(file_get_contents($path));
        return $base64_encode;
    }


    public function saveLogo(Request $request,$loc)
    {
        $imageHandlerObj = new ImageHandler();
        //get image name if exist else no-image.png
        $clientOriginalName = $imageHandlerObj
            ->setRequest($request)
            ->setAutoFileKey()
            ->getClientOriginalName();

        $imagePath = $clientOriginalName == '/images/no-image.png' ? '/images/': $loc;        
        $logoPath = $clientOriginalName == '/images/no-image.png' ? $clientOriginalName:$loc.$clientOriginalName;
            
        if($clientOriginalName != 'no-image.png')
        {
            $path = $imageHandlerObj
                ->setPath($imagePath)
                ->concatWithPublicPath()
                ->setImageName($clientOriginalName)
                ->moveTo($imageHandlerObj->getPublicPath())
                ->getSavedPath();
        }

        return $logoPath;
    }

      public function updateLogo(Request $request,$loc)
    {       
        $image_path = $request->file('image_paths');
  
        if(is_null( $image_path ))
            return false;

        $imageHandlerObj = new ImageHandler();
        //get image name if exist else no-image.png
        $clientOriginalName = $imageHandlerObj
            ->setRequest($request)
            ->setAutoFileKey()
            ->getClientOriginalName();

        $imagePath = $clientOriginalName == '/images/no-image.png' ? '/images/': $loc;        
        $logoPath = $clientOriginalName == '/images/no-image.png' ? $clientOriginalName:$loc.$clientOriginalName;
            
        if($clientOriginalName != 'no-image.png')
        {
            $path = $imageHandlerObj
                ->setPath($imagePath)
                ->concatWithPublicPath()
                ->setImageName($clientOriginalName)
                ->moveTo($imageHandlerObj->getPublicPath())
                ->getSavedPath();
        }

        return $logoPath;
    }

}