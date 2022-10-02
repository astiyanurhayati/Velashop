<?php

namespace App\Http\Controllers;

use Alert;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){

        $this->middleware(function($request, $next){
            if(Gate::allows('manage-categories')) return $next($request);
            abort(403, 'anda tidak memiliki cukup hak akses');
        });
    }
  




    public function index(Request $request)
    {
        //Fitur category list
        $categories = Category::paginate(10);

        //filter search
        $filterKeyword = $request->get('name');
        if ($filterKeyword) {
            $categories = Category::where("name", "LIKE", "%$filterKeyword%")->paginate(10);
        }
        return view('categories.index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Validator::make($request->all(), [
            "name" => "required|min:3|max:20",
            "image" => "required"
            ])->validate();

        //abis create di tampung di store
        $name = $request->get('name');
        $new_category = new Category;
        $new_category->name = $name;

        // pengecekkan apakah ada request bertipe file dengan nama 'image',
        //Jika ada impan file tersebut ke dalam folder storage/app/public/category_images
        if ($request->file('image')) {
            $image_path = $request->file('image')
                ->store('category_images', 'public');

            $new_category->image = $image_path;
        }

        //mengambil nilai id dari user yang sedang login dan berikan ke field created_by
        $new_category->created_by = Auth::user()->id;
        $new_category->slug = Str::slug($name, '-');
        $new_category->save();
        
        // dd($request);
        return redirect()->route('categories.create')->with('status', 'Category successfully created');
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return view('categories.show', ['category' => $category]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category_to_edit = Category::findOrFail($id);
        return view('categories.edit', ['category' => $category_to_edit]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        Validator::make($request->all(), [
            "name" => "required|min:3|max:20",
            "image" => "required",
            "slug" => [
            "required",
            Rule::unique("categories")->ignore($category->slug, "slug")],
        ])->validate();

        $name = $request->get('name');
        $slug = $request->get('slug');

        // cari Category yang sedang diedit
        $category->name = $name;
        $category->slug = $slug;


        if ($request->file('image')) {
            if ($category->image && file_exists(storage_path('app/public/' .
            $category->image))) {
                Storage::delete('public/' . $category->name);
            }
            $new_image = $request->file('image')->store('category_images',
            'public');
            $category->image = $new_image;
        }
        
        $category->updated_by = Auth::user()->id;
        $category->slug = Str::slug($name);
        
        $category->save();
        // dd($request);

        return redirect()->route('categories.edit', [$id])->with('status','Category succesfully update');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category successfully moved to trash');
    }

    //data Categorynya blum terhapus beneran masih ada di database, untuk melihatnnya bikin mehtod baru
    public function trash(){ 
        $deleted_category = Category::onlyTrashed()->paginate(10);
        return view('categories.trash', ['categories' => $deleted_category]);

    //onlyTrashed() untuk mendapatkan hanya kategori yang status nya soft delete yaitu yang field deleted_at nya tidak NULL.
    }

    //fitur restone wkwk
    public function restore($id){ 
        //withTrashed() karena ingin mencari semua Category baik yang aktif maupun yang ada di trash
        $category = Category::withTrashed()->findOrFail($id);
        if($category->trashed()){
            $category->restore();
        }else {
            return redirect()->route('categories.index')->with('status', 'Category is not in trash');
        }
        return redirect()->route('categories.index')->with('status', 'Category successfully restored');

    }

    //method delete Permanen
    public function deletePermanent($id){
        $category = Category::withTrashed()->findOrFail($id);

        //jika kategori yang akan dihapus permanent statusnya tidak di tong sampah / trashed /soft delete maka  stop operasi hapus permanent ini dan redirect dengan pesan tidak bisa menghapus kategori yang sedang aktif.
        if(!$category->trashed()){
            return redirect()->route('categories.index')->with('status', 'Can not delete permanent active category');
        } else {
            //Selanjutnya jika kategori tersebut memang sedang dalam tong sampah maka hapus secara permanent menggunakan method forceDelete() kemudian  redirect kembali ke halaman categories list dengan pesan bahwa kategori telah dihapus secara permanen
            $category->forceDelete();
            return redirect()->route('categories.index')->with('status', 'Category permanently deleted'); //pake inggris agar keren
        }
 
    }


    public function ajaxSearch(Request $request){
        $keyword = $request->get('q');
        $categories = Category::where("name", "LIKE", "%$keyword%")->get();
        return $categories;
    }
}



