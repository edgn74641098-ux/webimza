<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header
            :title="$group->exists ? 'Grup Duzenle' : 'Yeni Grup'"
            subtitle="Segment tanimi, uye kapsam ve aktiflik durumunu yonetin."
        />
    </x-slot>

    <x-ui.app-shell>
        @if($errors->any())
            <x-ui.alert type="error">Formda hatalar var. Lutfen alanlari kontrol edin.</x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ $group->exists ? route('admin.groups.update', $group) : route('admin.groups.store') }}" class="space-y-4">
                @csrf
                @if($group->exists) @method('PUT') @endif

                <div class="ui-form-grid">
                    <x-ui.input name="name" :value="old('name', $group->name)" placeholder="Grup Adi" required />
                    <x-ui.input name="code" :value="old('code', $group->code)" placeholder="Grup Kodu (opsiyonel)" />
                </div>

                <x-ui.textarea name="description" rows="3" placeholder="Aciklama">{{ old('description', $group->description) }}</x-ui.textarea>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $group->is_active ?? true))>
                    Aktif
                </label>

                <x-ui.card title="Uyeler" subtitle="Birden fazla kullanici secilebilir.">
                    <select name="users[]" multiple class="ui-input h-56">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(in_array($u->id, old('users', $group->exists ? $group->users->pluck('id')->all() : [])))>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </x-ui.card>

                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">{{ $group->exists ? 'Guncelle' : 'Kaydet' }}</x-ui.button>
                    <a href="{{ route('admin.groups.index') }}"><x-ui.button variant="secondary" type="button">Iptal</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>

