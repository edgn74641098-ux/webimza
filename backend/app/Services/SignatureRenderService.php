<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\SignatureTemplate;
use App\Models\User;

class SignatureRenderService
{
    public function __construct(
        private readonly PlaceholderService $placeholderService
    ) {}

    public function render(SignatureTemplate $template, User $user): array
    {
        $logoUrl = Asset::query()->where('name', 'logo')->value('public_url') ?? '';
        $bannerUrl = Asset::query()->where('name', 'banner')->value('public_url') ?? '';

        $data = [
            'DisplayName' => $user->name,
            'Email' => $user->email,
            'Username' => $user->username,
            'Title' => $user->title,
            'Department' => $user->department?->name,
            'Company' => $user->company,
            'Phone' => $user->phone,
            'Mobile' => $user->mobile,
            'Office' => $user->office,
            'Address' => $user->address,
            'Website' => $user->website,
            'LogoUrl' => $logoUrl,
            'BannerUrl' => $bannerUrl,
        ];

        return [
            'html' => $this->placeholderService->render($template->html_content, $data),
            'text' => $this->placeholderService->render($template->text_content ?? strip_tags($template->html_content), $data),
        ];
    }
}
