<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header :title="isset($user) ? 'Kullanici Duzenle' : 'Yeni Kullanici'" subtitle="Kullanici profil ve iletisim bilgilerini yonetin." />
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card class="mx-auto max-w-4xl">
            <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" class="space-y-4">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kullanici</label>
                        <x-ui.input name="name" :value="old('name', $user->name ?? '')" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">E-posta</label>
                        <x-ui.input name="email" type="email" :value="old('email', $user->email ?? '')" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Departman</label>
                        <x-ui.select name="department_id">
                            <option value="">Departman secin</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id ?? '') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Unvan</label>
                        <x-ui.input name="title" :value="old('title', $user->title ?? '')" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Telefon</label>
                        <x-ui.input name="phone" :value="old('phone', $user->phone ?? '')" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Mobil</label>
                        <x-ui.input name="mobile" :value="old('mobile', $user->mobile ?? '')" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Sirket</label>
                        <x-ui.input name="company" :value="old('company', $user->company ?? '')" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Website</label>
                        <x-ui.input name="website" :value="old('website', $user->website ?? '')" />
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    Aktif kullanici
                </label>

                <div class="flex items-center gap-2 pt-2">
                    <x-ui.button type="submit">{{ isset($user) ? 'Guncelle' : 'Kaydet' }}</x-ui.button>
                    <a href="{{ route('admin.users.index') }}"><x-ui.button type="button" variant="secondary">Vazgec</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
