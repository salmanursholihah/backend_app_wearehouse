<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsWebController extends Controller
{
    public function index()
    {
        $about = AboutUs::latest()->first();
        return view('about.index', compact('about'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('about', 'public');
        }

        AboutUs::updateOrCreate(['id' => 1], $data);

        return redirect()->back()->with('success', 'About Us updated');
    }
}
