<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){

        $this->middleware(function($request, $next){
            if(Gate::allows('manage-users')) return $next($request);
            abort(403, 'anda tidak memiliki cukup hak akses');
        });
    }
  



    public function index(Request $request)
    {

        // $users = User::all();
        //tangkap terlebih dahulu request variabel bernama 'keyword`
        //Data yang ditangkap berasal dari apa yang diketikan oleh user melalui inputan
        $filterKeyword = $request->get('keyword');
        $status = $request->get('status');

        if($status){
        $users = User::where('status', $status)->paginate(10);
        } else {
            $users = User::paginate(10);
        }

        if ($filterKeyword) {
            if ($status) {
                $users = User::where('email', 'LIKE', "%$filterKeyword%")->where('status', $status)->paginate(10);
            } else {
                $users = User::where('email', 'LIKE', "%$filterKeyword%")->paginate(10);
            }
        }
        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //fitur create User

        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Validator::make($request->all(),[
            "name" => "required|min:5|max:100",
            "username" => "required|min:5|max:20",
            "roles" => "required",
            "phone" => "required|digits_between:10,12",
            "address" => "required|min:20|max:200",
            "avatar" => "required",
            "email" => "required|email",
            "password" => "required",
            "password_confirmation" => "required|same:password"
        ])->validate();

        $new_user = new User;
        $new_user->name = $request->get('name');
        $new_user->username = $request->get('username');
        $new_user->roles = json_encode($request->get('roles'));
        $new_user->name = $request->get('name');
        $new_user->address = $request->get('address');
        $new_user->phone = $request->get('phone');
        $new_user->email = $request->get('email');
        $new_user->password = Hash::make($request->get('password'));

        //kalo ada request avatar simpen di folder store bagian public
        //jangan lupa php artisan storage:link
        if ($request->file('avatar')) {
            $file = $request->file('avatar')->store('avatars', 'public');
            $new_user->avatar = $file;
        }

        //lalu simpan
        $new_user->save();

        // mngarahkan user kembali ke form create
        return redirect()->route('users.create')->with('status', 'Berhasil Tambahkan User Baru');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //untuk halmanan detail
        $user = User::findOrFail($id);
        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', ['user' => $user]);
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
        Validator::make($request->all(), [
            "name" => "required|min:5|max:100",
            "roles" => "required",
            "phone" => "required|digits_between:10,12",
            "address" => "required|min:20|max:200",
           ])->validate();

        $user = User::findOrFail($id);

        $user->name = $request->get('name');
        $user->roles = json_encode($request->get('roles'));
        $user->address = $request->get('address');
        $user->phone = $request->get('phone');
        $user->status = $request->get('status');

        //  dd($request);
        // Untuk field roles sebelum menyimpan ke database kita ubah dulu dari PHP Array menjadi JSON Array
        // menggunakan fungsi json_encode() agar dapat disimpan ke database

        // Jika terdapat file upload 'avatar' maka dicek lagi, apakah user yang akan diedit ini memiliki file avatar
        if ($request->file('avatar')) {

            // apakah file tersebut ada di server kita, jika ada maka kita hapus file tersebut.
            if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
                Storage::delete('public/' . $user->avatar);
            }

            //Setelah itu kita simpan file yang diupload ke folder "avatars"
            $file = $request->file('avatar')->store('avatars', 'public');
            //Lalu set field 'avatar' user dengan path baru dari image yang diupload tadi
            $user->avatar = $file;
        }
        //terus update
        $user->save();
        return redirect()->route('users.index', [$id])->with('status', 'User
        succesfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('status', 'Berhasil hapus User');
    }
}
