<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\{Student, VideoCourse, VideoCourseModule};

class VideoCourseModuleService
{
    // public static function getModuleById(int $module_id): VideoCourseModule
    // {
    //     return VideoCourseModule::find($module_id);
    // }

    public static function getModulesByVideoCourse(VideoCourse $videoCourse)
    {
        return VideoCourseModule::where('video_course_id', $videoCourse->id)->get();
    }    

    public static function getModuleProgressByStudentId(VideoCourseModule $videoCourseModule, int $studentId): int
    {
        $student = Student::find($studentId);
        $classViewCount = $student->videoCourseClassViews()->whereRelation('module', 'id', $videoCourseModule->id)->count();
        $totalClasses = $videoCourseModule->classes()->count();

        return intval(($classViewCount / $totalClasses) * 100);
    }

    public static function storeModules(array $moduleNames, VideoCourse $videoCourse): void
    {
        try {
            $position = $videoCourse->modules()->exists() 
                ? $videoCourse->modules()->orderByDesc('position')->first()->position + 1
                : 1;

            foreach ($moduleNames as $moduleName) {
                VideoCourseModule::create([
                    'name' => $moduleName,
                    'position' => $position,
                    'video_course_id' => $videoCourse->id,
                ]);

                $position++;
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public static function updateModule(array $requestData, VideoCourseModule $videoCourseModule): void
    {
        $requestData['open'] = isset($requestData['open']);

        $videoCourseModule->update($requestData);
    }

    public static function deleteModule(VideoCourseModule $videoCourseModule)
    { 
        $videoCourseModule->delete();
    }

    public static function reorderPositions(array $ids): void
    {
        foreach ($ids as $key => $id) {
            VideoCourseModule::findOrFail($id)->update([
                'position' => $key + 1
            ]);
        }
    }
}
