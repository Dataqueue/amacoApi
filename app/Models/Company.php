<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Company extends Model
{
    use HasFactory;

    protected $guarded =[];
    public function Img1()
    {
        $path = $this->img1;
        if (File::exists(public_path($this->img1))) {
            return url($path);
        }
        return "No file Uploaded";

    }
    public function Img2()
    {
        $path = $this->img2;
        if (File::exists(public_path($this->img2))) {
            return url($path);
        }
        return "No file Uploaded";

    }
    public function Img3()
    {
        $path = $this->img3;
        if (File::exists(public_path($this->img3))) {
            return url($path);
        }
        return "No file Uploaded";

    }


    
}
