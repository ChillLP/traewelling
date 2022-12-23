<?php


namespace App\Http\Controllers\API\v1;

use App\Enum\StatusVisibility;
use App\Http\Controllers\Backend\Transport\StatusTagController as StatusTagBackend;
use App\Http\Resources\StatusTagResource;
use App\Models\Status;
use App\Models\StatusTag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class StatusTagController extends Controller
{

    /**
     * @OA\Get(
     *      path="/statuses/{statusId}/tags",
     *      operationId="getTagsForStatus",
     *      tags={"Status"},
     *      summary="Show all tags for a status which are visible for the current user",
     *      description="Returns a collection of all visible tags for the given status, if user is authorized to see
     *      it",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to access this status"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * Show all tags for a status which are visible for the current user
     *
     * @param int $statusId
     *
     * @return JsonResponse
     */
    public function index(int $statusId): JsonResponse {
        $status = Status::find($statusId);
        if ($status === null) {
            return $this->sendError(
                error: 'No status found for this id',
            );
        }
        return $this->sendResponse(
            data: StatusTagResource::collection(StatusTagBackend::getVisibleTagsForUser($status, \auth()->user())),
        );
    }

    /**
     * @OA\Put(
     *      path="/statuses/{statusId}/tags/{tagKey}",
     *      operationId="updateSingleStatusTag",
     *      tags={"Status"},
     *      summary="Update a StatusTag",
     *      description="Updates a single StatusTag Object, if user is authorized to",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter (
     *          name="tagKey",
     *          in="path",
     *          description="Key of StatusTag",
     *          example=seat,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to manipulate this status"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * @param Request $request
     * @param int     $statusId
     * @param string  $tagKey
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, int $statusId, string $tagKey): JsonResponse {
        $validator = Validator::make($request->all(), [
            'key'   => ['nullable', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->sendError(error: $validator->errors(), code: 400);
        }
        $validated = $validator->validate();

        $status = Status::find($statusId);
        if ($status === null || $status->tags->where('key', $tagKey)->count() === 0) {
            return $this->sendError(
                error: 'No StatusTag found for the given arguments',
            );
        }
        try {
            $statusTag = $status->tags->where('key', $tagKey)->first();
            $this->authorize('update', $statusTag);
            $statusTag->update($validated);
            return $this->sendResponse(data: new StatusTagResource($statusTag));
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this StatusTag',
            );
        }
    }


    /**
     * @OA\Post(
     *      path="/statuses/{statusId}/tags",
     *      operationId="createSingleStatusTag",
     *      tags={"Status"},
     *      summary="Create a StatusTag",
     *      description="Creates a single StatusTag Object, if user is authorized to",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation"
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id"),
     *       @OA\Response(response=403, description="User not authorized to manipulate this status"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * @param Request $request
     * @param int     $statusId
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request, int $statusId): JsonResponse {
        $validator = Validator::make($request->all(), [
            'key'        => ['required', 'string', 'max:255'],
            'value'      => ['required', 'string', 'max:255'],
            'visibility' => ['required', new Enum(StatusVisibility::class)],
        ]);

        if ($validator->fails()) {
            return $this->sendError(error: $validator->errors(), code: 400);
        }
        $validated = $validator->validate();

        $status = Status::find($statusId);
        if ($status === null) {
            return $this->sendError(
                error: 'No Status found for the given arguments',
            );
        }

        if ($status->tags->where('key', $validated['key'])->count() > 0) {
            return $this->sendError(
                error: 'StatusTag with this key already exists',
                code:  400,
            );
        }
        try {
            $this->authorize('update', $status);
            $validated['status_id'] = $status->id;
            $statusTag              = StatusTag::create($validated);
            return $this->sendResponse(data: new StatusTagResource($statusTag));
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this Status',
            );
        }
    }

    /**
     * @OA\Delete(
     *      path="/statuses/{statusId}/tags/{tagKey}",
     *      operationId="destroySingleStatusTag",
     *      tags={"Status"},
     *      summary="Destroy a StatusTag",
     *      description="Deletes a single StatusTag Object, if user is authorized to",
     *      @OA\Parameter (
     *          name="statusId",
     *          in="path",
     *          description="Status-ID",
     *          example=1337,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter (
     *          name="tagKey",
     *          in="path",
     *          description="Key of StatusTag",
     *          example=seat,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *                      ref="#/components/schemas/SuccessResponse"
     *          )
     *       ),
     *       @OA\Response(response=400, description="Bad request"),
     *       @OA\Response(response=404, description="No status found for this id and statusId"),
     *       @OA\Response(response=403, description="User not authorized to manipulate this status"),
     *       security={
     *           {"token": {}},
     *           {}
     *       }
     *     )
     *
     * @param int $statusId
     * @param int $tag
     *
     * @return JsonResponse
     */
    public function destroy(int $statusId, string $tagKey): JsonResponse {
        $status = Status::find($statusId);
        if ($status === null || $status->tags->where('key', $tagKey)->count() === 0) {
            return $this->sendError(
                error: 'No StatusTag found for the given arguments',
            );
        }
        try {
            $statusTag = $status->tags->where('key', $tagKey)->first();
            $this->authorize('destroy', $statusTag);
            $statusTag->delete();
            return $this->sendResponse();
        } catch (AuthorizationException) {
            return $this->sendError(
                error: 'User not authorized to manipulate this StatusTag',
            );
        }
    }
}
