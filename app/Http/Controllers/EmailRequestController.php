<?php

namespace App\Http\Controllers;

use App\Models\EmailMovementType;
use App\Models\EmailRequest;
use App\Services\ChangeLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailRequestController extends Controller
{
    private const CHANGE_ARROW = '<span style="color:#2563eb;font-weight:700;">&rarr;</span>';

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('emails.view'), 403);

        $search = (string) $request->string('search');

        $emailRequests = EmailRequest::query()
            ->with(['movementType', 'links', 'changeLogs.author'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('movementType', fn ($movementTypeQuery) => $movementTypeQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $movementTypes = EmailMovementType::query()->orderBy('name')->get();

        return view('emails.index', compact('emailRequests', 'movementTypes', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('emails.create'), 403);

        $data = $this->validatedData($request);

        $emailRequest = EmailRequest::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_movement_type_id' => $data['email_movement_type_id'],
            'created_by' => $request->user()->id,
        ]);

        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);

        $content = "<p>Solicitud de correo registrada por {$request->user()->name}.</p>"
            ."<p><strong>Nombre:</strong> {$emailRequest->name}</p>"
            ."<p><strong>Correo:</strong> {$emailRequest->email}</p>"
            ."<p><strong>Tipo de movimiento:</strong> {$emailRequest->movementType->name}</p>";

        if (! empty($data['link'])) {
            $content .= "<p><strong>Link:</strong> {$data['link']}</p>";
        }

        ChangeLogger::log($emailRequest, 'created', $content);

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo creada correctamente.');
    }

    public function update(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        abort_unless($request->user()->can('emails.update'), 403);

        $data = $this->validatedData($request);

        $originalName = $emailRequest->name;
        $originalEmail = $emailRequest->email;
        $originalMovementType = $emailRequest->movementType->name;
        $originalLink = $emailRequest->links->first()?->url;

        $emailRequest->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_movement_type_id' => $data['email_movement_type_id'],
        ]);

        $emailRequest->load('movementType', 'links');
        $this->syncLink($emailRequest, $data['link'] ?? null, $request->user()->id);
        $emailRequest->load('links');

        $changes = [];

        if ($originalName !== $emailRequest->name) {
            $changes[] = "<p><strong>Nombre:</strong> {$originalName} ".self::CHANGE_ARROW." {$emailRequest->name}</p>";
        }

        if ($originalEmail !== $emailRequest->email) {
            $changes[] = "<p><strong>Correo:</strong> {$originalEmail} ".self::CHANGE_ARROW." {$emailRequest->email}</p>";
        }

        if ($originalMovementType !== $emailRequest->movementType->name) {
            $changes[] = "<p><strong>Tipo de movimiento:</strong> {$originalMovementType} ".self::CHANGE_ARROW." {$emailRequest->movementType->name}</p>";
        }

        $newLink = $emailRequest->links->first()?->url;
        if ($originalLink !== $newLink) {
            $changes[] = '<p><strong>Link:</strong> '.($originalLink ?: 'Sin link').' '.self::CHANGE_ARROW.' '.($newLink ?: 'Sin link').'</p>';
        }

        if ($changes !== []) {
            ChangeLogger::log(
                $emailRequest,
                'updated',
                "<p>Solicitud de correo actualizada por {$request->user()->name}.</p>".implode('', $changes)
            );
        }

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo actualizada correctamente.');
    }

    public function destroy(Request $request, EmailRequest $emailRequest): RedirectResponse
    {
        abort_unless($request->user()->can('emails.delete'), 403);

        ChangeLogger::log(
            $emailRequest,
            'deleted',
            "<p>Solicitud de correo eliminada por {$request->user()->name}.</p>"
            ."<p><strong>Nombre:</strong> {$emailRequest->name}</p>"
            ."<p><strong>Correo:</strong> {$emailRequest->email}</p>"
            ."<p><strong>Tipo de movimiento:</strong> {$emailRequest->movementType->name}</p>"
        );

        $emailRequest->links()->delete();
        $emailRequest->comments()->delete();
        $emailRequest->attachments()->delete();
        $emailRequest->changeLogs()->delete();
        $emailRequest->delete();

        return redirect()->route('emails.index')->with('status', 'Solicitud de correo eliminada correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'email_movement_type_id' => ['required', 'exists:email_movement_types,id'],
            'link' => ['nullable', 'url', 'max:2048'],
        ]);
    }

    protected function syncLink(EmailRequest $emailRequest, ?string $url, int $userId): void
    {
        $currentLink = $emailRequest->links()->first();

        if (blank($url)) {
            if ($currentLink) {
                $currentLink->delete();
            }

            return;
        }

        if ($currentLink) {
            $currentLink->update([
                'label' => 'Solicitud de '.$emailRequest->name,
                'url' => $url,
            ]);

            return;
        }

        $emailRequest->links()->create([
            'label' => 'Solicitud de '.$emailRequest->name,
            'url' => $url,
            'created_by' => $userId,
        ]);
    }
}