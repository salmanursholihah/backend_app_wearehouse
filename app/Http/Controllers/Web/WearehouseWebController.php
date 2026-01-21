<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use App\Models\Request as ItemRequest;

class RequestWebController extends Controller
{
    public function index()
    {
        return view('pages.requests.index', [
            'requests' => ItemRequest::with('user','items.product')->latest()->get()
        ]);
    }

    public function show(ItemRequest $request)
    {
        return view('pages.requests.show', compact('request'));
    }

    public function process(Request $request, ItemRequest $itemRequest)
    {
        $itemRequest->update([
            'status' => $request->status,
            'processed_by' => auth()->id()
        ]);

        return back()->with('success','Request diproses');
    }
}

