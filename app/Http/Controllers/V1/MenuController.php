<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddMenuProductRequest;
use App\Http\Requests\V1\CreateMenuJsonRequest;
use App\Http\Requests\V1\DeleteMenuProductRequest;
use App\Http\Requests\V1\EditMenuProductRequest;
use App\Http\Requests\V1\getRestrauntMenuTable;
use App\Http\Requests\V1\getRestrauntMenuTableRequest;
use App\Repository\MenuRepository\MenuRepositoryInterface;
use App\Repository\MenuRepository\WatingToBuildMenuJsonRepositoryInterface;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Object_;

class MenuController extends Controller
{

    protected $menuRepository ;

    protected $watingToBuildMenuJsonRepo ;


    public function __construct(MenuRepositoryInterface $menuRepository , WatingToBuildMenuJsonRepositoryInterface $watingToBuildMenuJsonRepository)
    {
        $this->menuRepository = $menuRepository ;

        $this->watingToBuildMenuJsonRepo = $watingToBuildMenuJsonRepository ;
    }



    public function addMenuProduct(AddMenuProductRequest $request)
    {



        $addMenuProduct = $this->menuRepository->addProductMenu($request->productid , $request->restrauntid , $request->price , $request->discount , $request->makeups , $request->file('photo1') , $request->file('photo2') , $request->file('photo3')) ;


        if ($addMenuProduct)
            return response()->json([
                'data' => [
                    $addMenuProduct['data']
                ],
                'message' => 'success'
            ],200);
        else
            return response()->json([
                'message' => 'محصول مورد نظر موجود میباشد'
            ],409);
    }



    public function editMenuProduct(EditMenuProductRequest $request)
    {



        $galleryPhoto = [$request->file('photo1') , $request->file('photo2') ,$request->file('photo3')];
        $gallerySrC   = [$request->srcphoto1  , $request->srcphoto2  , $request->srcphoto3 ];
        $ValidateEditMenuProductArray = $this->editPhotoMenuProductValidate($galleryPhoto , $gallerySrC);

        $editgalleryMenuProduct = new Object_();
        if ($ValidateEditMenuProductArray['status'] == true )
        {

            foreach ($ValidateEditMenuProductArray['data'] as $key => $value){
                if (isset($value['file']))
                    $editgalleryMenuProduct->$key = $value['file'] ;
            }


            $editMenuProduct =  $this->
                                menuRepository->
                                editMenuProduct(
                                    $request->id ,
                                    $request->productid ,
                                    $request->restrauntid ,
                                    $request->price ,
                                    $request->discount ,
                                    $request->makeups ,
                                    $editgalleryMenuProduct
                                );

            if ($editMenuProduct)
            {
                return response()->json([
                    'data' => $editMenuProduct
                    ,
                    'message' => 'success'
                ],200);
            }else{
                return response()->json([
                    'message' => 'Error'
                ],409);
            }

        }else{
            return response()->json([
                'message' => 'Error' ,
                'errors' => [
                    'photo' => "خطا در آ‍پلود عکس" ,
                ]
            ],409);
        }


    }



    public function deleteMenuProduct(DeleteMenuProductRequest $request)
    {
        $deleteMenuRestraunt =  $this->menuRepository->deleteMenuProduct($request->id) ;

        if ($deleteMenuRestraunt)
        {
            return response()->json([
                'message' => 'success'
            ],200);
        }else{
            return response()->json([
                'message' => 'Error'
            ],409);
        }
    }



    // this is for create menu json
    public function getRestrauntMenuTable(getRestrauntMenuTableRequest $request )
    {
        $getRestrauntMenuTable =  $this->menuRepository->getRestrauntMenuTable($request->restrauntid);




        if ($getRestrauntMenuTable)
        {
            return response()->json([
                'data' => $getRestrauntMenuTable,
                'message' => 'success'
            ],200);
        }else{
            return response()->json([
                'message' => 'Error'
            ],409);
        }

    }


    // this is for CMS that return with pagination
    public function getMenuTable( $restrauntid , $paginationnumber )
    {
        $restrauntMenuTable =   $this->menuRepository->getMenuTable( $restrauntid , $paginationnumber );

        return response()->json([
            'data' => $restrauntMenuTable ,
            'message' => 'success'
        ],200);
    }


    public function editPhotoMenuProductValidate($galleryPhoto , $gallerySrC)
    {

        $ValidateEditMenuProductArray = [
            'status' => true ,
            'data' => [
                'photo1' => [] ,
                'photo2' => [] ,
                'photo3' => [] ,
            ],
            'message' => ''
        ];



        for ($i = 0 ; $i < count($galleryPhoto) ; $i++)
        {
            if (file_exists($galleryPhoto[$i]))
                $ValidateEditMenuProductArray['data']['photo'.($i+1)]['file'] = $galleryPhoto[$i];
            else if (isset($gallerySrC[$i]))
                $ValidateEditMenuProductArray['data']['photo'.($i+1)]['src'] = $gallerySrC[$i];
            else
                $ValidateEditMenuProductArray['status'] = false ;
        }


        return $ValidateEditMenuProductArray;
    }



    public function getMenuJson( $restrauntid )
    {

         $getRestrauntMenuTable =  $this->menuRepository->getRestrauntMenuTable($restrauntid);

         $menuJson = [];

        foreach ($getRestrauntMenuTable as $key => $value ){
            $menuJson[$value->typename][$value->maingroupname][$value->subgroupname][$value->menuId] = $value ;
         }


        return json_encode($menuJson);

    }



    public function createMenuJson(CreateMenuJsonRequest $request)
    {
        $menuJson = $this->getMenuJson($request->restrauntid);

        $data = [
            'information' => [
                'restrauntid' => 1 ,
                'create_at' => 132654984,
                'key' => '$851das2e12651'
            ],
            'data' => json_decode($menuJson)
        ];


        $fp = fopen("../public/images/{$request->restrauntid}/menu.json", 'w');
        fwrite($fp, json_encode($data) );
        fclose($fp);

        return $data ;

    }



    public function createMenuJsonRequest(CreateMenuJsonRequest $request)
    {


        $isExistCreateMenuRequest =  $this->watingToBuildMenuJsonRepo->IsExistCreateMenuJsonRequest($request->restrauntid);

        $state  =  '' ;
        $action = '' ;



        if ($isExistCreateMenuRequest)
        {
            $action = $updateCreateMenuJson =  $this->watingToBuildMenuJsonRepo->updateCreateMenuJson($request->restrauntid);
            $state = 'update';
        }
        else{
            $action = $insertCreateMenuJson =  $this->watingToBuildMenuJsonRepo->insertCreateMenuJson($request->restrauntid);
            $state = 'insert';
        }




        if ($action)
            return response()->json([
                'message' => $state.' successfull',
                'status' => '200'
            ],200);
        else
            return response()->json([
                'errors' => [
                    1 => [" Request fail "]
                ],
                'status' => '409' ,
            ],200);



    }


    public function getMenuJsonRequestList()
    {
        $getMenuJsonRequestList = $this->watingToBuildMenuJsonRepo->getMenuJsonRequestList();


        if ($getMenuJsonRequestList)
            return response()->json([
                'data' => $getMenuJsonRequestList  ,
                'message' => 'success' ,
                'status' => '200'
            ],200);
        else
            return response()->json([
                'errors' => [
                    1 => [" Request fail "]
                ],
                'status' => '409' ,
            ],200);
    }

}
