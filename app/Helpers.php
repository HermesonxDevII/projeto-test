<?php

use App\Services\StudentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\{Auth, Session, Storage};

if (!function_exists('deleteFile')) {
    function deleteFile($filePath, $disk = 'public')
    {
        if (Storage::disk($disk)->exists($filePath)) {
            Storage::disk($disk)->delete($filePath);
        }
    }
}

if (!function_exists('formattedSize')) {
    function formattedSize($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('formatTime')) {
    function formatTime($time)
    {
        return Carbon::parse($time)->format('g:i A');
    }
}

if (!function_exists('generateVideoEmbedUrl')) {
    function generateVideoEmbedUrl($url)
    {
        $finalUrl = '';

        if (strpos($url, 'vimeo.com/') !== false) {
            // Vimeo
            $videoId = str($url)->basename();
            
            if (strpos($videoId, '&') !== false) {
                $videoId = explode("&", $videoId)[0];
            }
            $finalUrl .= 'https://player.vimeo.com/video/' . $videoId;
        } else if (strpos($url, 'youtube.com/') !== false) {
            // Youtube
            $videoId = explode("v=", $url)[1];
            if (strpos($videoId, '&') !== false) {
                $videoId = explode("&", $videoId)[0];
            }
            $finalUrl .= 'https://www.youtube.com/embed/' . $videoId;
        } else if (strpos($url, 'youtu.be/') !== false) {
            /// Youtube - alternative URL
            $videoId = explode("youtu.be/", $url)[1];
            if (strpos($videoId, '&') !== false) {
                $videoId = explode("&", $videoId)[0];
            }
            $finalUrl .= 'https://www.youtube.com/embed/' . $videoId;
        }

        return $finalUrl;
    }
}

if (!function_exists('notify')) {
    function notify($message = '', $icon = 'success')
    {
        Session::flash('notification', [
            'msg'  => $message,
            'icon' => $icon
        ]);
    }
}

if (!function_exists('loggedUser')) {
    function loggedUser()
    {
        return Auth::user();
    }
}

if (!function_exists('selectedStudent')) {
    function selectedStudent()
    {
        if (!loggedUser()->is_guardian) {
            return null;
        }

        $selectedStudent = session()->get('student_id');

        if ($selectedStudent != null) {
            return StudentService::getStudentById($selectedStudent);
        }

        $students = StudentService::getStudentsWithAccessByGuardian(loggedUser());

        if ($students->count() === 1) {
            $student = $students->first();

            session()->put('student_id', $student->id);
            session()->put('student_name', $student->firstName);
            session()->put('student_nickname', $student->nickname);
            session()->put('student_avatar_id', $student->avatar_id);

            return $student;
        }

        return null;
    }
}

if (!function_exists('hasCalendar')) {
    function hasCalendar($student_id)
    {
        $student = StudentService::getStudentById($student_id);
        if ($student != null){
            $calendars = StudentService::getGradeOfSelectedStudent($student);
            return $calendars ? true : false;
        } else {
            return false;
        }
    }
}

if (!function_exists('getAppVersion')) {
    function getAppVersion()
    {
        return env('APP_VERSION', 1);
    }
}

if (!function_exists('getNextClassDate')) {
    function getNextClassDate($weekday_number)
    {
        $dayIndex = [
            'Sunday'=>0,
            'Monday'=>1,
            'Tuesday'=>2,
            'Wednesday'=>3,
            'Thursday'=>4,
            'Friday'=>5,
            'Saturday'=>6
        ];
        $dayWeek = array_search($weekday_number, $dayIndex);
        if(isset($dayWeek)){
            return date('d/m/Y',strtotime("next $dayWeek"));
        }
        return false;
    }
}

if (!function_exists('sumTimesFromArray')) {
    function sumTimesFromArray(array $times): string
    {
        $sum = strtotime('00:00:00');
        
        $totaltime = 0;
        
        foreach($times as $time) {
            $time = $time === null ? '00:00:00' : $time;

            // Converting the time into seconds
            $timeinsec = strtotime($time) - $sum;
            
            // Sum the time with previous value
            $totaltime = $totaltime + $timeinsec;
        }
        
        // Totaltime is the summation of all
        // time in seconds
        
        // Hours is obtained by dividing
        // totaltime with 3600
        $h = intval($totaltime / 3600);
        
        $totaltime = $totaltime - ($h * 3600);
        
        // Minutes is obtained by dividing
        // remaining total time with 60
        $m = intval($totaltime / 60);
        
        if ($h < 1) {
            return "{$m}m";
        }

        if ($m === 0) {
            return "{$h}h";
        }
        
        return "{$h}h{$m}m";
    }
}

if (!function_exists('formatTextWithHyperlinks')) {
    function formatTextWithHyperlinks(string $str, bool $external = true): string
    {
        $text = \Illuminate\Support\Str::markdown($str ?? '', [
            'html_input' => 'strip'
        ]);

        if ($external) {
            $text = preg_replace('/(<a\b[^><]*)>/i', '$1 target="_blank" rel="noopener noreferrer">', $text);
        }

        return $text;
    }
}

if (!function_exists('getLanguages')) {
    function getLanguages(): Collection
    {
        return \App\Models\Language::all();
    }
}

if (!function_exists('getAuthenticatedParticipant')) {
    function getAuthenticatedParticipant()
    {
        if (session()->has('student_id')) {
            return \App\Models\Student::find(session('student_id'));
        }

        if (auth()->check()) {
            return auth()->user();
        }
    
        return null;
    }
}

if (!function_exists('getReturnURL')) {
    function getReturnURL(): string
    {
        $baseUrl = config('app.plataform_url', 'http://localhost:80/chats');

        $baseUrl = rtrim($baseUrl, '/');

        return $baseUrl;
    }
}

if (!function_exists('translateDate')) {
    function translateDate(string $date): string
    {
        $dayIndex = [
            'sunday'    => 'Domingo',
            'monday'    => 'Segunda-Feira',
            'tuesday'   => 'Terça-Feira',
            'wednesday' => 'Quarta-Feira',
            'thursday'  => 'Quinta-Feira',
            'friday'    => 'Sexta-Feira',
            'saturday'  => 'Sábado'
        ];

        $lowerDate = strtolower($date);

        if (isset($dayIndex[$lowerDate])) {
            return $dayIndex[$lowerDate];
        }

        return $date;
    }
}