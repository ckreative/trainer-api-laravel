<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBrandingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    /**
     * Get the authenticated user's branding settings.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'handle' => $user->handle,
            'brandName' => $user->brand_name,
            'primaryColor' => $user->primary_color ?? '#D6FF00',
            'heroImageUrl' => $user->hero_image_url,
        ]);
    }

    /**
     * Update the authenticated user's branding settings.
     */
    public function update(UpdateBrandingRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $updateData = [];

        if (isset($validated['handle'])) {
            $updateData['handle'] = $validated['handle'];
        }
        if (isset($validated['brandName'])) {
            $updateData['brand_name'] = $validated['brandName'];
        }
        if (isset($validated['primaryColor'])) {
            $updateData['primary_color'] = $validated['primaryColor'];
        }
        if (array_key_exists('heroImageUrl', $validated)) {
            $updateData['hero_image_url'] = $validated['heroImageUrl'];
        }

        $user->update($updateData);

        return response()->json([
            'handle' => $user->handle,
            'brandName' => $user->brand_name,
            'primaryColor' => $user->primary_color ?? '#D6FF00',
            'heroImageUrl' => $user->hero_image_url,
            'message' => 'Branding updated successfully',
        ]);
    }
}
