<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\IssueApiToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IssueTokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class AuthTokenController extends Controller
{
    public function store(IssueTokenRequest $request, IssueApiToken $issueApiToken): JsonResponse
    {
        $token = $issueApiToken(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->string('device_name')->toString(),
        );

        return response()->json(['token' => $token], 201);
    }

    public function destroy(Request $request): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $user->currentAccessToken()->delete();
        }

        return response()->noContent();
    }
}
