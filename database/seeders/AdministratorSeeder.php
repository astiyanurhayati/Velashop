<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $administrator = new \App\Models\User;
        $administrator->username = "admin";
        $administrator->name = "astiya";
        $administrator->email = "astiya@gmail.com";
        $administrator->roles = json_encode(["ADMIN"]);
        $administrator->password = Hash::make("asti");
        $administrator->avatar = "saat-ini-tidak-ada-file.png";
        $administrator->address = "bogor";
        $administrator->save();
        $this->command->info("User Admin berhasil diinsert");
    }
}
