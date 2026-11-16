<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Support\CsvExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DonationController extends Controller
{
    public function index(Request $request): View
    {
        $filtered = $this->filtered($request);

        return view('admin.donations.index', [
            'donations' => (clone $filtered)->latest()->paginate(25)->withQueryString(),
            'filters' => $request->only(['q', 'estado', 'desde', 'hasta']),
            'approvedTotal' => (int) (clone $filtered)->approved()->sum('amount_in_cents') / 100,
            'approvedCount' => (clone $filtered)->approved()->count(),
            'allApprovedTotal' => (int) Donation::approved()->sum('amount_in_cents') / 100,
        ]);
    }

    public function show(Donation $donation): View
    {
        return view('admin.donations.show', compact('donation'));
    }

    public function export(Request $request): StreamedResponse
    {
        return CsvExport::download($this->filtered($request), [
            'Fecha' => 'created_at',
            'Fecha de pago' => 'paid_at',
            'Referencia' => 'reference',
            'Estado' => fn ($d) => $d->status_label,
            'Monto (COP)' => fn ($d) => $d->amount,
            'Medio de pago' => 'payment_method_type',
            'Transacción Wompi' => 'wompi_transaction_id',
            'Nombre' => 'full_name',
            'Tipo de documento' => 'document_type',
            'Número de documento' => 'document_number',
            'Correo' => 'email',
            'Celular' => 'phone',
        ], 'donaciones-'.now()->format('Y-m-d').'.csv');
    }

    private function filtered(Request $request): Builder
    {
        $q = trim((string) $request->query('q'));

        return Donation::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('document_number', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('reference', 'like', "%{$q}%")))
            ->when($request->query('estado'), fn ($query, $s) => $query->where('status', $s))
            ->when($this->date($request->query('desde')), fn ($query, $d) => $query->where('created_at', '>=', $d->startOfDay()))
            ->when($this->date($request->query('hasta')), fn ($query, $d) => $query->where('created_at', '<=', $d->endOfDay()));
    }

    private function date(?string $value): ?Carbon
    {
        try {
            return $value ? Carbon::createFromFormat('Y-m-d', $value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
