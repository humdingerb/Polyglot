<?php

namespace Polyglot\Http\Controllers;

use Polyglot\Language;
use Polyglot\Project;
use Polyglot\ProjectUser;
use Polyglot\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::all();
        return view('projects.index')->with('projects', $projects);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project = new Project;
        $project->name = $request->input('name');
        $project->url = '';
        $project->save();

        $project->users()->attach(Auth::id(), ['role' => 2]);

        return \Redirect::route('projects.index')->with('message', 'Project added.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \Polyglot\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project, $display = 'active')
    {
        // count progress
        $languages = Language::orderBy('iso_code')->get();
        $status = [];
        $modified = [];
        foreach($project->files as $file) {
            $status[$file->id] = [];
            $texts = $file->texts()->get(['id']);
            $count = $texts->count();
            foreach($languages as $language) {
                // FIXME: this can be optimized
                $translated = Translation::whereIn('text_id', $texts)
                    ->where('language_id', $language->id)
                    ->groupBy('needs_work')
                    ->selectRaw('needs_work, count(id)')
                    ->get()
                    ->mapWithKeys(function($item) use ($count) {
                        return [$item['needs_work'] =>
                            round($item['count(id)'] / $count * 100)];
                    })->toArray();
                if(!array_key_exists(0, $translated)) $translated[0] = 0;
                if(!array_key_exists(1, $translated)) $translated[1] = 0;
                $keys = ['translated', 'needs_work'];
                $status[$file->id][$language->id] =
                    array_combine($keys, $translated);
                if(array_key_exists($language->id, $modified) === false)
                    $modified[$language->id] = 0;
                $modified[$language->id] += array_sum($translated);
            }
        }
        $contributorRoles = [
            0 => 'past-translator',
            1 => 'translator',
            2 => 'admin'
        ];
        $contributors = ProjectUser::contributors($project->id)
            ->with('user')
            ->with('language')
            ->get()->groupBy('user_id')->sortBy(function($c) {
                return strtolower($c[0]->user->name);
            });
        $displayLinkLabel = sprintf('Show %s languages',
                                    ($display == 'all' ? 'only active' : 'all'));
        return view('projects.show')
            ->with('display', $display)
            ->with('project', $project)
            ->with('status', $status)
            ->with('modifiedKeys', $modified)
            ->with('displayLinkLabel', $displayLinkLabel)
            ->with('languages', $languages)
            ->with('roleClass', $contributorRoles)
            ->with('contributors', $contributors);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Polyglot\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $languages = Language::pluck('name', 'id')->all();
        return view('projects.edit')
            ->with('project', $project)
            ->with('languages', $languages);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Polyglot\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        if(count($request->get('languages')) > 0) {
            $project->languages()->sync($request->get('languages'));
        }

        return \Redirect::route('projects.show', [$project->id])->with('message', 'Languages saved.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Polyglot\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        //
    }
}
