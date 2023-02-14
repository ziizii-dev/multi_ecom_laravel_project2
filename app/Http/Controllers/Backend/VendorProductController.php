<?php

namespace App\Http\Controllers\Backend;

use Image;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\MultiImg;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Backend\VendorProductController;

class VendorProductController extends Controller
{
    //all product
    public function vendorAllProduct(){
       $id = Auth::user()->id;
        $products = Product::where('vendor_id',$id)->where('delete_status',1)->latest()->get();
        return view('vendor.backend.product.vendor_product_all',compact('products'));
}//End Method
//Add product
public function vendorAddProduct(){
    $brands = Brand::latest()->get();
    $categories= Category::latest()->get();

    return view('vendor.backend.product.vendor_product_add',compact('brands','categories'));
}//End Method
//get subcategory data form ajax
public function vendorGetSubCategory($category_id){
    // dd($category_id);
$subcat = SubCategory::where('category_id',$category_id)->orderBy('subcategory_name','ASC')->get();
// dd($subcat);
return json_encode($subcat);
}//End method


//Store Product
public function vendorStoreProduct(Request $request){

    $this->storeProductValidationCheck($request);
    $image = $request->file('product_thambnail');
    $fileName = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
    Image::make($image)->resize(800,800)->save('upload/products/thambnail/'.$fileName);
    $save_url = 'upload/products/thambnail/'.$fileName;

    $product_id = Product::insertGetId([
            'brand_id'=>$request->brand_id,
            'category_id'=>$request->category_id,
            'subcategory_id'=>$request->subcategory_id,
            'product_name'=>$request->product_name,
            'product_slug'=>strtolower(str_replace(' ','-',$request->product_name)),
            'product_code'=>$request->product_code,
            'product_qty'=>$request->product_qty,
            'product_tags'=>$request->product_tags,
            'product_size'=>$request->product_size,
            'product_color'=>$request->product_color,
            'selling_price'=>$request->selling_price,
            'discount_price'=>$request->discount_price,
            'short_descp'=>$request->short_descp,
            'long_descp'=>$request->long_descp,
            'product_thambnail'=>$save_url,
            'vendor_id'=>Auth::user()->id,
            'hot_deals'=>$request->hot_deals,
            'featured'=>$request->featured,
            'special_offer'=>$request->special_offer,
            'special_deals'=>$request->special_deals,
            'status'=>1,
            'created_at'=>Carbon::now(),
    ]);

    $images = $request->file('multi_img');
    // dd($images);
    foreach($images as $img){
        $multiImg = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
        Image::make($img)->resize(800,800)->save('upload/products/multi-image/'.$multiImg);
        $uploadPath = 'upload/products/multi-image/'.$multiImg;
        MultiImg::insert([
            'product_id'=>$product_id,
            'photo_name'=> $uploadPath,
            'created_at' =>Carbon::now(),
        ]);
    };//End foreach
    $notification = array(
        'message'=>"Vendor Product Inserted Successfully",
        'alert-type'=>'success'
    );
    return redirect()->route('vendor#allProduct')->with($notification);

}//End Method

//Edit product
public function vendorEditProduct($id){
    $multiImgs = MultiImg::where('product_id',$id)->where('delete_status',1)->get();
    $brands = Brand::latest()->get();
    $categories= Category::latest()->get();
    $subcategory= SubCategory::latest()->get();
    $products = Product::findOrFail($id);

    return view('vendor.backend.product.vendor_product_edit',compact('brands','categories','products','subcategory','multiImgs'));
}//End method
//update product
public function vendorUpdateProduct(Request $request){
    $this->updateProductValidationCheck($request);
    $product_id = $request->id;
                Product::findOrFail($product_id)->update([
        'brand_id'=>$request->brand_id,
        'category_id'=>$request->category_id,
        'subcategory_id'=>$request->subcategory_id,
        'product_name'=>$request->product_name,
        'product_slug'=>strtolower(str_replace(' ','-',$request->product_name)),
        'product_code'=>$request->product_code,
        'product_qty'=>$request->product_qty,
        'product_tags'=>$request->product_tags,
        'product_size'=>$request->product_size,
        'product_color'=>$request->product_color,
        'selling_price'=>$request->selling_price,
        'discount_price'=>$request->discount_price,
        'short_descp'=>$request->short_descp,
        'long_descp'=>$request->long_descp,
        'hot_deals'=>$request->hot_deals,
        'featured'=>$request->featured,
        'special_offer'=>$request->special_offer,
        'special_deals'=>$request->special_deals,
        'status'=>1,
        'created_at'=>Carbon::now(),




]);
$notification = array(
    'message'=>"Vendor Product Without Images Updated  Successfully",
    'alert-type'=>'success'
);
return redirect()->route('vendor#allProduct')->with($notification);
}//End Method

//Validation
private function storeProductValidationCheck($request){
    Validator::make($request->all(),[
        "brand_id" =>"required",
        "category_id" =>"required",
        "subcategory_id" =>"required",
        "product_name" =>"required",
        "product_code" =>"required",
        "product_qty" =>"required",
        "selling_price" =>"required",
        "discount_price" =>"required",
        "short_descp" =>"required",
        "long_descp" =>"required",
        "product_thambnail" =>'required| mimes:jpeg,jpg,png,webp,gif',
        // "multi_img"=>'required',




    ])->Validate();
 }//End method
    //Validation
private function updateProductValidationCheck($request){
    Validator::make($request->all(),[
        "brand_id" =>"required",
        "category_id" =>"required",
        "subcategory_id" =>"required",
        "product_name" =>"required",
        "product_code" =>"required",
        "product_qty" =>"required",
        "selling_price" =>"required",
        "discount_price" =>"required",
        "short_descp" =>"required",
        "long_descp" =>"required",

    ])->Validate();
 }//End

}
