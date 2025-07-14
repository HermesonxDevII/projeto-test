<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\{Classroom, Schedule, Course};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\{Auth, DB, Storage};

class ClassroomService
{
    public static function getAllClassrooms(int $status = null): Collection
    {
        $classrooms = Classroom::query();

        if ($status !== null) {
            // dd($status);
            $classrooms = $classrooms->isActive($status);
        }

        $classrooms = $classrooms->orderBy('name', 'asc');

        return $classrooms->get();
    }

    public static function getClassrooms(Request $request): Collection
    {
        $classrooms = Classroom::query();

        if (isset($request->status)) {
            $classrooms = $classrooms->whereStatus($request->status);
        }

        if (isset($request->id)) {
            $classrooms = $classrooms->whereId($request->id);
        }

        if (isset($request->name)) {
            $classrooms = $classrooms->whereName($request->name);
        }

        return $classrooms->get();
    }

    public static function getClassroomsByTeacherId(int $teacherId, int $courseType = 0): Collection
    {
        return Classroom::whereHas('courses', function ($query) use ($teacherId, $courseType) {
            $query->where('teacher_id', $teacherId);

            if ($courseType !== 0) {
                $query->where('type', $courseType);
            }
        })->isActive()->get();
    }

    public static function getClassroomById(int $id): ?Classroom
    {
        return Classroom::whereId($id)
            ->with('courses.schedules')
            ->first();
    }

    public static function getSchedules(Classroom $classroom, $courseType = 1): Collection
    {
        return Schedule::whereStatus(1)->whereHas('course.classroom', function ($q) use ($classroom, $courseType) {
            $q->whereId($classroom->id)->whereType($courseType);
        })->with('weekday')
            ->get()
            ->unique('weekday.id')
            ->sortBy('weekday.id');

        // SELECT DISTINCT(W.SHORT_NAME) FROM SCHEDULES S
        //     INNER JOIN COURSES C ON C.ID = S.COURSE_ID
        //     INNER JOIN WEEKDAYS W ON S.WEEKDAY_ID = W.ID
        // WHERE C.CLASSROOM_ID = ?
        //    AND S.STATUS = ?
        //    AND C.TYPE = 1
        // ORDER BY W.ID ASC
    }

    public static function getWeekdays(Classroom $classroom): string
    {
        return Self::getSchedules($classroom)->implode('weekday.short_name', ', ');
    }

    public static function getStartEnd(Classroom $classroom)
    {
        $query = Course::where([['classroom_id', $classroom->id], ["type", 1]])->select('start', 'end')->get();
        $start = formatTime($query->sortBy('start')
            ->pluck('start')->first());
        $end   = formatTime($query->sortByDesc('end')
            ->pluck('end')->first());
        return "{$start} ~ {$end}";
    }

    public static function getCoursesByClassroom(Classroom $classroom, int $courseType = 1)
    {
        if ($courseType == 1) {
            // Live courses
            return $classroom->courses()->whereType($courseType)->get()->sortBy('created_at');
        } else if ($courseType == 2) {
            // Recorded Courses
            return Course::whereType($courseType)->whereHas('module', function ($q) use ($classroom) {
                $q->where('classroom_id', $classroom->id);
            })->get()->sortBy('module.created_at');
        } else {
            return null;
        }
    }

    static public function getFirstCourse(Classroom $classroom, int $courseType = 1)
    {
        if ($courseType > 2) {
            return null;
        }

        $first_module = \App\Models\Module::where('classroom_id', $classroom->id)
            ->has('courses')
            ->orderBy('created_at')
            ->first();

        return $first_module?->courses->sortByDesc('recorded_at')->first();
    }

    static public function getLastCourse(Classroom $classroom, int $courseType = 1)
    {
        if ($courseType > 2) {
            return null;
        }

        $first_module = \App\Models\Module::where('classroom_id', $classroom->id)
            ->has('courses')
            ->orderByDesc('created_at')
            ->first();

        return $first_module?->courses->sortByDesc('recorded_at')->first();
    }

    static public function getFirstModule(Classroom $classroom, int $courseType = 1)
    {
        if ($courseType > 2)
        {
            return null;
        } else
        {
            $first_module = ModuleService::getModulesByClassroom($classroom)->sortBy('created_at')->first();

            if ($first_module != null)
            {
                return $first_module;
            } else {
                return null;
            }
        }
    }

    static public function getLastModule(Classroom $classroom, int $courseType = 1)
    {
        if ($courseType > 2)
        {
            return null;
        } else
        {
            $first_module = ModuleService::getModulesByClassroom($classroom)->sortByDesc('created_at')->first();

            if ($first_module != null)
            {
                return $first_module;
            } else {
                return null;
            }
        }
    }

    public static function countByTeacherId(int $teacherId): int
    {
        return Classroom::whereHas('courses', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->isActive()
            ->count();
    }

    public static function storeClassroom(array $requestData): int
    {
        $requestData['classroom']['status'] = true;
        $requestData['classroom']['school_id'] = 1;
        $requestData['classroom']['thumbnail'] = Self::uploadThumbnail($requestData['classroom']);
        $classroom = Classroom::create($requestData['classroom']);
        return $classroom->id;
    }

    public static function uploadThumbnail($request)
    {
        if(!isset($request['thumbnail'])){
            return 'thumbnails/default_thumbnail.png';
        };

        if ($request['thumbnail']) {
            $fileName = uniqid(date('HisYmd')) . ".{$request['thumbnail']->extension()}";

            Storage::putFileAs(
                'public/thumbnails', $request['thumbnail'], $fileName
            );

            return 'thumbnails/' . $fileName;
        } else {
            return 'thumbnails/default_thumbnail.png';
        }
    }
    public static function updateClassroom(array $requestData, Classroom $classroom): void
    {
        if(isset($requestData['thumbnail'])){
            deleteFile($classroom->getRawOriginal('thumbnail'));
            $requestData['thumbnail'] = Self::uploadThumbnail($requestData);
        };

        $classroom->update($requestData);
    }

    public static function deleteClassroom(Classroom $classroom): void
    {
        deleteFile($classroom->getRawOriginal('thumbnail'));
        $classroom->delete();
    }

    public static function forceDeleteClassroom(Classroom $classroom): void
    {
        $classroom->forceDelete();
    }

    public static function restoreClassroom(Classroom $classroom): void
    {
        $classroom->restore();
    }

    public static function addStudent(Classroom $classroom, int $IdStudent): void
    {
        $classroom->students()->attach($IdStudent);
    }

    public static function removeStudent(Classroom $classroom, int $IdStudent): void
    {
        $classroom->students()->detach($IdStudent);
    }
}
