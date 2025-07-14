<?php

namespace App\Services;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\View\View;
use App\Models\{Classroom, Student, Course, Module, Schedule, User, Calendar, VideoCourse, VideoCourseClass};
use Illuminate\Database\Eloquent\Collection;

class StudentService
{
    public static function getAllStudents()
    {
        return Student::all();
    }

    public static function getAllActiveStudents()
    {
        return Student::where('status', 1)->get();
    }

    public static function getStudents(Request $request)
    {
        $student = Student::with('guardian');

        if (isset($request->status)) {
            $student = $student->whereStatus($request->status);
        }

        if (isset($request->id)) {
            $student = $student->whereId($request->id);
        }

        if (isset($request->email)) {
            $student = $student->whereEmail($request->email);
        }

        return $student->get();
    }

    public static function getStudentById(int $studentId = null)
    {
        if ($studentId != null) {
            return Student::findOrFail($studentId);
        } else {
            return null;
        }
    }

    static function getStudentsByClassroom(Classroom $classroom): Collection
    {
        return Student::whereDoesntHave('classrooms', function ($q) use ($classroom) {
            $q->where('classroom_id', $classroom->id);
        })
            ->orderBy('students.full_name')
            ->get();
    }

    public static function getStudentsWithAccessByGuardian(User $guardian): Collection
    {
        return Student::where('guardian_id', $guardian->id)
            ->where(function ($q) {
                $q->whereDate('expires_at', '>', now()->format('Y-m-d'))
                    ->orWhereNull('expires_at');
            })
            ->where('status', true)
            ->get();
    }

    public static function getStudentsByVideoCourse(VideoCourse $videoCourse): Collection
    {
        return Student::whereDoesntHave('videoCourses', function ($q) use ($videoCourse) {
            $q->where('video_courses.id', $videoCourse->id);
        })
            ->orderBy('students.full_name')
            ->get();
    }

    public static function getStudentsByTeacherId(int $teacherId): Collection
    {
        return Student::whereHas('classrooms', function ($query) use ($teacherId) {
            $query->whereHas('courses', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })->isActive();
        })
            ->get();
    }

    static function courseIsDone(Student $student, Course $course): bool
    {
        return $student->courses()->whereIn('course_id', [$course->id])->get()->isNotEmpty();
    }

    static function getCourses(
        ?Student $student,
        int $courseType = 1,
        int $module_id = null,
        int $classroom_id = null
    ): Collection {
        if ($student != null) {
            $courses = Course::whereType($courseType)->whereHas('students', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            });

            if ($classroom_id != null) {
                $courses->whereHas('module', function ($q) use ($classroom_id) {
                    $q->where('classroom_id', $classroom_id);
                });
            }

            if ($module_id != null) {
                $courses->where('module_id', $module_id);
            }

            return $courses->get();
        } else {
            return new Collection();
        }
    }

    public static function getCoursesOfSelectedStudent(int $courseType = 2)
    {
        $student = selectedStudent();

        if ($student != null) {
            $classrooms = $student->classrooms->pluck('id');

            if ($courseType == 2) {
                $courses = Course::whereHas('module', function ($q) use ($classrooms) {
                    $q->whereIn('classroom_id', $classrooms);
                })->whereType('2')
                    ->get();
            } else {
                $courses = Course::whereIn('classroom_id', $classrooms)
                    ->whereType(1)->get();
            }
            return $courses;
        } else {
            return new Collection();
        }
    }

    public static function getGradeOfSelectedStudent($student)
    {
        if ($student != null) {
            $grade = $student->grade_id;

            $calendars = Calendar::whereHas('grades', function ($q) use ($grade) {
                $q->where('grade_id', '=', $grade);
            })->get();

            return ($calendars->count() > 0) ? $calendars : null;
        } else {
            return null;
        }
    }

    public static function getSchedules(Student $student, int $courseType = 1): Collection
    {
        return Schedule::whereStatus(1)->whereHas('course.classroom.students', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })->with('weekday')
            ->get()
            ->unique('weekday.id')
            ->sortBy('weekday.id');
    }

    static function getCoursesWeekdays(Student $student, int $courseType)
    {
        return Self::getSchedules($student, $courseType)->implode('weekday.short_name', ', ');
    }

    public static function getClassroomStatistics(Classroom $classroom): array
    {
        $user = Auth::user();
        $student = null;

        if ($user->is_guardian) {
            $finished_courses = StudentService::getCourses(selectedStudent(), 2, null, $classroom->id)->count();
            $total_courses = ClassroomService::getCoursesByClassroom($classroom, 2)->count();

            return [
                'finished' => $finished_courses,
                'total' => $total_courses,
                'percentage' => intval(($finished_courses / max($total_courses, 1)) * 100)
            ];
        }
    }

    public static function getVideoCourseClassStatistics(VideoCourseClass $class): array
    {
        $user = Auth::user();
        $student = null;

        if ($user->is_guardian) {
            $progress = VideoCourseModuleService::getModuleProgressByStudentId($class->module, selectedStudent()->id);

            return [
                'finished' => $class->viewed,
                'class_id' => $class->id,
                'progress' => $progress
            ];
        }
    }

    public static function getRecentCourses(Student $student, int $take = 10)
    {
        return Course::whereHas('students', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })->take($take)->get();
    }

    public static function countByTeacherId(int $teacherId): int
    {
        return Student::where('status', 1)
            ->whereHas('classrooms', function ($query) use ($teacherId) {
                $query->whereHas('courses', function ($query) use ($teacherId) {
                    $query->where('teacher_id', $teacherId);
                })->isActive();
            })
            ->count();
    }

    public static function storeStudent($data): int
    {
        $data['status'] = 1;
        $data['school_id'] = null;
        $student = Student::create($data);
        if (array_key_exists('classrooms', $data)) {
            $student->classrooms()->syncWithoutDetaching($data['classrooms']);
        }
        if (array_key_exists('courses', $data)) {
            $student->videoCourses()->syncWithoutDetaching($data['courses']);
        }
        return $student->id;
    }

    public static function updateStudent(Request $request, Student $student)
    {
        $user = Auth::user();
        if ($request->filled('student.status')) {
            $status = $request->input('student.status') == 'on' ? true : false;
        } else {
            $status = $student->status;
        }
        $student->update(
            array_merge(
                $request->input('student'),
                ['status' => $status]
            )
        );
        if (!$user->is_guardian) {
            $student->classrooms()->sync(
                $request->input('student.classrooms')
            );
        }
        session()->put('student_avatar_id', $student->avatar_id);
    }

    public static function deleteStudent(Student $student): void
    {
        $student->delete();
    }

    public static function forceDeleteStudent(Student $student): void
    {
        $student->forceDelete();
    }

    public static function restoreStudent(Student $student): void
    {
        $student->restore();
    }
}
