<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;


class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');

    }
 
    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function add_brand() {
        return view('admin.brand-add');
    }
    public function brand_store(Request $request) {
 {
    $request->validate([
        'name' => 'required',
        'slug'=>'required|Unique:brands,slug',

        'image' => 'required|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);
        $brand = new Brand();
        $brand->name = $request->input('name');
        $brand->slug = Str::slug ($request->name);
        $image = $request->file('image');
        $file_extention= $request->file('image')->extension();
        $file_name = Carbon::now ()->timestamp.'.'.$file_extention;
        $this->GenerateBrandThumbailsImage($image,$file_name);
        $brand->image =$file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added succefully');

     
        }
 }   
 public function brandsIndex(Request $request)
{
    $query = Brand::query();

    if ($request->filled('name')) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    $brands = $query->paginate(10);

    return view('admin.brands', compact('brands'));
}

  public function brand_edit ($id){

    $brand=Brand::find($id);

    return view('admin.brand-edit',compact('brand'));


  }
  public function brand_update(Request $request, $id) {
    $request->validate([
        'name' => 'required', // Correcting the validation rule
        'slug' => 'required|unique:brands,slug,' . $id,
        'image' => 'mimes:jpg,jpeg,png,gif,svg|max:2048',
    ]);

    $brand = Brand::find($id);
    $brand->name = $request->input('name');
    $brand->slug = Str::slug($request->input('name'));
    
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/brands'), $filename);
        $brand->image = $filename;
    }
    
    $brand->save();
    return redirect()->route('admin.brands')->with('status', 'Brand has been updated successfully
    ');

}

  
      public function GenerateBrandThumbailsImage($image, $imageName)
 {
     $destinationPath = public_path('uploads/brands');
     
     $img = Image::read($image->path());  // Fixed method call
     $img->resize(124, 124, function ($constraint) {
         $constraint->aspectRatio();
     })->save($destinationPath . '/' . $imageName);
 }
 public function brand_delete($id) {
    $brand = Brand::find($id);

    if (!$brand) {
        return redirect()->route('admin.brands')->with('error', 'Brand not found.');
    }

    if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
        File::delete(public_path('uploads/brands').'/'.$brand->image);
    }

    $brand->delete();
    
    return redirect()->route('admin.brands')->with('status', 'Brand has been deleted successfully.');
 }}
