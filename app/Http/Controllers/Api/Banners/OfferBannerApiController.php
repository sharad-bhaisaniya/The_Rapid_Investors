<?php

namespace App\Http\Controllers\Api\Banners;

use App\Http\Controllers\Controller;
use App\Models\OfferBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OfferBannerApiController extends Controller
{
    /* ===================== LIST ===================== */
    public function index()
    {
        $banners = OfferBanner::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $banners->map(fn ($b) => $this->transform($b))
        ]);
    }

    /* ===================== SINGLE (EDIT) ===================== */
    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->transform(
                OfferBanner::findOrFail($id)
            )
        ]);
    }

    /* ===================== CREATE ===================== */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($request->heading);
        $data['created_by'] = Auth::id();

        $banner = OfferBanner::create($data);

        $this->handleMedia($request, $banner);

        return response()->json([
            'success' => true,
            'message' => 'Offer banner created',
            'data' => $this->transform($banner)
        ], 201);
    }

    /* ===================== UPDATE ===================== */
    public function update(Request $request, $id)
    {
        $banner = OfferBanner::findOrFail($id);

        $data = $this->validateData($request);
        $data['slug'] = Str::slug($request->heading);
        $data['updated_by'] = Auth::id();

        $banner->update($data);

        $this->handleMedia($request, $banner);

        return response()->json([
            'success' => true,
            'message' => 'Offer banner updated',
            'data' => $this->transform($banner)
        ]);
    }

    /* ===================== DELETE ===================== */
    public function destroy($id)
    {
        $banner = OfferBanner::findOrFail($id);

        $banner->clearMediaCollection('offer_banner_desktop');
        $banner->clearMediaCollection('offer_banner_mobile');
        $banner->clearMediaCollection('offer_banner_thumbnail');

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Offer banner deleted'
        ]);
    }

    /* ===================== TOGGLE ACTIVE ===================== */
    public function toggle($id)
    {
        $banner = OfferBanner::findOrFail($id);
        $banner->is_active = ! $banner->is_active;
        $banner->save();

        return response()->json([
            'success' => true,
            'is_active' => $banner->is_active
        ]);
    }

    /* ===================== VALIDATION ===================== */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'heading'          => 'required|string|max:255',
            'sub_heading'      => 'nullable|string|max:255',
            'content'          => 'nullable|string',
            'highlight_text'   => 'nullable|string',

            'button1_text'     => 'nullable|string|max:100',
            'button1_link'     => 'nullable|string|max:500',
            'button1_target'   => 'nullable|string|max:20',

            'button2_text'     => 'nullable|string|max:100',
            'button2_link'     => 'nullable|string|max:500',
            'button2_target'   => 'nullable|string|max:20',

            'position'         => 'nullable|string|max:50',
            'device_visibility'=> 'nullable|string|max:20',

            'is_active'        => 'nullable|boolean',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);
    }

    /* ===================== MEDIA HANDLER ===================== */
    private function handleMedia(Request $request, OfferBanner $banner): void
    {
        if ($request->hasFile('desktop_image')) {
            $banner->clearMediaCollection('offer_banner_desktop');
            $banner->addMediaFromRequest('desktop_image')
                ->toMediaCollection('offer_banner_desktop');
        }

        if ($request->hasFile('mobile_image')) {
            $banner->clearMediaCollection('offer_banner_mobile');
            $banner->addMediaFromRequest('mobile_image')
                ->toMediaCollection('offer_banner_mobile');
        }

        if ($request->hasFile('thumbnail_image')) {
            $banner->clearMediaCollection('offer_banner_thumbnail');
            $banner->addMediaFromRequest('thumbnail_image')
                ->toMediaCollection('offer_banner_thumbnail');
        }
    }

    /* ===================== TRANSFORMER ===================== */
    private function transform(OfferBanner $b): array
    {
        return [
            'id' => $b->id,
            'slug' => $b->slug,
            'heading' => $b->heading,
            'sub_heading' => $b->sub_heading,
            'content' => $b->content,
            'highlight_text' => $b->highlight_text,

            'button1' => [
                'text' => $b->button1_text,
                'link' => $b->button1_link,
                'target' => $b->button1_target,
            ],
            'button2' => [
                'text' => $b->button2_text,
                'link' => $b->button2_link,
                'target' => $b->button2_target,
            ],

            'position' => $b->position,
            'device_visibility' => $b->device_visibility,
            'is_active' => $b->is_active,
            'start_date' => $b->start_date,
            'end_date' => $b->end_date,

            'view_count' => $b->view_count,
            'click_count' => $b->click_count,

            'images' => [
                'desktop' => $b->desktop_image_url,
                'mobile' => $b->mobile_image_url,
                'thumbnail' => $b->thumbnail_url,
            ],
        ];
    }
}
