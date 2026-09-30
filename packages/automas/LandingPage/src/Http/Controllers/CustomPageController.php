<?php

namespace Automas\LandingPage\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Automas\LandingPage\Models\CustomPage;
use Automas\LandingPage\Models\LandingPageSetting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Automas\LandingPage\Http\Requests\StoreCustomPageRequest;
use Automas\LandingPage\Http\Requests\UpdateCustomPageRequest;

class CustomPageController extends Controller
{
    public function index(Request $request)
    {
        if(Auth::user()->can('manage-custom-pages')){
            $query = CustomPage::query();
        
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }
            
            if ($request->filled('sort')) {
                $direction = $request->get('direction', 'asc');
                $query->orderBy($request->sort, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $pages = $query->paginate($request->get('per_page', 10));
            
                return Inertia::render('LandingPage/CustomPages/Index', [
                    'pages' => $pages
                ]);
            }
        else{
            return back()->with('error', __('Permission denied'));
        }
    }

    public function create()
    {
        if(Auth::user()->can('create-custom-pages')){
            return Inertia::render('LandingPage/CustomPages/Create');
        }
        else{
            return redirect()->route('custom-pages.index')->with('error', __('Permission denied'));
        }
    }

    public function store(StoreCustomPageRequest $request)
    {
        if(Auth::user()->can('create-custom-pages')){
            $validated = $request->validated();

            if (!$validated['slug']) {
                $validated['slug'] = Str::slug($validated['title']);
            }

                CustomPage::create($validated);

                return redirect()->route('custom-pages.index')->with('success', __('The custom page has been created successfully.'));
            }
        else{
            return redirect()->route('custom-pages.index')->with('error', __('Permission denied'));
        }
    }

    public function edit(CustomPage $customPage)
    {
        if(Auth::user()->can('edit-custom-pages')){
          
            return Inertia::render('LandingPage/CustomPages/Edit', [
                'page' => $customPage
            ]);
        }
        else{
            return redirect()->route('custom-pages.index')->with('error', __('Permission denied'));
        }
    }

    public function update(UpdateCustomPageRequest $request, CustomPage $customPage)
    {
        if(Auth::user()->can('edit-custom-pages')){
            $validated = $request->validated();

            $customPage->update($validated);

            return redirect()->route('custom-pages.index')->with('success', __('The custom page details are updated successfully.'));
        }
        else{
            return redirect()->route('custom-pages.index')->with('error', __('Permission denied'));
        }
    }

    public function destroy(CustomPage $customPage)
    {
        if(Auth::user()->can('delete-custom-pages')){
            if ($customPage->is_disabled) {
                return back()->with('error', __('This page cannot be deleted.'));
            }
            
            $customPage->delete();

            return back()->with('success', __('The custom page has been deleted.'));
        }
        else{
            return redirect()->route('custom-pages.index')->with('error', __('Permission denied'));
        }
    }

    public function show($slug)
    {
        $page = CustomPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $landingPageSettings = LandingPageSetting::first();

        return Inertia::render('LandingPage/CustomPages/Show', [
            'page' => $page,
            'landingPageSettings' => $landingPageSettings
        ]);
    }
}