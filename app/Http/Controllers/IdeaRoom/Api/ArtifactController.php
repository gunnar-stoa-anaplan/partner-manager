<?php

namespace App\Http\Controllers\IdeaRoom\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdeaRoom\StoreArtifactRequest;
use App\Http\Requests\IdeaRoom\UpdateArtifactRequest;
use App\Http\Resources\IdeaRoom\ArtifactResource;
use App\Models\IdeaRoom\Artifact;
use App\Models\IdeaRoom\ArtifactVersion;
use App\Models\IdeaRoom\Idea;
use App\Models\IdeaRoom\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArtifactController extends Controller
{
    public function storeForIdea(StoreArtifactRequest $request, Idea $idea): JsonResponse
    {
        abort_unless($idea->user_id === $request->user()->id, 403);

        $artifact = $this->createArtifact($request, $idea->id, null, $request->user()->id);

        return response()->json(new ArtifactResource($artifact), 201);
    }

    public function storeForRoom(StoreArtifactRequest $request, Room $room): JsonResponse
    {
        abort_unless($room->user_id === $request->user()->id, 403);

        $artifact = $this->createArtifact($request, $room->idea_id, $room->id, $request->user()->id);

        return response()->json(new ArtifactResource($artifact), 201);
    }

    public function show(Request $request, Artifact $artifact): ArtifactResource
    {
        abort_unless($artifact->user_id === $request->user()->id, 403);

        $artifact->load('versions');

        return new ArtifactResource($artifact);
    }

    public function download(Request $request, Artifact $artifact): StreamedResponse
    {
        abort_unless($artifact->user_id === $request->user()->id, 403);
        abort_unless($artifact->file_path, 404);

        return Storage::download($artifact->file_path, $artifact->title);
    }

    public function update(UpdateArtifactRequest $request, Artifact $artifact): ArtifactResource
    {
        abort_unless($artifact->user_id === $request->user()->id, 403);

        ArtifactVersion::query()->create([
            'artifact_id' => $artifact->id,
            'version' => $artifact->version,
            'content' => $artifact->content,
            'file_path' => $artifact->file_path,
            'changed_by_type' => 'user',
            'changed_by_id' => $request->user()->id,
        ]);

        $artifact->update([
            ...$request->validated(),
            'version' => $artifact->version + 1,
        ]);

        return new ArtifactResource($artifact);
    }

    public function destroy(Request $request, Artifact $artifact): JsonResponse
    {
        abort_unless($artifact->user_id === $request->user()->id, 403);

        $artifact->delete();

        return response()->json(null, 204);
    }

    private function createArtifact(StoreArtifactRequest $request, int $ideaId, ?int $roomId, int $userId): Artifact
    {
        $data = [
            'idea_id' => $ideaId,
            'idea_room_id' => $roomId,
            'user_id' => $userId,
            'type' => $request->validated('type'),
            'title' => $request->validated('title'),
            'content' => $request->validated('content'),
            'metadata' => $request->validated('metadata'),
            'version' => 1,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('artifacts', 'local');
            $data['file_mime'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $artifact = Artifact::query()->create($data);

        ArtifactVersion::query()->create([
            'artifact_id' => $artifact->id,
            'version' => 1,
            'content' => $artifact->content,
            'file_path' => $artifact->file_path,
            'changed_by_type' => 'user',
            'changed_by_id' => $userId,
        ]);

        return $artifact;
    }
}
