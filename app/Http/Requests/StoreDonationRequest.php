<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // "Otro monto" escrito como 150.000 o $150,000 → 150000
        $amount = $this->input('amount') === 'otro' ? $this->input('custom_amount') : $this->input('amount');
        $amount = preg_replace('/\D/', '', (string) $amount);

        $phone = preg_replace('/[^\d+]/', '', (string) $this->input('phone'));
        if (preg_match('/^(\+?57)(3\d{9})$/', $phone, $m)) {
            $phone = $m[2];
        }

        $this->merge([
            'amount_value' => $amount === '' ? null : (int) $amount,
            'full_name' => trim(preg_replace('/\s+/u', ' ', (string) $this->input('full_name'))),
            'document_type' => Str::upper((string) $this->input('document_type')),
            'document_number' => Str::upper(preg_replace('/[^A-Za-z0-9]/', '', (string) $this->input('document_number'))),
            'email' => Str::lower(trim((string) $this->input('email'))),
            'phone' => $phone ?: null,
        ]);
    }

    public function rules(): array
    {
        $min = config('services.wompi.min_amount');
        $max = config('services.wompi.max_amount');

        return [
            'amount_value' => ['required', 'integer', "min:{$min}", "max:{$max}"],
            'full_name' => ['required', 'string', 'max:150', 'regex:/^[\pL\s\'.-]+$/u'],
            'document_type' => ['required', Rule::in(array_keys(config('laura.tipos_documento')))],
            'document_number' => ['required', 'string', Rule::when(
                in_array($this->input('document_type'), ['CC', 'TI'], true),
                ['regex:/^\d{5,11}$/'], ['regex:/^[A-Z0-9]{4,20}$/']
            )],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'regex:/^(3\d{9}|\+\d{8,15})$/'],
            'accept_terms' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        $fmt = fn ($v) => '$'.number_format($v, 0, ',', '.');

        return [
            'amount_value.required' => 'Elija o escriba un monto.',
            'amount_value.min' => 'El aporte mínimo es '.$fmt(config('services.wompi.min_amount')).'.',
            'amount_value.max' => 'El aporte máximo en línea es '.$fmt(config('services.wompi.max_amount')).'.',
            'document_number.regex' => 'Revise el número de documento.',
            'phone.regex' => 'Escriba un celular colombiano de 10 dígitos o un número internacional con +.',
            'full_name.regex' => 'El nombre solo puede contener letras.',
            'accept_terms.accepted' => 'Debe aceptar los términos y condiciones y la política de privacidad.',
        ];
    }

    public function attributes(): array
    {
        return [
            'amount_value' => 'monto',
            'full_name' => 'nombre completo',
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'email' => 'correo',
            'phone' => 'celular',
        ];
    }

    protected function getRedirectUrl()
    {
        return route('donations.create').'#aporte';
    }
}
