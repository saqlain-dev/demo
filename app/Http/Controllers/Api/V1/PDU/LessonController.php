<?php

namespace App\Http\Controllers\Api\V1\PDU;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lessons=Lesson::with(['projectDetail','themeName','lessonCategory','provienceName'])->get();
        return resp(1,'Successful!', $lessons,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'province_id' => 'required',
            'category_id' => 'required',
            'theme_id' => 'required',
            'lesson_title' => 'required',
            'lesson_narrative' => 'required',
            'recommendations' => 'required',
        ]);
        $lesson=Lesson::query()->create( $this->input);

        return resp(1,'Successful!', $lesson,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        $lessons=Lesson::with(['projectDetail','themeName','lessonCategory','provienceName'])->findOrFail($lesson->id);
        return resp(1,'Successful!', $lessons,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lesson $lesson)
    {
        $request->validate([
            'province_id' => 'required',
            'category_id' => 'required',
            'theme_id' => 'required',
            'lesson_title' => 'required',
            'lesson_narrative' => 'required',
            'recommendations' => 'required',
        ]);
        $updateLesson=Lesson::query()->where('id', $lesson->id)->update( $this->input);
        $lessons=Lesson::with(['projectDetail','themeName','lessonCategory','provienceName'])->findOrFail($lesson->id);
        return resp(1,'Successful!', $lessons,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }
    public function lessonDropdown(){

        $data['theme']= Type::getTypeValues('lesson-theme');
        $data['category']= Type::getTypeValues('lesson-category');
        $data['province']= Type::getTypeValues('province');
        $data['projects']= ProjectProfile::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function projectLesson($id){

        $lessons=Lesson::with(['projectDetail','themeName','lessonCategory','provienceName'])->where('project_id',$id)->get();

        return resp(1,'Successful!', $lessons,Response::HTTP_CREATED);
    }
}
