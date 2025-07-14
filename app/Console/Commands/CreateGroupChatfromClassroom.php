<?php

namespace App\Console\Commands;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Console\Command;

class CreateGroupChatfromClassroom extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-group-chatfrom-classroom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $turmas = Classroom::withoutTrashed()->get();
        $adms = User::whereHas('roles', function($query) {
            $query->where('roles.id', 1);
        })->get();
        $userAdm = User::where('email','contato@smartlead.com.br')->first();
    
        
        foreach($turmas as $turma) {
            
            $conversation = $userAdm->createGroup(
                name: $turma->name,
                description: $turma->description,
                classroom_id: $turma->id
            );
    
            $studentsParticipants = $turma->students()->get();
    
            foreach ($studentsParticipants as $student) {
                $alreadyExists = $conversation->participants()->where('participantable_id', $student->id)->where('participantable_type', $student->getMorphClass())->exists();
                if (! $alreadyExists) {
                    $conversation->addParticipant($student);
                }
            }
    
            $courses = $turma->liveCourses;
    
            foreach ($courses as $course) {
                if($course->teacher) {
                    $alreadyExists = $conversation->participants()->where('participantable_id', $course->teacher->id)->where('participantable_type', $course->teacher->getMorphClass())->exists();
                    if (! $alreadyExists) {
                        $conversation->addParticipant($course->teacher, \Namu\WireChat\Enums\ParticipantRole::ADMIN);
                    }
                }
               
            }
    
            foreach($adms as $adm) {
                $alreadyExists = $conversation->participants()->where('participantable_id', $adm->id)->where('participantable_type', $adm->getMorphClass())->exists();
                if (! $alreadyExists) {
                    $conversation->addParticipant($adm, \Namu\WireChat\Enums\ParticipantRole::ADMIN);
                }
            }
    
        }
    
        $this->info('Grupos de turmas criados com sucesso!'); // Exibe mensagem no terminal

        return Command::SUCCESS; // Equivale a return 0
    }
}
