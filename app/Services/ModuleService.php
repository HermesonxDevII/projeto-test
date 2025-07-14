<?php

namespace App\Services;

use App\Models\{Classroom, Schedule, Course, Module};
use Illuminate\Database\Eloquent\Collection;

class ModuleService
{
    static public function getAllModules(): Collection
    {
        return Module::all();
    }

    static public function getModuleById(int $module_id): Module
    {
        return Module::findOrFail($module_id);
    }

    static public function getModulesByClassroom(Classroom $classroom)
    {
        return Module::where('classroom_id', $classroom->id)->get();
    }

    static public function storeModule(array $requestData): int
    {
        $module = Module::create($requestData);
        return $module->id;
    }
    
    static public function updateModule(array $requestData, Module $module): void
    {
        $module->update($requestData);
    }

    public static function deleteModule(Module $module)
    { 
        $module->courses()->delete();
        $module->delete();
    }

    public static function forceDeleteModule(Module $module)
    {
        $module->forceDelete();
    }

    public static function restoreModule(Module $module)
    {
        $module->restore();
    }
}
