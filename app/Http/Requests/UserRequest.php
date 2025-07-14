<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->status;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user_id = $this->id ? $this->id : null;

        $rules = [
            'name' => ['required', 'string'],
            //'email' => ['email', 'unique:users,email,' . $user_id . ',id'],
            'email' => [
                'required',
                Rule::unique('users')->ignore($user_id, 'id')->whereNull('deleted_at')
            ],
            'phone_number' => ['required', 'min:10'],
            'profile_photo' => ['mimes:jpg,jpeg,png', 'max:2048'],
        ];

        if ($this->method() === 'PUT')
        {
            if ($this->filled('password'))
            {
                $rules['password'] = ['required', 'confirmed'];
                $rules['password_confirmation'] = ['required'];
            }
        } else if ($this->method() === 'POST')
        {
            $rules['password'] = ['required', 'confirmed'];
            $rules['password_confirmation'] = ['required'];
            $rules['role_id'] = ['required', 'between:1,3'];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if (loggedUser()->is_teacher && loggedUser()->id === (int)$this->id) {
            $this->merge([
                'name' => loggedUser()->name,
                'email' => loggedUser()->email
            ]);
        }
    }

    public function messages()
    {
        $messages = [
            'name.required' => 'Este campo é obrigatório.',
            'name.string' => 'Este campo deve conter apenas letras.',
            'email.email' => 'O campo E-mail deve ser válido',
            'email.unique' => 'Este E-mail não está disponível.',
            'phone_number.required' => 'Este campo é obrigatório.',
            'phone_number.regex' => 'O campo Telefone deve ser válido.',
            'phone_number.min' => 'Este campo deve conter no mínimo 10 números.',
            'status.required' => 'O campo Status é obrigatório.',
            'status.boolean' => 'O campo Status deve ser Ativado ou Desativado.',
            'profile_photo.mimes' => 'A Imagem deve ser no formato jpg, jpeg ou png.',
            'profile_photo.max' => 'O Imagem deve conter no máximo 2mb.',
            'role_id.required' => 'O Perfil é obrigatória.',
            'role_id.between' => 'O Perfil deve ser válida.',
            'password.required' => 'A Senha é obrigatória.',
            'password.confirmed' => 'A Confirmação Senha não coincide.',
            'password_confirmation.required' => 'A confirmação da senha é obrigatória.'
        ];

        return $messages;
    }
}
