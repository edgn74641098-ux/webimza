<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SignatureTemplate;
use App\Models\User;
use App\Services\SignatureRenderService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = SignatureTemplate::query();

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('version', 'like', "%{$q}%")
                    ->orWhere('type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return view('admin.templates.index', [
            'templates' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'type']),
        ]);
    }

    public function create()
    {
        return view('admin.templates.create', [
            'previewUsers' => User::query()->with('department:id,name')->orderBy('name')->limit(50)->get([
                'id', 'name', 'email', 'username', 'title', 'company', 'phone', 'mobile', 'office', 'address', 'website', 'department_id',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:new_message,reply,both'],
            'html_content' => ['required', 'string'],
            'text_content' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'version' => ['required', 'string', 'max:255'],
        ]);

        if (!empty($data['is_default'])) {
            SignatureTemplate::query()->update(['is_default' => false]);
        }

        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['is_default'] = (bool)($data['is_default'] ?? false);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        SignatureTemplate::create($data);

        return redirect()->route('admin.templates.index')->with('success', 'Sablon olusturuldu.');
    }

    public function edit(SignatureTemplate $template)
    {
        return view('admin.templates.edit', [
            'template' => $template,
            'previewUsers' => User::query()->with('department:id,name')->orderBy('name')->limit(50)->get([
                'id', 'name', 'email', 'username', 'title', 'company', 'phone', 'mobile', 'office', 'address', 'website', 'department_id',
            ]),
        ]);
    }

    public function update(Request $request, SignatureTemplate $template)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:new_message,reply,both'],
            'html_content' => ['required', 'string'],
            'text_content' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'version' => ['required', 'string', 'max:255'],
        ]);

        if (!empty($data['is_default'])) {
            SignatureTemplate::where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        $data['updated_by'] = auth()->id();
        $data['is_default'] = (bool)($data['is_default'] ?? false);
        $data['is_active'] = (bool)($data['is_active'] ?? false);

        $template->update($data);

        return redirect()->route('admin.templates.index')->with('success', 'Sablon guncellendi.');
    }

    public function destroy(SignatureTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', 'Sablon silindi.');
    }

    public function preview(Request $request, SignatureRenderService $renderService)
    {
        $data = $request->validate([
            'html_content' => ['required', 'string'],
            'text_content' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $user = isset($data['user_id']) ? User::with('department')->find($data['user_id']) : null;
        if (! $user) {
            $user = new User([
                'name' => 'Test User',
                'email' => 'test@firma.com',
                'username' => 'test.user',
                'title' => 'Muhendis',
                'company' => 'TRINOX',
                'phone' => '+90 212 000 00 00',
                'mobile' => '+90 555 000 00 00',
                'website' => 'https://example.com',
            ]);
            $user->setRelation('department', null);
        }

        $template = new SignatureTemplate([
            'html_content' => $data['html_content'],
            'text_content' => $data['text_content'] ?? '',
        ]);

        $rendered = $renderService->render($template, $user);

        return response()->json([
            'html' => $rendered['html'] ?? '',
            'text' => $rendered['text'] ?? '',
        ]);
    }
}
