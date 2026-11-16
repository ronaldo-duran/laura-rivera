<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVolunteerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Limpia los datos antes de validar: puntos en la cédula, espacios en el celular, etc. */
    protected function prepareForValidation(): void
    {
        $clean = fn ($v) => is_string($v) ? trim(preg_replace('/\s+/u', ' ', $v)) : $v;

        $phone = preg_replace('/[^\d+]/', '', (string) $this->input('phone'));
        if (preg_match('/^(\+?57)(3\d{9})$/', $phone, $m)) {
            $phone = $m[2]; // celular colombiano sin indicativo
        }

        $this->merge([
            'document_type' => Str::upper((string) $this->input('document_type')),
            'document_number' => Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $this->input('document_number'))),
            'first_names' => $clean($this->input('first_names')),
            'last_names' => $clean($this->input('last_names')),
            'preferred_name' => $clean($this->input('preferred_name')) ?: null,
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => $phone,
        ]);
    }

    public function rules(): array
    {
        $numericDocs = ['CC', 'TI'];

        return [
            'document_type' => ['required', Rule::in(array_keys(config('laura.tipos_documento')))],
            'document_number' => [
                'required', 'string',
                Rule::when(in_array($this->input('document_type'), $numericDocs, true), ['regex:/^\d{5,11}$/'], ['regex:/^[A-Z0-9]{4,20}$/']),
                Rule::unique('volunteers')->where('document_type', $this->input('document_type')),
            ],
            'first_names' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\'.-]+$/u'],
            'last_names' => ['required', 'string', 'max:100', 'regex:/^[\pL\s\'.-]+$/u'],
            'preferred_name' => ['nullable', 'string', 'max:60'],
            'treatment' => ['required', Rule::in(array_keys(config('laura.tratamientos')))],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'regex:/^(3\d{9}|\+\d{8,15})$/'],
            'neighborhood' => ['nullable', Rule::in(config('laura.barrios'))],
            'help_type' => ['nullable', Rule::in(config('laura.tipos_voluntariado'))],
            'accept_terms' => ['accepted'],
            'website' => ['nullable', 'max:0'], // trampa anti-bots (campo oculto)
        ];
    }

    public function messages(): array
    {
        return [
            'document_number.unique' => 'Este documento ya está registrado como voluntario. ¡Gracias por su apoyo!',
            'document_number.regex' => 'Revise el número de documento.',
            'phone.regex' => 'Escriba un celular colombiano de 10 dígitos (p. ej. 3001234567) o un número internacional con +.',
            'first_names.regex' => 'Los nombres solo pueden contener letras.',
            'last_names.regex' => 'Los apellidos solo pueden contener letras.',
            'accept_terms.accepted' => 'Debe aceptar los términos y condiciones y la política de privacidad.',
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'first_names' => 'nombres',
            'last_names' => 'apellidos',
            'preferred_name' => 'nombre preferido',
            'treatment' => 'cómo nos referimos a usted',
            'email' => 'correo',
            'phone' => 'celular',
            'neighborhood' => 'barrio o vereda',
            'help_type' => 'tipo de apoyo',
        ];
    }

    /** Al fallar, vuelve al formulario (no al tope de la página). */
    protected function getRedirectUrl()
    {
        return strtok(url()->previous(), '#').'#registro';
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }

    public function withValidator(Validator $validator): void
    {
        // Si es un bot, no mostramos errores: el controlador finge éxito.
        if ($this->isBot()) {
            $validator->setRules([]);
        }
    }
}
