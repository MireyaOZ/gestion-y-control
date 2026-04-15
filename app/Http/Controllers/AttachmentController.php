<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesManagedModels;
use App\Models\Attachment;
use App\Models\Subtask;
use App\Models\SystemRecord;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        return back()->with('status', 'Adjunto cargado.');
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $this->authorizeAction($attachment->attachable);

        Storage::disk($attachment->disk)->delete($attachment->path);
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
