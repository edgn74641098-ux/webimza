<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="isset($template) ? 'Sablon Duzenle' : 'Yeni Sablon'" subtitle="Sade split-view editor ile imza sablonu olusturun." />
    </x-slot>

    <x-ui.app-shell>
        <div
            class="grid grid-cols-1 gap-4 xl:grid-cols-2"
            x-data="{
                html: @js(old('html_content', $template->html_content ?? '')),
                text: @js(old('text_content', $template->text_content ?? '')),
                previewHtml: '',
                previewText: '',
                previewUserId: '',
                previewLoading: false,
                previewError: '',
                placeholders: ['DisplayName','Title','Department','Company','Phone','Mobile','Email','Website','LogoUrl','BannerUrl'],
                openTag: '{'+'{',
                closeTag: '}'+'}',
                insertPlaceholder(token, target = 'html') {
                    const value = this.openTag + token + this.closeTag;
                    if (target === 'text') {
                        this.text = (this.text || '') + value;
                        return;
                    }
                    this.html = (this.html || '') + value;
                },
                insertConditionalMobile() {
                    const block = this.openTag + '#if Mobile' + this.closeTag + '\nMobil: ' + this.openTag + 'Mobile' + this.closeTag + '<br>\n' + this.openTag + '/if' + this.closeTag;
                    this.html = (this.html || '') + '\n' + block;
                },
                pasteHtmlFromClipboard(event) {
                    const html = event?.clipboardData?.getData('text/html');
                    if (!html) {
                        return;
                    }

                    event.preventDefault();
                    this.html = html;
                },
                async runPreview() {
                    this.previewLoading = true;
                    this.previewError = '';
                    try {
                        const response = await fetch('{{ route('admin.templates.preview') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                html_content: this.html || '',
                                text_content: this.text || '',
                                user_id: this.previewUserId || null
                            })
                        });

                        const payload = await response.json();
                        if (!response.ok) {
                            this.previewHtml = '';
                            this.previewText = '';
                            this.previewError = payload.message || 'Preview olusturulamadi.';
                            return;
                        }

                        this.previewHtml = payload.html || '';
                        this.previewText = payload.text || '';
                    } catch (error) {
                        this.previewHtml = '';
                        this.previewText = '';
                        this.previewError = 'Preview istegi sirasinda baglanti hatasi olustu.';
                    } finally {
                        this.previewLoading = false;
                    }
                }
            }"
            x-init="runPreview()"
        >
            <x-ui.card title="Sablon Editoru">
                <form method="POST" action="{{ isset($template) ? route('admin.templates.update', $template) : route('admin.templates.store') }}" class="space-y-4">
                    @csrf
                    @if(isset($template))
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">Sablon adi</label>
                            <x-ui.input name="name" :value="old('name', $template->name ?? '')" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tip</label>
                            <x-ui.select name="type">
                                <option value="both" @selected(old('type', $template->type ?? 'both') === 'both')>both</option>
                                <option value="new_message" @selected(old('type', $template->type ?? '') === 'new_message')>new_message</option>
                                <option value="reply" @selected(old('type', $template->type ?? '') === 'reply')>reply</option>
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Versiyon</label>
                            <x-ui.input name="version" :value="old('version', $template->version ?? '1.0.0')" required />
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-700">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ?? true))>
                            Aktif
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $template->is_default ?? false))>
                            Varsayilan
                        </label>
                    </div>

                    <div>
                        <p class="mb-2 text-sm font-medium text-slate-700">Placeholder butonlari</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="token in placeholders" :key="token">
                                <button type="button" class="ui-btn-secondary" @click="insertPlaceholder(token, 'html')" x-text="'{{' + token + '}}'"></button>
                            </template>
                            <button type="button" class="ui-btn-secondary" @click="insertConditionalMobile()">Conditional: Mobile</button>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">HTML icerik</label>
                        <p class="mb-2 text-xs text-slate-500">Word/Outlook'tan yapistirirken HTML korunmasi icin bu alana yapistirin (Ctrl+V).</p>
                        <x-ui.textarea name="html_content" x-model="html" @paste="pasteHtmlFromClipboard($event)" rows="12" class="font-mono" required />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Text icerik</label>
                        <x-ui.textarea name="text_content" x-model="text" rows="8" class="font-mono" />
                    </div>

                    <div class="flex items-center gap-2">
                        <x-ui.button>Kaydet</x-ui.button>
                        <a href="{{ route('admin.templates.index') }}"><x-ui.button type="button" variant="secondary">Vazgec</x-ui.button></a>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Onizleme">
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Onizleme kullanicisi</label>
                        <div class="flex items-center gap-2">
                            <x-ui.select x-model="previewUserId">
                                <option value="">Ornek kullanici (fallback)</option>
                                @foreach($previewUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.button type="button" variant="secondary" @click="runPreview()" x-bind:disabled="previewLoading">Onizlemeyi Guncelle</x-ui.button>
                        </div>
                    </div>

                    <template x-if="previewError">
                        <x-ui.alert type="danger"><span x-text="previewError"></span></x-ui.alert>
                    </template>

                    <template x-if="previewLoading">
                        <x-ui.alert type="info">Onizleme hazirlaniyor...</x-ui.alert>
                    </template>

                    <div>
                        <p class="mb-1 text-sm font-medium text-slate-700">HTML preview</p>
                        <iframe class="h-[280px] w-full rounded-lg border border-slate-200 bg-white" :srcdoc="previewHtml"></iframe>
                    </div>

                    <div>
                        <p class="mb-1 text-sm font-medium text-slate-700">Plain text preview</p>
                        <x-ui.code-block><span x-text="previewText || '-'"></span></x-ui.code-block>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </x-ui.app-shell>
</x-app-layout>
