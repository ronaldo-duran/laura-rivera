<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationRequest;
use App\Models\Donation;
use App\Services\Wompi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function __construct(private readonly Wompi $wompi) {}

    public function create(): View
    {
        abort_unless(config('laura.donaciones.activas'), 404);

        return view('donations.create', [
            'enabled' => $this->wompi->isConfigured(),
            'sandbox' => $this->wompi->isSandbox(),
        ]);
    }

    /** Registra la intención de aporte y envía a la persona al checkout de Wompi. */
    public function store(StoreDonationRequest $request): RedirectResponse
    {
        abort_unless(config('laura.donaciones.activas'), 404);

        if (filled($request->input('website'))) {
            return redirect()->route('donations.create');
        }

        abort_unless($this->wompi->isConfigured(), 503, 'Las donaciones en línea no están disponibles en este momento.');

        $data = $request->validated();

        $donation = Donation::create([
            'reference' => Donation::newReference(),
            'amount_in_cents' => $data['amount_value'] * 100,
            'currency' => config('services.wompi.currency'),
            'status' => 'CREATED',
            'full_name' => $data['full_name'],
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'terms_accepted_at' => now(),
            'ip_address' => $request->ip(),
            'source' => $request->session()->get('lr_source'),
        ]);

        $request->session()->put('donation_reference', $donation->reference);

        return redirect()->away($this->wompi->checkoutUrl($donation, route('donations.result')));
    }

    /**
     * Página a la que Wompi devuelve a la persona (?id=transacción).
     * Es informativa: el estado oficial llega por el webhook. Si hay llave privada,
     * consultamos la transacción para mostrar el resultado de inmediato.
     */
    public function result(Request $request): View
    {
        abort_unless(config('laura.donaciones.activas'), 404);

        $donation = null;
        $transactionId = (string) $request->query('id', '');

        if ($transactionId !== '' && ($tx = $this->wompi->fetchTransaction($transactionId))) {
            $donation = Donation::where('reference', $tx['reference'] ?? '')->first();
            if ($donation && isset($tx['status']) && (int) ($tx['amount_in_cents'] ?? 0) === $donation->amount_in_cents) {
                $donation->applyWompiStatus($tx['status'], $tx);
            }
        }

        if (! $donation && ($ref = $request->session()->get('donation_reference'))) {
            $donation = Donation::where('reference', $ref)->first();
        }

        return view('donations.result', ['donation' => $donation?->fresh()]);
    }
}
