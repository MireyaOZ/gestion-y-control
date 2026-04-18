<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesManagedModels;
use App\Models\Attachment;
use App\Models\Subtask;
use App\Models\SystemRecord;
use App\Models\Task;
use App\Services\ChangeLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use ResolvesManagedModels;

    public function store(Request $request, string $type, string $id): RedirectResponse
    {
        $model = $this->resolveOwnedModel($type, $id);
        $this->authorizeAction($model);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $data['file'];
        $path = $file->store("attachments/{$type}", 'public');

        $model->attachments()->create([
            'uploaded_by' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        if ($model instanceof SystemRecord) {
            $model->load('status');

            ChangeLogger::log(
                $model,
                'attachment_added',
                '<div data-status-group="'.e($model->status?->name ?? 'Sin estatus').'"><p>Adjunto agregado por '.e($request->user()->name).'.</p><div><strong>Archivo agregado:</strong><ul style="margin:6px 0 0 18px;list-style:disc;"><li>'.e($file->getClientOriginalName()).' <a href="'.e(asset('storage/'.$path)).'" target="_blank" style="color:#960018;text-decoration:underline;">Abrir archivo</a></li></ul></div></div>'
            );
        }

        return back()->with('status', 'Adjunto cargado.');
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorizeAction($attachment->attachable);

        $attachable = $attachment->attachable;

        if ($attachable instanceof SystemRecord) {
            $attachable->load('status');

            ChangeLogger::log(
                $attachable,
                'attachment_deleted',
                '<div data-status-group="'.e($attachable->status?->name ?? 'Sin estatus').'"><p>Adjunto eliminado por '.e(request()->user()?->name ?? 'Sistema').'.</p><div><strong>Archivo eliminado:</strong><ul style="margin:6px 0 0 18px;list-style:disc;"><li>'.e($attachment->original_name).'</li></ul></div></div>'
            );
        }

        $attachment->delete();

        return back()->with('status', 'Adjunto eliminado.');
    }

    protected function authorizeAction(Task|Subtask|SystemRecord $model): void
    {
        if ($model instanceof Task) {
            $this->authorize('update', $model);
        } elseif ($model instanceof SystemRecord) {
            abort_unless(request()->user()?->can('systems.update'), 403);
        } else {
            $this->authorize('update', $model);
        }
    }
}
